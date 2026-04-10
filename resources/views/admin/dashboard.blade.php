@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* Add Dashboard styles */
    .dashboard-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
        height: 100%;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .icon-shape {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .bg-light-primary { background-color: #f0f9ff; color: #3b82f6; } 
    .bg-light-danger { background-color: #fef2f2; color: #ef4444; } 
    .bg-light-purple { background-color: #faf5ff; color: #a855f7; } 
    .bg-light-warning { background-color: #fff7ed; color: #f97316; } 
    
    .text-success-custom { color: #10b981; font-weight: 500; font-size: 0.8rem;}
    .text-danger-custom { color: #ef4444; font-weight: 500; font-size: 0.8rem;}
    .text-muted-custom { color: #6b7280; font-size: 0.875rem; }
    
    .card-title-custom {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }
    .card-header-custom {
        border-bottom: 1px solid #f3f4f6;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-header-title {
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        font-size: 0.95rem;
    }
    .header-btn {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.875rem;
    }
    .header-btn:hover {
        background-color: #f9fafb;
    }
    .header-btn-primary {
        background-color: #3b82f6;
        border: 1px solid #3b82f6;
        color: #fff;
    }
    .header-btn-primary:hover {
        background-color: #2563eb;
        color: #fff;
    }
    
    /* Animation delays */
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    .delay-5 { animation-delay: 0.5s; }
    .delay-6 { animation-delay: 0.6s; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
    <div>
        <h1 class="h4 mb-1 text-gray-800 fw-bold">Trung tâm Điều hành Đăng kiểm</h1>
        <p class="text-muted-custom mb-0">Cập nhật dữ liệu thời gian thực • 20/2/2026</p>
    </div>
    <div class="d-flex gap-2">
        <button class="header-btn shadow-sm">
            <i class="far fa-calendar-alt me-1"></i> Tháng 2, 2026
        </button>
        <button class="header-btn header-btn-primary shadow-sm">
            <i class="fas fa-chart-line me-1"></i> Xuất báo cáo
        </button>
    </div>
</div>

<!-- Top Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1 -->
    <div class="col-xl col-md-4 col-sm-6 animate__animated animate__fadeInUp delay-1">
        <div class="dashboard-card p-3 p-xl-4 h-100">
            <div class="d-flex justify-content-between align-items-start h-100 flex-column">
                <div class="w-100 d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title-custom mb-0">Tổng số tàu cá</h6>
                    <div class="icon-shape bg-light-primary" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="fas fa-ship opacity-50"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="stat-value mb-1">{{ number_format($totalShips, 0, ',', '.') }}</h2>
                        @if($shipGrowthRate >= 0)
                            <span class="text-success-custom"><i class="fas fa-arrow-trend-up text-xs"></i> +{{ $shipGrowthRate }}%</span>
                        @else
                            <span class="text-danger-custom"><i class="fas fa-arrow-trend-down text-xs"></i> {{ $shipGrowthRate }}%</span>
                        @endif
                    </div>
                    <p class="text-muted-custom mb-0" style="font-size: 0.75rem;">Đang quản lý trên hệ thống</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 2: Hết hạn Đăng kiểm -->
    <div class="col-xl col-md-4 col-sm-6 animate__animated animate__fadeInUp delay-2">
        <a href="{{ route('admin.ships.index', ['expiration' => 'expired']) }}" class="text-decoration-none h-100 d-block">
            <div class="dashboard-card p-3 p-xl-4 border border-danger border-opacity-25 h-100" style="background-color: #fffafb;">
                <div class="d-flex justify-content-between align-items-start h-100 flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title-custom text-danger mb-0">Đã Hết hạn Đăng kiểm</h6>
                        <div class="icon-shape bg-light-danger" style="width: 36px; height: 36px; font-size: 1.1rem;">
                            <i class="fas fa-exclamation-circle opacity-75"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="stat-value text-danger mb-1">{{ number_format($expiredShipsCount, 0, ',', '.') }}</h2>
                        </div>
                        <p class="text-muted-custom mb-0" style="font-size: 0.75rem;">Cần xử lý ngay</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 3: Sắp Hết hạn Đăng kiểm -->
    <div class="col-xl col-md-4 col-sm-6 animate__animated animate__fadeInUp delay-2">
        <a href="{{ route('admin.ships.index', ['expiration' => 'expiring_soon']) }}" class="text-decoration-none h-100 d-block">
            <div class="dashboard-card p-3 p-xl-4 border border-warning border-opacity-25 h-100" style="background-color: #fffbeb;">
                <div class="d-flex justify-content-between align-items-start h-100 flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title-custom text-warning mb-0">Sắp Hết hạn Đăng kiểm</h6>
                        <div class="icon-shape bg-light-warning" style="width: 36px; height: 36px; font-size: 1.1rem;">
                            <i class="fas fa-clock opacity-75"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="stat-value text-warning mb-1">{{ number_format($expiringSoonShipsCount, 0, ',', '.') }}</h2>
                        </div>
                        <p class="text-muted-custom mb-0" style="font-size: 0.75rem;">Trong vòng 30 ngày tới</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4 -->
    <div class="col-xl col-md-6 col-sm-6 animate__animated animate__fadeInUp delay-3">
        <a href="{{ route('admin.ships.index', ['status' => 'processing']) }}" class="text-decoration-none h-100 d-block">
            <div class="dashboard-card p-3 p-xl-4 h-100">
                <div class="d-flex justify-content-between align-items-start h-100 flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title-custom text-info mb-0">Hồ sơ đang duyệt</h6>
                        <div class="icon-shape bg-info bg-opacity-10 text-info" style="width: 36px; height: 36px; font-size: 1.1rem;">
                            <i class="fas fa-tasks opacity-75"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="stat-value text-info mb-1">{{ number_format($inspectingCount, 0, ',', '.') }}</h2>
                        </div>
                        <p class="text-muted-custom mb-0" style="font-size: 0.75rem;">Hồ sơ chưa được duyệt</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 5 -->
    <div class="col-xl col-md-6 col-sm-12 animate__animated animate__fadeInUp delay-4">
        <div class="dashboard-card p-3 p-xl-4 h-100">
            <div class="d-flex justify-content-between align-items-start h-100 flex-column">
                <div class="w-100 d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title-custom mb-0">Tổng công nợ</h6>
                    <div class="icon-shape bg-light-warning" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="fas fa-dollar-sign opacity-50"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="stat-value mb-1">{{ number_format($totalDebt, 0, ',', '.') }} đ</h2>
                        @if($debtGrowthRate >= 0)
                            <span class="text-success-custom"><i class="fas fa-arrow-trend-up text-xs"></i> +{{ $debtGrowthRate }}%</span>
                        @else
                            <span class="text-danger-custom"><i class="fas fa-arrow-trend-down text-xs"></i> {{ $debtGrowthRate }}%</span>
                        @endif
                    </div>
                    <p class="text-muted-custom mb-0" style="font-size: 0.75rem;">Tăng so với tháng trước</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Middle Charts -->
<div class="row g-3 mb-4">
    <div class="col-lg-7 animate__animated animate__fadeInUp delay-5">
        <div class="dashboard-card">
            <div class="card-header-custom border-0 pb-0">
                <h5 class="card-header-title"><i class="fas fa-chart-line text-primary me-2"></i> Lưu lượng Đăng kiểm (6 tháng)</h5>
                <div class="d-flex align-items-center gap-3" style="font-size: 0.75rem;">
                    <div class="d-flex align-items-center"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:#3b82f6; margin-right:4px;"></span><span class="text-muted-custom">Đã xong</span></div>
                    <div class="d-flex align-items-center"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:#bfdbfe; margin-right:4px;"></span><span class="text-muted-custom">Đăng ký mới</span></div>
                </div>
            </div>
            <div class="card-body p-3">
                <div id="trafficChart" style="height: 280px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 animate__animated animate__fadeInUp delay-5">
        <div class="dashboard-card">
            <div class="card-header-custom border-0 pb-0">
                <h5 class="card-header-title"><i class="fas fa-file-invoice-dollar text-success me-2"></i> Quản lý Doanh thu & Công nợ</h5>
                <div class="d-flex align-items-center gap-3" style="font-size: 0.75rem;">
                    <div class="d-flex align-items-center"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:#10b981; margin-right:4px;"></span><span class="text-muted-custom">Thực thu</span></div>
                    <div class="d-flex align-items-center"><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:#f87171; margin-right:4px;"></span><span class="text-muted-custom">Công nợ</span></div>
                </div>
            </div>
            <div class="card-body p-3">
                <div id="revenueChart" style="height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="row g-3 mb-4">
    <!-- Donut Chart -->
    <div class="col-lg-4 animate__animated animate__fadeInUp delay-6">
        <div class="dashboard-card">
            <div class="card-header-custom border-0 pb-0">
                <h5 class="card-header-title">Cơ cấu Nhân sự</h5>
            </div>
            <div class="card-body p-4">
                <div id="userStructureChart" style="height: 220px; display: flex; justify-content: center; align-items: center;"></div>
                
                <div class="mt-4">
                    @php $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']; @endphp
                    @foreach($userStructureDetails as $index => $detail)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:{{ $colors[$index % count($colors)] }}; margin-right:8px;"></span> 
                            <span class="text-sm text-gray-700" style="font-size:0.875rem;">{{ $detail['name'] }}</span>
                        </div>
                        <span class="fw-bold" style="font-size:0.875rem;">{{ $detail['percent'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Horizontal Bar Chart -->
    <div class="col-lg-4 animate__animated animate__fadeInUp delay-6">
        <div class="dashboard-card">
            <div class="card-header-custom border-0 pb-0">
                <h5 class="card-header-title"><i class="fas fa-map-marker-alt text-purple me-2" style="color:#8b5cf6"></i>Số tàu các tỉnh</h5>
            </div>
            <div class="card-body p-4"> 
                <div id="geoDistributionChart" style="height: 280px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Expiring List -->
    <div class="col-lg-4 animate__animated animate__fadeInUp delay-6">
        <div class="dashboard-card d-flex flex-column" style="background-color: #fafafa;">
            <div class="card-header-custom border-0 pb-0 d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="card-header-title"><i class="far fa-clock text-warning me-2" style="color:#f59e0b"></i> Sắp Hết hạn Đăng kiểm</h5>
                <span class="badge bg-warning text-dark rounded-pill" style="background-color:#fed7aa !important;">{{ $expiringCount }}</span>
            </div>
            <div class="card-body p-4 flex-grow-1 d-flex flex-column bg-transparent">
                <div class="d-flex justify-content-between text-muted-custom border-bottom pb-2 mb-3">
                    <span style="font-size: 0.8rem; font-weight: 600;">Số hiệu / Chủ tàu</span>
                    <span style="font-size: 0.8rem; font-weight: 600;">Hết hạn Đăng kiểm</span>
                </div>
                
                @if($expiringShipsList->isEmpty())
                <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted-custom py-5">
                    <span style="font-size: 0.875rem; color:#9ca3af;">Không có dữ liệu ({{ Carbon\Carbon::now()->year }}).</span>
                </div>
                @else
                <div class="flex-grow-1 d-flex flex-column gap-3 mb-3">
                    @foreach($expiringShipsList as $ship)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                        <div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #374151;">{{ $ship->registration_number ?? $ship->name }}</div>
                            <div style="font-size: 0.75rem; color: #6b7280;">{{ $ship->owner_name }}</div>
                        </div>
                        <div style="font-size: 0.875rem; font-weight: 500; color: #ef4444;">
                            {{ $ship->expiration_date ? \Carbon\Carbon::parse($ship->expiration_date)->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <div class="mt-auto border-top pt-3 text-center">
                    <a href="{{ route('admin.ships.index') }}" class="text-primary text-decoration-none" style="font-size: 0.85rem; font-weight: 500;">Xem tất cả tàu <i class="fas fa-arrow-right ms-1 text-xs"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Traffic Line Chart
        var trafficOptions = {
            series: [{
                name: 'Đã xong',
                data: {!! json_encode(array_reverse($trafficData['done'])) !!}
            }, {
                name: 'Đăng ký mới',
                data: {!! json_encode(array_reverse($trafficData['new'])) !!}
            }],
            chart: {
                height: 280,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                parentHeightOffset: 0,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: { enabled: true, delay: 150 },
                    dynamicAnimation: { enabled: true, speed: 350 }
                }
            },
            colors: ['#3b82f6', '#bfdbfe'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: {!! json_encode(array_reverse($trafficMonths)) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#9ca3af', fontSize: '12px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af', fontSize: '12px' },
                    offsetX: -10
                },
                min: 0,
                tickAmount: 4
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            legend: { show: false },
            tooltip: {
                theme: 'light',
                y: { formatter: function (val) { return val + " tàu" } }
            }
        };
        var trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficOptions);
        trafficChart.render();

        // Revenue Bar Chart
        var revenueOptions = {
            series: [{
                name: 'Thực thu',
                data: {!! json_encode(array_reverse($revenueData['revenue'])) !!}
            }, {
                name: 'Công nợ',
                data: {!! json_encode(array_reverse($revenueData['debt'])) !!}
            }],
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                parentHeightOffset: 0,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            colors: ['#10b981', '#f87171'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: '35%',
                },
            },
            dataLabels: { enabled: false },
            stroke: {
                width: 2,
                colors: ['#fff']
            },
            xaxis: {
                categories: {!! json_encode(array_reverse($trafficMonths)) !!},
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#9ca3af', fontSize: '12px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af', fontSize: '12px' },
                    offsetX: -10
                },
                min: 0,
                tickAmount: 4
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            legend: { show: false },
            fill: { opacity: 1 },
            tooltip: {
                theme: 'light',
                y: { formatter: function (val) { return val + " Tr" } }
            }
        };
        var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
        revenueChart.render();

        // User Structure Donut Chart
        var userOptions = {
            series: {!! json_encode($userStructure['series']) !!},
            labels: {!! json_encode($userStructure['labels']) !!},
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Inter, sans-serif',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 300
                    }
                }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#6366f1'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#6b7280', offsetY: 20 },
                            value: { show: true, fontSize: '24px', fontWeight: 700, color: '#1f2937', offsetY: -10 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Nhân sự',
                                fontSize: '14px',
                                color: '#6b7280',
                                formatter: function (w) {
                                    return "{{ $totalUserStructure }}"
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['#fff'] },
            legend: { show: false },
            tooltip: { theme: 'light' }
        };
        var userChart = new ApexCharts(document.querySelector("#userStructureChart"), userOptions);
        userChart.render();
        
        // Geo Distribution Chart
        var geoOptions = {
            series: [{
                name: 'Số tàu',
                data: {!! json_encode($geoDistribution['series']) !!}
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                parentHeightOffset: 0,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: { enabled: true, delay: 400 }
                }
            },
            colors: ['#8b5cf6'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '40%',
                    dataLabels: {
                        position: 'right'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: {
                    colors: ['#9ca3af'],
                    fontSize: '11px',
                    fontWeight: 500
                },
                formatter: function (val, opt) {
                    return val + ' - - - -';
                },
                offsetX: 10,
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            xaxis: {
                categories: {!! json_encode($geoDistribution['labels']) !!},
                labels: { show: false },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { colors: '#6b7280', fontSize: '12px' }
                }
            },
            grid: {
                show: true,
                xaxis: { lines: { show: false } },   
                yaxis: { lines: { show: true } },
                strokeDashArray: 4,
                borderColor: '#e5e7eb',
                padding: { top: 0, right: 30, bottom: -10, left: 0 }
            },
            tooltip: {
                theme: 'light',
                y: { formatter: function (val) { return val + " tàu" } }
            }
        };
        var geoChart = new ApexCharts(document.querySelector("#geoDistributionChart"), geoOptions);
        geoChart.render();
    });
</script>
@endsection
