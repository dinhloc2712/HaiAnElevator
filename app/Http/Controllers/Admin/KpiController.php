<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Proposal;
use App\Models\KpiResetLog;
use App\Models\Ship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KpiController extends Controller
{
    /**
     * Lấy thời điểm reset KPI gần nhất (nếu có).
     * Chỉ tính proposals created_at > $lastResetAt
     */
    private function getLastResetAt()
    {
        $last = KpiResetLog::latest('reset_at')->first();
        return $last ? $last->reset_at : null;
    }

    public function index()
    {
        $this->authorize('view_kpi');

        $lastResetAt = $this->getLastResetAt();

        // Lấy users có quản lý ít nhất 1 tàu qua bảng ship_user
        $users = User::has('managedShips')
            ->with(['role', 'managedShips'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($lastResetAt) {
                $shipIds = $user->managedShips->pluck('id');

                // Chỉ tính proposals: status=approved, amount=0, và sau mốc reset gần nhất
                $query = Proposal::whereIn('ship_id', $shipIds)
                    ->where('status', 'approved')
                    ->where('amount', 0);

                if ($lastResetAt) {
                    $query->where('updated_at', '>', $lastResetAt);
                }

                $totalFee = $query->sum('pre_vat_amount');

                $user->commission_total    = $totalFee * ($user->commission_rate / 100);
                $user->revenue_total       = $totalFee;
                $user->managed_ships_count = $user->managedShips->count();

                return $user;
            })
            ->sortByDesc('commission_total')
            ->values();

        // Danh sách log reset để hiển thị (tùy chọn)
        $resetLogs = KpiResetLog::with('resetByUser')
            ->latest('reset_at')
            ->take(5)
            ->get();
        // Lấy tất cả tàu để assign trong modal
        $allShips = Ship::select('id', 'registration_number', 'owner_name', 'status')->latest()->get();

        return view('admin.kpi.index', compact('users', 'lastResetAt', 'resetLogs', 'allShips'));
    }

    /**
     * AJAX: trả về chi tiết user + danh sách tàu quản lý dưới dạng JSON
     */
    public function userDetail(User $user)
    {
        $this->authorize('view_kpi');
        $user->load(['role', 'managedShips']);

        $lastResetAt = $this->getLastResetAt();
        $shipIds     = $user->managedShips->pluck('id');

        $query = Proposal::whereIn('ship_id', $shipIds)
            ->where('status', 'approved')
            ->where('amount', 0);

        if ($lastResetAt) {
            $query->where('updated_at', '>', $lastResetAt);
        }

        $totalFee        = $query->sum('pre_vat_amount');
        $commissionTotal = $totalFee * ($user->commission_rate / 100);

        $ships = $user->managedShips->map(function ($ship) use ($lastResetAt) {
            // Công nợ = tổng amount còn lại của proposals approved nhưng chưa hoàn thành (amount > 0)
            $debtQuery = Proposal::where('ship_id', $ship->id)
                ->where('status', 'approved')
                ->where('amount', '>', 0);

            if ($lastResetAt) {
                $debtQuery->where('updated_at', '>', $lastResetAt);
            }

            $debt = $debtQuery->sum('amount');

            $expirationDate = $ship->expiration_date
                ? $ship->expiration_date->format('d/m/Y')
                : null;

            return [
                'id'                  => $ship->id,
                'registration_number' => $ship->registration_number,
                'name'                => $ship->name ? $ship->name : $ship->registration_number,
                'owner_name'          => $ship->owner_name,
                'status'              => $ship->status,
                'expiration_date'     => $expirationDate,
                'debt'                => $debt,
            ];
        });

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'role'            => $user->role ? ($user->role->display_name ?? $user->role->name) : 'N/A',
                'avatar_char'     => strtoupper(substr($user->name, 0, 1)),
                'commission'      => $commissionTotal,
                'commission_rate' => $user->commission_rate,
                'ships_count'     => $ships->count(),
            ],
            'ships' => $ships->values(),
        ]);
    }

    /**
     * Reset KPI: lưu snapshot hiện tại rồi tạo mốc reset mới.
     * Sau reset, chỉ tính proposals created/updated SAU mốc này.
     */
    public function resetKpi(Request $request)
    {
        $this->authorize('reset_kpi');

        $lastResetAt = $this->getLastResetAt();

        // Tạo snapshot KPI hiện tại của tất cả user trước khi reset
        $snapshot = User::has('managedShips')
            ->with(['managedShips'])
            ->get()
            ->map(function ($user) use ($lastResetAt) {
                $shipIds = $user->managedShips->pluck('id');
                $query   = Proposal::whereIn('ship_id', $shipIds)
                    ->where('status', 'approved')
                    ->where('amount', 0);
                if ($lastResetAt) {
                    $query->where('updated_at', '>', $lastResetAt);
                }
                $totalFee = $query->sum('pre_vat_amount');
                return [
                    'user_id'          => $user->id,
                    'user_name'        => $user->name,
                    'revenue_total'    => $totalFee,
                    'commission_rate'  => $user->commission_rate,
                    'commission_total' => $totalFee * ($user->commission_rate / 100),
                    'ships_count'      => $user->managedShips->count(),
                ];
            })
            ->values()
            ->toArray();

        // Lưu log reset
        KpiResetLog::create([
            'reset_by' => Auth::id(),
            'snapshot' => $snapshot,
            'reset_at' => now(),
            'note'     => $request->input('note', 'Reset KPI thủ công'),
        ]);

        return back()->with('success', 'Đã reset KPI thành công. Snapshot đã được lưu lại.');
    }

    /**
     * Cập nhật tỷ lệ hoa hồng cho user
     */
    public function updateCommission(Request $request, User $user)
    {
        $this->authorize('update_kpi');
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $user->update(['commission_rate' => $request->commission_rate]);

        return back()->with('success', "Đã cập nhật tỷ lệ hoa hồng của {$user->name} thành {$request->commission_rate}%");
    }

    /**
     * Cập nhật danh sách tàu phụ trách cho user
     */
    public function updateAssignedShips(Request $request, User $user)
    {
        $this->authorize('update_kpi');

        $request->validate([
            'ship_ids'   => 'nullable|array',
            'ship_ids.*' => 'exists:ships,id',
        ]);

        $shipIds = $request->input('ship_ids', []);
        
        // Sync relationships with pivot data
        $syncData = [];
        foreach ($shipIds as $id) {
            $syncData[$id] = ['assigned_at' => now()];
        }
        
        $user->managedShips()->sync($syncData);

        return back()->with('success', "Đã cập nhật danh sách tàu phụ trách của nhân viên {$user->name}.");
    }
}
