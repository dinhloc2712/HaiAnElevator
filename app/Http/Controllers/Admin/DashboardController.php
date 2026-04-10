<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ship;
use App\Models\User;
use App\Models\Inspection;
use App\Models\Transaction;
use App\Models\Proposal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('view_dashboard');

        // 1. TỔNG ĐỘI TÀU
        $totalShips = Ship::count();
        $shipsLastMonth = Ship::whereMonth('created_at', Carbon::now()->subMonth()->month)
                               ->whereYear('created_at', Carbon::now()->subMonth()->year)
                               ->count();
        $shipsThisMonth = Ship::whereMonth('created_at', Carbon::now()->month)
                               ->whereYear('created_at', Carbon::now()->year)
                               ->count();
        
        $shipGrowthRate = $shipsLastMonth > 0 ? round((($shipsThisMonth - $shipsLastMonth) / $shipsLastMonth) * 100, 1) : ($shipsThisMonth > 0 ? 100 : 0);

        // 2. HẾT HẠN & SẮP HẾT HẠN
        $now = Carbon::now()->startOfDay();
        $thirtyDaysLater = $now->copy()->addDays(30);

        $expiredShipsCount = Ship::whereNotNull('expiration_date')->where('expiration_date', '<', $now)->count();
        $expiringSoonShipsCount = Ship::whereNotNull('expiration_date')->whereBetween('expiration_date', [$now, $thirtyDaysLater])->count();

        // 3. ĐANG XỬ LÝ (Processing) - Count ships where latest proposal is not approved
        $allShipsWithLatestProposal = Ship::with(['proposals' => function ($q) {
            $q->latest('created_at')->limit(1);
        }])->get();

        $inspectingCount = 0;
        foreach ($allShipsWithLatestProposal as $s) {
            $latestProposal = $s->proposals->first();
            if ($latestProposal && $latestProposal->status !== 'approved') {
                $inspectingCount++;
            }
        }

        // 4. TỔNG CÔNG NỢ
        $totalDebt = Proposal::whereNotNull('ship_id')->sum('amount');
        
        // Compare with last month debt
        $debtLastMonth = Proposal::whereNotNull('ship_id')
                                    ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                                    ->whereYear('created_at', Carbon::now()->subMonth()->year)
                                    ->sum('amount');
        $debtGrowthRate = $debtLastMonth > 0 ? round((($totalDebt - $debtLastMonth) / $debtLastMonth) * 100, 1) : ($totalDebt > 0 ? 100 : 0);
        
        // 5. Lưu lượng Đăng kiểm (6 tháng)
        $trafficData = ['done' => [], 'new' => []];
        $trafficMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $trafficMonths[] = 'T' . $month->month;
            
            $doneCount = Inspection::where('status', 'completed')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
                
            $newCount = Inspection::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
                
            $trafficData['done'][] = $doneCount;
            $trafficData['new'][] = $newCount;
        }

        // 6. Quản lý Doanh thu & Công nợ (6 tháng)
        $revenueData = ['revenue' => [], 'debt' => []];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $thucThu = Transaction::where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $congNo = Proposal::whereNotNull('ship_id')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
                
            // Convert to millions for chart
            $revenueData['revenue'][] = round($thucThu / 1000000, 2);
            $revenueData['debt'][] = round($congNo / 1000000, 2);
        }

        // 7. Cơ cấu Nhân sự (Theo Role)
        $userStructureDB = User::with('role')
                                ->selectRaw('role_id, count(*) as total')
                                ->groupBy('role_id')
                                ->get();
                                
        $userStructure = ['labels' => [], 'series' => []];
        $totalUserStructure = 0;
        foreach ($userStructureDB as $item) {
            $label = $item->role ? ($item->role->display_name ?? $item->role->name) : 'Chưa phân quyền';
            $userStructure['labels'][] = $label;
            $userStructure['series'][] = $item->total;
            $totalUserStructure += $item->total;
        }
        
        $userStructureDetails = [];
        foreach ($userStructureDB as $item) {
            $label = $item->role ? ($item->role->display_name ?? $item->role->name) : 'Chưa phân quyền';
            $percent = $totalUserStructure > 0 ? round(($item->total / $totalUserStructure) * 100, 1) : 0;
            $userStructureDetails[] = [
                'name' => $label,
                'count' => $item->total,
                'percent' => $percent
            ];
        }
        
        // Handle empty structure
        if (empty($userStructure['labels'])) {
            $userStructure['labels'] = ['Chưa có dữ liệu'];
            $userStructure['series'] = [0];
        }

        // 8. Phân bổ Địa lý (Top 5)
        $topProvinces = Ship::selectRaw('province_id, count(*) as total')
                            ->whereNotNull('province_id')
                            ->where('province_id', '!=', '')
                            ->groupBy('province_id')
                            ->orderByDesc('total')
                            ->limit(5)
                            ->get();
                            
        $geoDistribution = [
            'labels' => [],
            'series' => []
        ];
        
        foreach ($topProvinces as $province) {
            $geoDistribution['labels'][] = $province->province_id;
            $geoDistribution['series'][] = $province->total;
        }
        
        if (empty($geoDistribution['labels'])) {
             $geoDistribution['labels'] = ['N/A'];
             $geoDistribution['series'] = [0];
        }

        // 9. Sắp hết hạn (Sắp hết hạn trong 30 ngày tới hoặc đã hết hạn)
        $expiringShips = Ship::whereNotNull('expiration_date')
                             ->where('expiration_date', '<=', Carbon::now()->addDays(30))
                             ->orderBy('expiration_date', 'asc')
                             ->get();
                             
        $expiringCount = $expiringShips->count();
        $expiringShipsList = $expiringShips->take(5);

        return view('admin.dashboard', compact(
            'totalShips', 'shipGrowthRate',
            'expiredShipsCount', 'expiringSoonShipsCount',
            'inspectingCount',
            'totalDebt', 'debtGrowthRate',
            'trafficMonths', 'trafficData',
            'revenueData',
            'userStructure', 'userStructureDetails', 'totalUserStructure',
            'geoDistribution',
            'expiringCount', 'expiringShipsList'
        ));
    }
}
