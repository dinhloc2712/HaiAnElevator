@extends('layouts.admin')

@section('title', 'Quản lý Bảo trì')

@section('styles')
    <style>
        .schedule-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e3e6f0;
            transition: all 0.2s;
            border-left: 4px solid #4e73df;
        }

        .schedule-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .badge-soft-primary {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-warning {
            background-color: rgba(246, 194, 62, 0.1);
            color: #f6c23e;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-success {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-secondary {
            background-color: rgba(133, 135, 150, 0.1);
            color: #858796;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .nav-pills .nav-link {
            color: #5a5c69;
            border-radius: 8px;
            padding: 8px 20px;
            transition: all 0.2s;
        }

        .nav-pills .nav-link.active {
            background-color: #4e73df;
            color: #fff;
            box-shadow: 0 2px 5px rgba(78, 115, 223, 0.3);
        }

        .calendar-container {
            background: #fff;
            border-radius: 15px;
            border: 1px solid #e3e6f0;
            overflow: hidden;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: bold;
            background: #f8f9fc;
            padding: 15px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e3e6f0;
        }

        .calendar-day {
            background: #fff;
            min-height: 120px;
            padding: 10px;
            transition: all 0.2s;
        }

        .calendar-day:hover {
            background: #f8f9fc;
        }

        .calendar-day.other-month {
            background: #fafafa;
            color: #b7b9cc;
        }

        .calendar-day.today {
            background: rgba(78, 115, 223, 0.05);
        }

        .day-number {
            font-weight: bold;
            color: #5a5c69;
            margin-bottom: 5px;
            display: inline-block;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            border-radius: 50%;
        }

        .calendar-day.today .day-number {
            background: #4e73df;
            color: #fff;
        }

        .task-indicator {
            font-size: 0.7rem;
            padding: 4px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .task-indicator.pending {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            border-left-color: #4e73df;
        }

        .task-indicator.in_progress {
            background: rgba(246, 194, 62, 0.1);
            color: #f6c23e;
            border-left-color: #f6c23e;
        }

        .task-indicator.completed {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
            border-left-color: #1cc88a;
        }

        .task-indicator.overdue {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
            border-left-color: #e74a3b;
        }

        .stat-card-modern {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
        }

        .stat-card-modern:hover {
            transform: translateY(-3px);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .bg-gradient-primary-soft {
            background: linear-gradient(135deg, rgba(78, 115, 223, 0.1) 0%, rgba(34, 74, 190, 0.1) 100%);
            color: #4e73df;
        }

        .bg-gradient-success-soft {
            background: linear-gradient(135deg, rgba(28, 200, 138, 0.1) 0%, rgba(19, 133, 92, 0.1) 100%);
            color: #1cc88a;
        }

        .bg-gradient-warning-soft {
            background: linear-gradient(135deg, rgba(246, 194, 62, 0.1) 0%, rgba(218, 165, 32, 0.1) 100%);
            color: #f6c23e;
        }

        .bg-gradient-danger-soft {
            background: linear-gradient(135deg, rgba(231, 74, 59, 0.1) 0%, rgba(190, 38, 23, 0.1) 100%);
            color: #e74a3b;
        }

        /* Thêm style cho thanh thống kê bar */
        .stat-bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 150px;
        }

        .stat-bar {
            width: 40px;
            border-radius: 8px;
            transition: height 1s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .stat-bar::after {
            content: attr(data-value);
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-weight: 800;
            font-size: 0.9rem;
            color: #4b5563;
        }
        
        .table-responsive {
            border-radius: 0 0 12px 12px;
        }
        .table thead th {
            letter-spacing: 0.5px;
            background: #f8f9fc;
            padding-top: 15px;
            padding-bottom: 15px;
        }
        .table tbody td {
            padding-top: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f3f9;
        }
    </style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Lịch bảo trì & Công việc</h1>
                <p class="mb-0 text-muted small">Quản lý lịch bảo dưỡng định kỳ và phân công kỹ thuật viên.</p>
            </div>

            <div class="d-flex gap-3 align-items-center">
                <ul class="nav nav-pills custom-pills" id="scheduleTab" role="tablist"
                    style="background: #fff; border-radius: 10px; padding: 4px; border: 1px solid #e3e6f0;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-3 px-4 py-2 fw-bold text-dark" id="list-tab"
                            data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
                            <i class="fas fa-list me-2"></i> Danh sách
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 px-4 py-2 fw-bold text-dark" id="calendar-tab"
                            data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                            <i class="far fa-calendar-alt me-2"></i> Lịch
                        </button>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.maintenance.settings') }}"
                        class="btn btn-outline-primary rounded-3 px-3 shadow-sm" title="Cài đặt hệ thống">
                        <i class="fas fa-cog"></i>
                    </a>

                    @can('create_maintenance_schedule')
                        <a href="{{ route('admin.maintenance.create') }}" class="btn btn-tech-success rounded-3 px-4 shadow-sm">
                            <i class="fas fa-plus me-md-2"></i><span class="d-none d-md-inline"> Tạo phiếu bảo trì</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @php
        $maxVal = max($stats['completed'], $stats['in_progress'], $stats['pending'], $stats['overdue'], 1);
        $getBarHeight = function ($val) use ($maxVal) {
            return ($val / $maxVal) * 120 + 8; // Max height 120px + 8px min
        };
    @endphp

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="tech-card h-100 p-4 shadow-sm border-0" style="border-radius: 20px; background: white;">
                <h6 class="text-uppercase small fw-bold text-muted mb-5 d-flex align-items-center"
                    style="letter-spacing: 1px;">
                    <i class="fas fa-chart-bar me-2 text-primary"></i> Tổng quan tháng này
                </h6>
                <div class="d-flex justify-content-around align-items-end" style="height: 180px;">
                    <!-- Đã hoàn thành -->
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['completed']) }}px; background: #10b981;"></div>
                        <span class="small fw-bold text-muted mt-2">Hoàn thành</span>
                    </div>
                    <!-- Đang thực hiện -->
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['in_progress']) }}px; background: #f59e0b;"></div>
                        <span class="small fw-bold text-muted mt-2">Đang làm</span>
                    </div>
                    <!-- Chờ xử lý -->
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['pending']) }}px; background: #3b82f6;"></div>
                        <span class="small fw-bold text-muted mt-2">Chờ xử lý</span>
                    </div>
                    <!-- Quá hạn -->
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['overdue']) }}px; background: #ef4444;"></div>
                        <span class="small fw-bold text-muted mt-2">Quá hạn</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="completion-rate-card h-100 shadow-sm border-0 d-flex flex-column justify-content-center p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 24px; height: 24px;">
                        <i class="fas fa-check small" style="font-size: 0.7rem;"></i>
                    </div>
                    <h6 class="text-success small fw-bold mb-0" style="letter-spacing: 0.5px;">TỶ LỆ HOÀN THÀNH</h6>
                </div>
                <div class="mb-2">
                    <span class="display-4 fw-bold text-success"
                        style="line-height: 1;">{{ $stats['completion_rate'] }}%</span>
                </div>
                <div class="badge bg-white shadow-sm border-0 py-2 px-3 align-self-start rounded-pill">
                    <span class="text-{{ $trend >= 0 ? 'success' : 'danger' }} small fw-bold">
                        <i class="fas fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }} me-1"></i>
                        {{ abs($trend) }}% so với tháng trước
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content" id="scheduleTabContent">
        {{-- LIST VIEW --}}
        <div class="tab-pane fade show active" id="list" role="tabpanel">
            <h5 class="fw-bold mb-4">Công việc sắp tới & Đang thực hiện</h5>
            <div class="tech-card overflow-hidden">
                <div class="table-responsive" style="border-radius: 0 0 12px 12px; min-height: 300px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 small fw-bold text-muted text-nowrap" style="width: 120px;">MÃ THANG</th>
                                <th class="border-0 small fw-bold text-muted text-nowrap" style="width: 150px;">LOẠI BẢO TRÌ</th>
                                <th class="border-0 small fw-bold text-muted text-nowrap" style="width: 120px;">TRẠNG THÁI</th>
                                <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 180px;">THỜI GIAN</th>
                                <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 150px;">NHÂN VIÊN</th>
                                <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 250px;">ĐỊA ĐIỂM</th>
                                <th class="pe-4 border-0 text-end small fw-bold text-muted text-nowrap" style="width: 180px;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingTasks as $task)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary text-nowrap">{{ $task->elevator->code ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge-soft-secondary border-0 text-nowrap">
                                            {{ $task->task_type == 'repair' ? 'Sửa chữa' : 'Bảo dưỡng định kỳ' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($task->status == 'completed')
                                            <span class="badge-soft-success text-nowrap">Hoàn thành</span>
                                        @elseif($task->status == 'in_progress')
                                            <span class="badge-soft-warning text-nowrap">Đang thực hiện</span>
                                        @elseif($task->status == 'overdue')
                                            <span class="badge-soft-secondary text-danger border-danger text-nowrap">Quá hạn</span>
                                        @else
                                            <span class="badge-soft-primary bg-light text-muted border text-nowrap">Chờ xử lý</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $displayDate = $task->check_date;
                                        @endphp
                                        <div class="fw-bold text-dark text-nowrap">
                                            <i class="far fa-calendar text-muted me-1"></i> {{ $displayDate ? $displayDate->format('d/m/Y') : 'N/A' }}
                                        </div>
                                        @if ($task->start_time)
                                            <div class="small text-muted text-nowrap">
                                                <i class="far fa-clock me-1"></i> {{ date('H:i', strtotime($task->start_time)) }} - {{ $task->end_time ? date('H:i', strtotime($task->end_time)) : '...' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-nowrap fw-bold text-dark small">
                                            <i class="far fa-user text-muted me-1"></i> {{ $task->staff_names ?? 'Chưa phân công' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 250px;" title="{{ $task->elevator->building->name ?? 'N/A' }}">
                                            {{ $task->elevator->building->name ?? 'N/A' }}
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;" title="{{ $task->elevator->building->address ?? 'N/A' }}">
                                            <i class="fas fa-map-marker-alt me-1"></i> {{ $task->elevator->building->address ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            @if ($task->status == 'pending' || $task->status == 'overdue')
                                                @can('update_maintenance_schedule')
                                                    <form action="{{ route('admin.maintenance.start', $task->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 rounded-pill shadow-sm">
                                                            <i class="fas fa-play me-1"></i> Thực hiện
                                                        </button>
                                                    </form>
                                                @endcan
                                            @elseif($task->status == 'in_progress')
                                                @can('update_maintenance_schedule')
                                                    <a href="{{ route('admin.maintenance.edit', $task->id) }}"
                                                        class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm">
                                                        <i class="fas fa-check-square me-1"></i> Hoàn thành
                                                    </a>
                                                @endcan
                                            @endif

                                            <div class="dropdown {{ $loop->last ? 'dropup' : '' }}">
                                                <button class="btn btn-link text-muted p-0 shadow-none px-2" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-strategy="fixed">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    @can('view_maintenance_schedule')
                                                        <li>
                                                            <a class="dropdown-item small" href="{{ route('admin.maintenance.show', $task->id) }}">
                                                                <i class="far fa-eye me-2 text-secondary"></i> Chi tiết
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item small"
                                                                href="{{ route('admin.maintenance.export', $task->id) }}"
                                                                target="_blank">
                                                                <i class="fas fa-print me-2 text-info"></i> Xuất phiếu (PDF)
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('update_maintenance_schedule')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item small" href="{{ route('admin.maintenance.edit', $task->id) }}">
                                                                <i class="fas fa-edit me-2 text-primary"></i>
                                                                {{ $task->status == 'completed' ? 'Chỉnh sửa kết quả' : 'Chỉnh sửa' }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('delete_maintenance_schedule')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('admin.maintenance.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item small text-danger">
                                                                    <i class="fas fa-trash-alt me-2"></i> Xóa lịch
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted border-0">
                                        <i class="far fa-calendar-check fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">Không có công việc nào sắp tới.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CALENDAR VIEW --}}
        <div class="tab-pane fade" id="calendar" role="tabpanel">
            @php
                $now = \Carbon\Carbon::now();
                $month = request('month', $now->month);
                $year = request('year', $now->year);

                $date = \Carbon\Carbon::createFromDate($year, $month, 1);
                $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon\Carbon::MONDAY);
                $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon\Carbon::SUNDAY);

                // Group tasks by date string
                $groupedTasks = [];
                foreach ($upcomingTasks as $t) {
                    $workDate = $t->check_date;
                    if ($workDate) {
                        $dateStr = $workDate->format('Y-m-d');
                        if (!isset($groupedTasks[$dateStr])) {
                            $groupedTasks[$dateStr] = [];
                        }
                        $groupedTasks[$dateStr][] = $t;
                    }
                }
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Tháng {{ $date->format('n - Y') }}</h4>
                <div class="btn-group">
                    <a href="?month={{ $date->copy()->subMonth()->month }}&year={{ $date->copy()->subMonth()->year }}#calendar"
                        class="btn btn-outline-secondary px-3"><i class="fas fa-chevron-left"></i></a>
                    <a href="?month={{ $date->copy()->addMonth()->month }}&year={{ $date->copy()->addMonth()->year }}#calendar"
                        class="btn btn-outline-secondary px-3"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="table-responsive border-0">
                <div class="calendar-container shadow-sm" style="min-width: 800px;">
                <div class="calendar-header">
                    <div>T2</div>
                    <div>T3</div>
                    <div>T4</div>
                    <div>T5</div>
                    <div>T6</div>
                    <div>T7</div>
                    <div>CN</div>
                </div>

                <div class="calendar-grid">
                    @php
                        $currentDay = $startOfCalendar->copy();
                    @endphp

                    @while ($currentDay <= $endOfCalendar)
                        @php
                            $isOtherMonth = $currentDay->month != $month;
                            $isToday = $currentDay->isToday();
                            $dateStr = $currentDay->format('Y-m-d');
                            $dayTasks = $groupedTasks[$dateStr] ?? [];
                        @endphp
                        <div
                            class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                            <span class="day-number">{{ $currentDay->day }}</span>

                            <div class="tasks mt-1">
                                @foreach ($dayTasks as $task)
                                    <div class="task-indicator {{ $task->status }} shadow-sm"
                                        title="{{ $task->elevator->code ?? 'N/A' }} - {{ $task->status == 'completed' ? 'Hoàn thành' : 'Chưa xong' }}"
                                        onclick="window.location='{{ route('admin.maintenance.show', $task->id) }}'">
                                        <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                        {{ $task->start_time ? date('H:i', strtotime($task->start_time)) : '' }}
                                        {{ $task->elevator->code ?? 'N/A' }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @php
                            $currentDay->addDay();
                        @endphp
                    @endwhile
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Restore active tab from hash
            let hash = window.location.hash;
            if (hash) {
                let triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
                if (triggerEl) {
                    let tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            }

            // Update hash when tab changes
            var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabEls.forEach(function(el) {
                el.addEventListener('shown.bs.tab', function(event) {
                    window.location.hash = event.target.getAttribute('data-bs-target');
                });
            });
        });
    </script>
@endsection
