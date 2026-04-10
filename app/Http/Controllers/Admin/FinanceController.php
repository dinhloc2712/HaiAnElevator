<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_finance');
        $collectedRevenue = Transaction::where('type', 'income')->sum('amount');
        $uncollectedRevenue = Proposal::whereNotNull('ship_id')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $totalRevenue = $collectedRevenue + $uncollectedRevenue;

        $chartData = [];
        $chartLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $chartLabels[] = 'T' . $monthStart->format('n');
            $chartData[] = Transaction::where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
        }

        $recentTransactions = Transaction::orderBy('transaction_date', 'desc')->take(5)->get();

        $perPage = $request->get('per_page', 20);
        $search  = $request->get('search', '');

        $query = Transaction::with('user')->orderBy('transaction_date', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate($perPage)->withQueryString();

        return view('admin.finance.index', compact(
            'totalRevenue', 'collectedRevenue', 'uncollectedRevenue', 'totalExpense',
            'chartLabels', 'chartData',
            'recentTransactions', 'transactions',
            'search', 'perPage'
        ));
    }

    public function create()
    {
        $this->authorize('create_finance');
        $proposals = \App\Models\Proposal::with(['ship'])->orderBy('created_at', 'desc')->get();
        return view('admin.finance.create', compact('proposals'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_finance');
        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'amount'           => 'required|numeric|min:1',
            'customer_name'    => 'nullable|string|max:255',
            'payment_method'   => 'required|in:cash,transfer',
            'status'           => 'required|in:pending,approved,rejected',
            'description'      => 'nullable|string|max:1000',
            'reference_id'     => 'nullable|string|max:100',
            'reference_type'   => 'nullable|string|max:255',
        ]);

        // Auto-generate code: PT-YYYYMMDD-XXXX or PC-...
        $prefix = $validated['type'] === 'income' ? 'PT' : 'PC';
        $count  = Transaction::where('type', $validated['type'])->count() + 1;
        $validated['code']    = $prefix . '-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $validated['user_id'] = Auth::id();

        $transaction = Transaction::create($validated);

        if ($transaction->reference_type === 'proposal' && $transaction->reference_id) {
            $proposal = \App\Models\Proposal::find($transaction->reference_id);
            if ($proposal) {
                $proposal->amount -= $transaction->amount;
                if ($proposal->amount < 0) {
                    $proposal->amount = 0;
                }
                $proposal->paid_amount += $transaction->amount;
                $proposal->save();
            }
        }

        return redirect()->route('admin.finance.index')
            ->with('success', 'Tạo phiếu ' . ($validated['type'] === 'income' ? 'thu' : 'chi') . ' thành công!');
    }

    public function edit(Transaction $finance)
    {
        $this->authorize('update_finance');
        return view('admin.finance.edit', compact('finance'));
    }

    public function update(Request $request, Transaction $finance)
    {
        $this->authorize('update_finance');
        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'amount'           => 'required|numeric|min:1',
            'customer_name'    => 'nullable|string|max:255',
            'payment_method'   => 'required|in:cash,transfer',
            'status'           => 'required|in:pending,approved,rejected',
            'description'      => 'nullable|string|max:1000',
            'reference_id'     => 'nullable|string|max:100',
            'reference_type'   => 'nullable|string|max:255',
        ]);

        $finance->update($validated);

        return redirect()->route('admin.finance.index')
            ->with('success', 'Cập nhật phiếu thành công!');
    }

    public function destroy(Transaction $finance)
    {
        $this->authorize('delete_finance');
        $finance->delete();

        return redirect()->route('admin.finance.index')
            ->with('success', 'Xóa phiếu thành công!');
    }
}
