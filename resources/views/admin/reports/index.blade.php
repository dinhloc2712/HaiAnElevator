@extends('layouts.admin')

@section('title', 'Báo cáo & Phân tích')

@section('styles')
<style>
    /* Tabs & Content Styling */
    .report-tabs {
        border-bottom: 2px solid #f1f3f9;
        margin-bottom: 25px;
    }
    .report-tabs .nav-link {
        color: #6b7280;
        font-weight: 600;
        border: none;
        padding: 12px 24px;
        background: transparent;
        position: relative;
        transition: all 0.2s ease;
    }
    .report-tabs .nav-link:hover {
        color: #3b82f6;
    }
    .report-tabs .nav-link.active {
        color: #2563eb;
        background: transparent;
    }
    .report-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #2563eb;
    }

    .report-card {
        background: #fff;
        border: 1px solid #f1f3f9;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    
    .report-header-title {
        font-weight: 800;
        color: #111827;
        font-size: 1.7rem;
        letter-spacing: -0.5px;
    }
    
    .month-picker-container {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .month-picker-btn {
        background: transparent;
        border: none;
        padding: 8px 12px;
        color: #374151;
        cursor: pointer;
        transition: background 0.1s;
    }
    .month-picker-btn:hover { background: #f9fafb; }
    .month-picker-text {
        font-weight: 700;
        padding: 0 16px;
        color: #111827;
    }

    /* Table styling */
    .table-report {
        margin-bottom: 0;
    }
    .table-report thead th {
        border-bottom: 2px solid #f3f4f6;
        color: #6b7280;
        font-weight: 700;
        font-size: 0.85rem;
        background: #f9fafb;
        padding: 12px 16px;
    }
    .table-report tbody td {
        vertical-align: middle;
        padding: 16px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    .badge-status {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
    }
    .status-qua-han { background: #7f1d1d; color: #fca5a5; }
    .status-den-han { background: #fee2e2; color: #ef4444; }
    .status-sap-den { background: #f3f4f6; color: #374151; }

    .rating-star { color: #f59e0b; font-weight: 700; }
    
    .count-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .count-completed { background: #d1fae5; color: #10b981; }
    .count-progress { background: #fef3c7; color: #f59e0b; }
    .count-pending { background: #dbeafe; color: #3b82f6; }
</style>
@endsection

@section('content')
@php
    $prevMonth = $date->copy()->subMonth();
    $nextMonth = $date->copy()->addMonth();
    $strMonthYear = 'tháng ' . str_pad($month, 2, '0', STR_PAD_LEFT) . ' ' . $year;
@endphp

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="report-header-title mb-1">Báo cáo & Phân tích</h1>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">Dữ liệu tổng hợp và báo cáo chi tiết tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }} năm {{ $year }}.</p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="month-picker-container">
                <a href="?month={{ $prevMonth->month }}&year={{ $prevMonth->year }}" class="month-picker-btn text-decoration-none">
                    <i class="fas fa-chevron-left small"></i>
                </a>
                <div class="month-picker-text">{{ str_pad($month, 2, '0', STR_PAD_LEFT) }} / {{ $year }}</div>
                <a href="?month={{ $nextMonth->month }}&year={{ $nextMonth->year }}" class="month-picker-btn text-decoration-none">
                    <i class="fas fa-chevron-right small"></i>
                </a>
            </div>
            
            <button class="btn btn-primary fw-bold rounded-3 px-4 shadow-sm" onclick="window.print()">
                <i class="fas fa-file-download me-2"></i> Xuất dữ liệu
            </button>
        </div>
    </div>
</div>

<ul class="nav nav-tabs report-tabs" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Tổng quan</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">Danh sách bảo trì</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">Công việc nhân viên</button>
    </li>
</ul>

<div class="tab-content" id="reportTabContent">
    <!-- TAB 1: TỔNG QUAN -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="row g-4">
            <!-- Biểu đồ phân loại lỗi -->
            <div class="col-lg-5">
                <div class="report-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-4 d-flex align-items-center">
                        <i class="far fa-file-alt text-primary me-2"></i> Phân loại lỗi thường gặp
                    </h5>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="faultChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Biểu đồ Hiệu suất KTV -->
            <div class="col-lg-7">
                <div class="report-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-4 d-flex align-items-center">
                        <i class="fas fa-user-friends text-primary me-2"></i> Hiệu suất Kỹ thuật viên (Số task hoàn thành)
                    </h5>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="staffChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: DANH SÁCH BẢO TRÌ -->
    <div class="tab-pane fade" id="maintenance" role="tabpanel">
        <div class="report-card p-0 overflow-hidden">
            <div class="p-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="far fa-calendar-alt text-primary me-2"></i> Danh sách thang máy cần bảo trì {{ $strMonthYear }}
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-report">
                    <thead>
                        <tr>
                            <th>Mã thang máy</th>
                            <th>Tòa nhà</th>
                            <th>Chi nhánh</th>
                            <th>Lần bảo trì cuối</th>
                            <th>Lịch bảo trì kế tiếp</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dueElevators as $elv)
                            <tr>
                                <td class="fw-bold text-primary">{{ $elv->code }}</td>
                                <td>{{ $elv->building->name ?? 'N/A' }}</td>
                                <td>{{ $elv->branch->name ?? 'N/A' }}</td>
                                <td>
                                    {{ $elv->last_check_date ? $elv->last_check_date->format('Y-m-d') : 'Chưa có' }}
                                    @if($elv->source == 'deadline')
                                        <span class="ms-1 badge bg-light text-muted border" style="font-size: 0.72rem;">Hạn TD</span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    {{ $elv->next_check_date ? $elv->next_check_date->format('Y-m-d') : 'N/A' }}
                                </td>
                                <td>
                                    @php
                                        $today = now()->startOfDay();
                                        $checkDate = $elv->next_check_date ? $elv->next_check_date->startOfDay() : null;
                                        $isOverdue  = $checkDate && $checkDate < $today;
                                        $isDue      = $checkDate && !$isOverdue && $checkDate->diffInDays($today, false) >= -3;
                                        $isSoon     = $checkDate && !$isOverdue && !$isDue;
                                    @endphp
                                    @if($isOverdue)
                                        <span class="badge-status status-qua-han"><i class="fas fa-exclamation-triangle me-1" style="font-size:0.75rem;"></i>Quá hạn</span>
                                    @elseif($isDue)
                                        <span class="badge-status status-den-han">Đến hạn</span>
                                    @else
                                        <span class="badge-status status-sap-den">Sắp đến hạn</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Không có thang máy nào cần bảo trì trong tháng này.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: CÔNG VIỆC NHÂN VIÊN -->
    <div class="tab-pane fade" id="staff" role="tabpanel">
        <div class="report-card p-0 overflow-hidden">
            <div class="p-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fas fa-users text-primary me-2"></i> Tổng hợp công việc nhân viên {{ $strMonthYear }}
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-report text-center" style="vertical-align: middle;">
                    <thead style="text-align: left;">
                        <tr>
                            <th style="text-align: left;">Nhân viên</th>
                            <th class="text-center">Hoàn thành</th>
                            <th class="text-center">Đang làm</th>
                            <th class="text-center">Chờ xử lý</th>
                            <th class="text-center">Tổng cộng</th>
                            <th class="text-center">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        @forelse($staffPerformance as $staff)
                            <tr>
                                <td class="fw-bold text-dark">{{ $staff['name'] }}</td>
                                <td class="text-center"><span class="count-badge count-completed">{{ $staff['completed'] }}</span></td>
                                <td class="text-center"><span class="count-badge count-progress">{{ $staff['in_progress'] }}</span></td>
                                <td class="text-center"><span class="count-badge count-pending">{{ $staff['pending'] }}</span></td>
                                <td class="text-center fw-bold fs-5 text-dark">{{ $staff['total'] }}</td>
                                <td class="text-center">
                                    <span class="rating-star"><i class="fas fa-star me-1"></i> {{ number_format($staff['rating'], 1) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu trong tháng này.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6b7280';

        // 1. DOUGHNUT CHART (Faults)
        const ctxFault = document.getElementById('faultChart').getContext('2d');
        const faultLabels = {!! json_encode($faultStats['labels']) !!};
        const faultData = {!! json_encode($faultStats['data']) !!};

        new Chart(ctxFault, {
            type: 'doughnut',
            data: {
                labels: faultLabels,
                datasets: [{
                    data: faultData,
                    backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#10b981'],
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 13, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.chart._metasets[context.datasetIndex].total;
                                let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return label + ': ' + percentage + '%';
                            }
                        }
                    }
                }
            }
        });

        // 2. HORIZONTAL BAR CHART (Staff Performance)
        const ctxStaff = document.getElementById('staffChart').getContext('2d');
        const staffLabels = {!! json_encode($topStaffLabels) !!};
        const staffData = {!! json_encode($topStaffData) !!};

        new Chart(ctxStaff, {
            type: 'bar',
            data: {
                labels: staffLabels,
                datasets: [{
                    label: 'Số task hoàn thành',
                    data: staffData,
                    backgroundColor: '#10b981', // Green from design
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                indexAxis: 'y', // horizontal bar
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        grid: { borderDash: [4, 4], color: '#f3f4f6', drawBorder: false },
                        ticks: { stepSize: 5 }
                    },
                    y: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { size: 13, weight: '600' }, color: '#4b5563' }
                    }
                }
            }
        });
    });
</script>
@endsection
