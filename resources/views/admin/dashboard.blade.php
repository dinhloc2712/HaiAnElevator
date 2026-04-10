@extends('layouts.admin')

@section('title', 'Tổng quan')


@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Tổng quan hệ thống</h1>
        <p class="page-subtitle mb-0">Xin chào, <strong>{{ auth()->user()->name }}</strong>! Đây là tổng quan hoạt động hôm nay.</p>
    </div>
    <div class="text-end text-muted small">
        <i class="fas fa-calendar-alt me-1"></i>
        {{ now()->locale('vi')->isoFormat('dddd, D MMMM YYYY') }}
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    {{-- Total Users --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    @if($userGrowthRate >= 0)
                        <span class="badge-growth bg-success bg-opacity-15 text-success">
                            <i class="fas fa-arrow-up me-1"></i>{{ $userGrowthRate }}%
                        </span>
                    @else
                        <span class="badge-growth bg-danger bg-opacity-15 text-danger">
                            <i class="fas fa-arrow-down me-1"></i>{{ abs($userGrowthRate) }}%
                        </span>
                    @endif
                </div>
                <div class="stat-value text-primary">{{ number_format($totalUsers) }}</div>
                <div class="stat-label mt-1">Tổng tài khoản</div>
            </div>
        </div>
    </div>

    {{-- New Users This Month --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <span class="badge-growth bg-info bg-opacity-15 text-info">Tháng này</span>
                </div>
                <div class="stat-value text-success">{{ number_format($usersThisMonth) }}</div>
                <div class="stat-label mt-1">Tài khoản mới</div>
            </div>
        </div>
    </div>

    {{-- Total News --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span class="badge-growth bg-warning bg-opacity-15 text-warning">Tin tức</span>
                </div>
                <div class="stat-value text-warning">{{ number_format($totalNews) }}</div>
                <div class="stat-label mt-1">Tổng thông báo</div>
            </div>
        </div>
    </div>

    {{-- News This Month --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <span class="badge-growth bg-success bg-opacity-15 text-success">Tháng này</span>
                </div>
                <div class="stat-value text-info">{{ number_format($newsThisMonth) }}</div>
                <div class="stat-label mt-1">Thông báo mới</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-4 mb-4">
    {{-- User Growth Chart --}}
    <div class="col-12 col-lg-8">
        <div class="card chart-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Tăng trưởng tài khoản</h6>
                        <small class="text-muted">6 tháng gần nhất</small>
                    </div>
                    <i class="fas fa-chart-line text-primary opacity-50"></i>
                </div>
                <canvas id="userGrowthChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- User Structure Pie --}}
    <div class="col-12 col-lg-4">
        <div class="card chart-card h-100">
            <div class="card-body p-4">
                <div class="mb-3">
                    <h6 class="fw-bold mb-0">Cơ cấu nhân sự</h6>
                    <small class="text-muted">Phân bổ theo vai trò</small>
                </div>
                <canvas id="userStructureChart" height="180"></canvas>
                <div class="mt-3">
                    @foreach($userStructureDetails as $detail)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                        <span class="small text-truncate me-2" style="max-width: 140px;">{{ $detail['name'] }}</span>
                        <div class="text-end">
                            <span class="fw-bold small">{{ $detail['count'] }}</span>
                            <span class="text-muted small ms-1">({{ $detail['percent'] }}%)</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tables Row --}}
<div class="row g-4">
    {{-- Latest News --}}
    <div class="col-12 col-lg-6">
        <div class="card table-card">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">Thông báo mới nhất</h6>
                    <small class="text-muted">5 tin tức gần đây</small>
                </div>
                @can('view_news')
                <a href="{{ route('admin.news.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Xem tất cả
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                @forelse($latestNews as $item)
                <div class="d-flex align-items-center px-4 py-3 border-bottom border-light">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px;">
                            <i class="fas fa-file-alt fa-sm"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate small">{{ $item->title }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">{{ $item->created_at->diffForHumans() }}</div>
                    </div>
                    @can('view_news')
                    <a href="{{ route('admin.news.show', $item->id) }}" class="btn btn-sm btn-light ms-2">
                        <i class="fas fa-eye fa-sm"></i>
                    </a>
                    @endcan
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-2x mb-2 opacity-30"></i>
                    <p class="mb-0 small">Chưa có thông báo nào.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Latest Users --}}
    <div class="col-12 col-lg-6">
        <div class="card table-card">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">Tài khoản mới nhất</h6>
                    <small class="text-muted">5 người dùng gần đây</small>
                </div>
                @can('view_user')
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Xem tất cả
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                @forelse($latestUsers as $user)
                <div class="d-flex align-items-center px-4 py-3 border-bottom border-light">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-secondary bg-opacity-15 text-secondary fw-bold d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px; font-size: 1rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate small">{{ $user->name }}</div>
                        <div class="text-muted text-truncate" style="font-size: 0.78rem;">
                            {{ $user->email }}
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size: 0.72rem; white-space: nowrap;">
                        {{ $user->role->display_name ?? 'N/A' }}
                    </span>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-users fa-2x mb-2 opacity-30"></i>
                    <p class="mb-0 small">Chưa có tài khoản nào.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Color palette
    const palette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796'];

    // 1. User Growth Chart
    const ugCtx = document.getElementById('userGrowthChart');
    if (ugCtx) {
        new Chart(ugCtx, {
            type: 'line',
            data: {
                labels: @json($growthMonths),
                datasets: [{
                    label: 'Tài khoản mới',
                    data: @json($userGrowthData),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78,115,223,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#4e73df',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. User Structure Pie Chart
    const usCtx = document.getElementById('userStructureChart');
    if (usCtx) {
        new Chart(usCtx, {
            type: 'doughnut',
            data: {
                labels: @json($userStructure['labels']),
                datasets: [{
                    data: @json($userStructure['series']),
                    backgroundColor: palette,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endsection
