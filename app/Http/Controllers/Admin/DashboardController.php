<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\News;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('view_dashboard');

        // 1. Tổng số tài khoản
        $totalUsers = User::count();
        $usersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year)
                              ->count();
        $usersLastMonth = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
                              ->whereYear('created_at', Carbon::now()->subMonth()->year)
                              ->count();
        $userGrowthRate = $usersLastMonth > 0
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100, 1)
            : ($usersThisMonth > 0 ? 100 : 0);

        // 2. Tổng số thông báo / tin tức
        $totalNews = News::count();
        $newsThisMonth = News::whereMonth('created_at', Carbon::now()->month)
                             ->whereYear('created_at', Carbon::now()->year)
                             ->count();

        // 3. Cơ cấu Nhân sự (Theo Role)
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
                'name'    => $label,
                'count'   => $item->total,
                'percent' => $percent,
            ];
        }

        if (empty($userStructure['labels'])) {
            $userStructure['labels'] = ['Chưa có dữ liệu'];
            $userStructure['series'] = [0];
        }

        // 4. Tăng trưởng tài khoản 6 tháng gần nhất
        $userGrowthData  = [];
        $growthMonths    = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $growthMonths[]   = 'T' . $month->month;
            $userGrowthData[] = User::whereMonth('created_at', $month->month)
                                     ->whereYear('created_at', $month->year)
                                     ->count();
        }

        // 5. Tin tức mới nhất (5 bài)
        $latestNews = News::latest()->take(5)->get();

        // 6. Tài khoản mới nhất (5 người)
        $latestUsers = User::with('role')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'usersThisMonth', 'userGrowthRate',
            'totalNews', 'newsThisMonth',
            'userStructure', 'userStructureDetails', 'totalUserStructure',
            'growthMonths', 'userGrowthData',
            'latestNews', 'latestUsers'
        ));
    }
}
