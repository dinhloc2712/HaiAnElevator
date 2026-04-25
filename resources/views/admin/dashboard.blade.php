@extends('layouts.admin')

@section('title', 'Tổng quan')


@section('content')
    <style>
        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .dashboard-subtitle {
            color: #718096;
            font-size: 1.1rem;
        }

        .update-badge {
            background: #f7fafc;
            border: 1px solid #edf2f7;
            padding: 8px 16px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .dot-online {
            width: 10px;
            height: 10px;
            background: #48bb78;
            border-radius: 50%;
            display: inline-block;
        }

        .premium-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .card-stat-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .card-stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .card-stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Colors and Accents */
        .border-accent-blue {
            border-bottom: 4px solid #3182ce;
        }

        .text-blue {
            color: #2b6cb0;
        }

        .bg-light-blue {
            background: rgba(49, 130, 206, 0.1);
            color: #3182ce;
        }

        .border-accent-red {
            border-bottom: 4px solid #e53e3e;
        }

        .text-red {
            color: #c53030;
        }

        .bg-light-red {
            background: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }

        .border-accent-orange {
            border-bottom: 4px solid #ed8936;
        }

        .text-orange {
            color: #c05621;
        }

        .bg-light-orange {
            background: rgba(237, 137, 54, 0.1);
            color: #ed8936;
        }

        .border-accent-purple {
            border-bottom: 4px solid #805ad5;
        }

        .text-purple {
            color: #6b46c1;
        }

        .bg-light-purple {
            background: rgba(128, 90, 213, 0.1);
            color: #805ad5;
        }

        .border-accent-green {
            border-bottom: 4px solid #48bb78;
        }

        .text-green {
            color: #2f855a;
        }

        .bg-light-green {
            background: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
    </style>

    <div
        class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h1 class="dashboard-title">Dashboard Tổng Quan</h1>
            <p class="dashboard-subtitle">Hệ thống quản lý Hải An Elevator - Báo cáo thời gian thực.</p>
        </div>
        <div class="update-badge">
            <span class="dot-online"></span>
            Cập nhật: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- ===== BANNER THÔNG BÁO ===== --}}
    @php
        $isAdmin = auth()->user()->role?->name === 'admin';
    @endphp

    {{-- Banner cho Admin: Thang máy đến hạn/quá hạn bảo trì --}}
    @if ($isAdmin && $overdueElevators->count() > 0)
        <div class="mb-4"
            style="background:#fff5f5;border:1px solid #fed7d7;border-left:4px solid #e53e3e;border-radius:12px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;animation:slideInBanner 0.4s ease;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div
                    style="width:40px;height:40px;background:#fed7d7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-bell" style="color:#e53e3e;font-size:1.1rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;color:#c53030;font-size:0.95rem;">Cảnh báo: Cần lập lịch bảo trì!</div>
                    <div style="color:#718096;font-size:0.85rem;">
                        Có <strong style="color:#e53e3e;">{{ $overdueElevators->count() }}</strong> thang máy quá hạn hoặc sắp đến hạn (trong 15 ngày) <strong style="color:#e53e3e;">chưa được tạo lịch bảo trì</strong>.
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.maintenance.due') }}"
                style="background:#e53e3e;color:#fff;border-radius:8px;padding:7px 18px;font-size:0.85rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                Xem tất cả
            </a>
        </div>
    @endif

    <style>
        @keyframes slideInBanner {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="row g-4 mb-5">
        <!-- TỔNG THANG MÁY -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card premium-card h-100 border-accent-blue">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <div class="card-stat-label">Tổng Thang Máy</div>
                        <div class="card-stat-value text-dark">{{ number_format($totalElevators) }}</div>
                    </div>
                    <div class="card-stat-icon bg-light-blue">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KHÁCH HÀNG BÁO SỰ CỐ -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card premium-card h-100 border-accent-red">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <div class="card-stat-label">KHÁCH HÀNG BÁO SỰ CỐ</div>
                        <div class="card-stat-value text-red">{{ number_format($activeIncidents) }}</div>
                    </div>
                    <div class="card-stat-icon bg-light-red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ĐANG BẢO TRÌ -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card premium-card h-100 border-accent-orange">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <div class="card-stat-label">Đang bảo trì</div>
                        <div class="card-stat-value text-orange">{{ number_format($ongoingMaintenance) }}</div>
                    </div>
                    <div class="card-stat-icon bg-light-orange">
                        <i class="fas fa-wrench"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- SẮP ĐẾN HẠN (30 NGÀY) -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card premium-card h-100 border-accent-purple">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <div class="card-stat-label">Sắp đến hạn (30 ngày)</div>
                        <div class="card-stat-value text-purple">{{ number_format($maintenanceDue) }}</div>
                    </div>
                    <div class="card-stat-icon bg-light-purple">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KHÁCH HÀNG -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card premium-card h-100 border-accent-green">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <div class="card-stat-label">Khách hàng (Tòa nhà)</div>
                        <div class="card-stat-value text-green">{{ number_format($totalBuildings) }}</div>
                    </div>
                    <div class="card-stat-icon bg-light-green">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .chart-card {
            border: none;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            padding: 25px;
            height: 100%;
        }

        .chart-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-list {
            list-style: none;
            padding: 0;
            margin-top: 25px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .status-label-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-count {
            font-weight: 800;
            font-size: 1.1rem;
            color: #1a202c;
        }

        .priority-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .reminder-card {
            background: #fff;
            border: 1px solid #fef2f2;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s ease;
        }

        .reminder-card:hover {
            border-color: #fee2e2;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.05);
        }

        .reminder-icon {
            width: 45px;
            height: 45px;
            background: #fff5f5;
            color: #e53e3e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .customer-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .customer-item:last-child {
            border-bottom: none;
        }

        .badge-priority {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 50px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .reminder-list,
        .customer-list,
        .order-list {
            max-height: 390px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Tùy chỉnh thanh cuộn cho danh sách */
        .reminder-list::-webkit-scrollbar,
        .customer-list::-webkit-scrollbar,
        .order-list::-webkit-scrollbar {
            width: 4px;
        }

        .reminder-list::-webkit-scrollbar-track,
        .customer-list::-webkit-scrollbar-track,
        .order-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .reminder-list::-webkit-scrollbar-thumb,
        .customer-list::-webkit-scrollbar-thumb,
        .order-list::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .reminder-list::-webkit-scrollbar-thumb:hover,
        .customer-list::-webkit-scrollbar-thumb:hover,
        .order-list::-webkit-scrollbar-thumb:hover {
            background: #cbd5e0;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .badge-status {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>

    <div class="row g-4 mb-5">
        <!-- XU HƯỚNG BẢO TRÌ -->
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-chart-line text-blue"></i>
                    <h6 class="chart-title mb-0">XU HƯỚNG BẢO TRÌ (THÁNG)</h6>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="maintenanceTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TRẠNG THÁI THIẾT BỊ -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h6 class="chart-title mb-0">TRẠNG THÁI THIẾT BỊ</h6>
                </div>
                <div style="height: 200px; position: relative;">
                    <canvas id="deviceStatusChart"></canvas>
                </div>
                <ul class="status-list">
                    <li class="status-item">
                        <div class="status-label-group">
                            <div class="status-dot" style="background: #48bb78;"></div>
                            <span class="status-text">Hoạt động</span>
                        </div>
                        <span class="status-count">{{ number_format($deviceStats['active']) }}</span>
                    </li>
                    <li class="status-item">
                        <div class="status-label-group">
                            <div class="status-dot" style="background: #3182ce;"></div>
                            <span class="status-text">Bảo trì</span>
                        </div>
                        <span class="status-count">{{ number_format($deviceStats['maintenance']) }}</span>
                    </li>
                    <li class="status-item">
                        <div class="status-label-group">
                            <div class="status-dot" style="background: #e53e3e;"></div>
                            <span class="status-text">Sự cố</span>
                        </div>
                        <span class="status-count">{{ number_format($deviceStats['fault']) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- HÀNG THỨ 3: CHI TIẾT VẬN HÀNH -->
    <div class="row g-4 mb-5">
        <!-- THỐNG KÊ SỰ CỐ -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-exclamation-circle text-red"></i>
                    <h6 class="chart-title mb-0">THỐNG KÊ SỰ CỐ</h6>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="incidentBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- LỊCH NHẮC BẢO TRÌ CHU KỲ -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-bell text-warning"></i>
                    <h6 class="chart-title mb-0">LỊCH NHẮC BẢO TRÌ CHU KỲ</h6>
                </div>
                <div class="reminder-list mt-3">
                    @foreach ($maintenanceReminders as $reminder)
                        <div class="reminder-card">
                            <div class="reminder-icon">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark">{{ $reminder['code'] }}</div>
                                <div class="text-muted small">{{ $reminder['contact'] }}</div>
                            </div>
                            <div class="text-end">
                                <div class="small fw-bold" style="color: {{ $reminder['color'] }}">
                                    {{ $reminder['label'] }}</div>
                                <div class="small fw-bold" style="color: {{ $reminder['color'] }}">
                                    {{ $reminder['deadline'] }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Chu kỳ: {{ $reminder['cycle'] }} ngày
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- KHÁCH HÀNG SẮP ĐẾN HẠN -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-calendar-check text-purple"></i>
                    <h6 class="chart-title mb-0">KHÁCH HÀNG SẮP ĐẾN HẠN BẢO TRÌ</h6>
                </div>
                <div class="customer-list mt-2">
                    @foreach ($dueCustomers as $customer)
                        <div class="customer-item">
                            <div class="priority-circle"
                                style="background: {{ $customer['days_left'] <= 0 ? '#fff5f5' : ($customer['days_left'] <= 5 ? '#fffaf0' : '#ebf8ff') }}; color: {{ $customer['days_left'] <= 0 ? '#c53030' : ($customer['days_left'] <= 5 ? '#c05621' : '#2b6cb0') }};">
                                {{ $customer['days_left'] }}d
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="fw-bold text-dark small">{{ $customer['building_name'] }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $customer['deadline'] }}</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $customer['customer_name'] }}
                                    </div>
                                    <span
                                        class="badge-priority {{ $customer['badge_class'] }}">{{ $customer['priority'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG THỨ 4: DOANH THU & ĐƠN HÀNG -->
    <div class="row g-4 mb-5">
        <!-- BIỂU ĐỒ DOANH THU 15 NGÀY -->
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-money-bill-wave text-success"></i>
                    <h6 class="chart-title mb-0">XU HƯỚNG DOANH THU (15 NGÀY GẦN NHẤT)</h6>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ĐƠN HÀNG GẦN ĐÂY -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-shopping-cart text-blue"></i>
                    <h6 class="chart-title mb-0">ĐƠN HÀNG GẦN ĐÂY</h6>
                </div>
                <div class="order-list mt-2">
                    @foreach ($recentOrders as $order)
                        <div class="order-item">
                            <div class="bg-light-blue p-2 rounded"
                                style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-invoice" style="font-size: 1.2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="fw-bold text-dark small">{{ $order['code'] }}</div>
                                    <div class="fw-bold text-success small">{{ $order['total_amount'] }}</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $order['building_name'] }}
                                    </div>
                                    <span
                                        class="badge-status {{ $order['status_class'] }}">{{ $order['status_label'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Xu hướng bảo trì (Cũ)
            const trendCtx = document.getElementById('maintenanceTrendChart').getContext('2d');
            const blueGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
            blueGradient.addColorStop(0, 'rgba(49, 130, 206, 0.2)');
            blueGradient.addColorStop(1, 'rgba(49, 130, 206, 0)');

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($maintenanceLabels),
                    datasets: [{
                        label: 'Phiếu bảo trì',
                        data: @json($maintenanceTrend),
                        borderColor: '#3182ce',
                        borderWidth: 3,
                        backgroundColor: blueGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#3182ce',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#1a202c',
                            bodyColor: '#4a5568',
                            borderColor: '#edf2f7',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 4,
                            usePointStyle: true,
                            callbacks: {
                                title: (items) => items[0].label,
                                label: (item) => ` value : ${item.formattedValue}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // 2. Trạng thái thiết bị (Cũ)
            const statusCtx = document.getElementById('deviceStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hoạt động', 'Bảo trì', 'Sự cố'],
                    datasets: [{
                        data: [
                            {{ $deviceStats['active'] }},
                            {{ $deviceStats['maintenance'] }},
                            {{ $deviceStats['fault'] }}
                        ],
                        backgroundColor: ['#48bb78', '#3182ce', '#e53e3e'],
                        hoverOffset: 4,
                        borderWidth: 0,
                        cutout: '75%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 3. Thống kê sự cố (Mới)
            const incidentCtx = document.getElementById('incidentBarChart').getContext('2d');
            new Chart(incidentCtx, {
                type: 'bar',
                data: {
                    labels: @json($incidentLabels),
                    datasets: [{
                        label: 'Số sự cố',
                        data: @json($incidentTrends),
                        backgroundColor: '#f56565',
                        borderRadius: 8,
                        barThickness: 25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#a0aec0',
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // 4. Doanh thu 15 ngày (Mới)
            const revenueCtx = document.getElementById('revenueTrendChart')?.getContext('2d');
            if (revenueCtx) {
                const greenGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
                greenGradient.addColorStop(0, 'rgba(72, 187, 120, 0.2)');
                greenGradient.addColorStop(1, 'rgba(72, 187, 120, 0)');

                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: @json($orderLabels),
                        datasets: [{
                            label: 'Doanh thu',
                            data: @json($orderTrendData),
                            borderColor: '#48bb78',
                            borderWidth: 3,
                            backgroundColor: greenGradient,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#48bb78',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = 'Doanh thu: ';
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('vi-VN', {
                                                style: 'currency',
                                                currency: 'VND'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN').format(value);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
