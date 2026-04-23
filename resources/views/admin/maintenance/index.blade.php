@extends('layouts.admin')

@section('title', 'Lịch bảo trì & Công việc')

@section('styles')
    <style>
        .schedule-card {
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            transition: all 0.2s;
            background: #fff;
        }

        .schedule-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .badge-soft-primary {
            background: #e3f2fd;
            color: #0d6efd;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-warning {
            background: #fff3cd;
            color: #856404;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-success {
            background: #d1e7dd;
            color: #0f5132;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .badge-soft-secondary {
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        /* Calendar Grid */
        .calendar-container {
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .calendar-header div {
            padding: 15px;
            text-align: center;
            font-weight: 700;
            color: #4e73df;
            font-size: 0.85rem;
        }

        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .calendar-day {
            min-height: 120px;
            padding: 10px;
            border-right: 1px solid #e3e6f0;
            border-bottom: 1px solid #e3e6f0;
            background: #fff;
        }

        .calendar-day:nth-child(7n) {
            border-right: none;
        }

        .calendar-day.today {
            background: #f0f8ff;
            box-shadow: inset 0 0 0 2px #4e73df;
        }

        .calendar-day.empty {
            background: #fcfcfc;
        }

        .day-number {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .event-pill {
            display: block;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .event-pill:hover {
            opacity: 0.9;
            color: #fff;
            text-decoration: none;
        }

        .event-periodic {
            background-color: #3b82f6;
        }

        /* Blue */
        .event-repair {
            background-color: #10b981;
        }

        /* Green */
        .event-pending {
            opacity: 0.7;
        }

        /* Dashboard Stylings */
        .stat-bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            gap: 12px;
        }

        .stat-bar {
            width: 100%;
            max-width: 80px;
            min-height: 8px;
            border-radius: 6px;
            transition: height 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .completion-rate-card {
            background: #f0fdf4;
            border-radius: 20px;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .completion-rate-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(16, 185, 129, 0.05);
            border-radius: 50%;
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
                            <i class="fas fa-plus me-2"></i> Tạo phiếu bảo trì
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
                    style="letter-spacing: 0.5px;">
                    <i class="fas fa-chart-bar me-2 text-primary"></i> Trạng thái công việc tháng này
                </h6>
                <div class="d-flex align-items-end justify-content-around px-2 px-md-4" style="height: 160px;">
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['completed']) }}px; background: #10b981;"></div>
                        <span class="small fw-bold text-muted mt-2">Hoàn thành</span>
                    </div>
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['in_progress']) }}px; background: #f59e0b;"></div>
                        <span class="small fw-bold text-muted mt-2">Đang làm</span>
                    </div>
                    <div class="stat-bar-container">
                        <div class="stat-bar shadow-sm"
                            style="height: {{ $getBarHeight($stats['pending']) }}px; background: #3b82f6;"></div>
                        <span class="small fw-bold text-muted mt-2">Chưa làm</span>
                    </div>
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
            <div class="row g-4">
                <div class="col-lg-12">
                    @forelse($upcomingTasks as $task)
                        <div class="schedule-card p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <h5 class="mb-0 fw-bold text-primary">{{ $task->elevator->code ?? 'N/A' }}</h5>
                                        <span class="badge-soft-secondary border-0">
                                            {{ $task->task_type == 'repair' ? 'Sửa chữa' : 'Bảo dưỡng định kỳ' }}
                                        </span>
                                        @if ($task->status == 'completed')
                                            <span class="badge-soft-success">Hoàn thành</span>
                                        @elseif($task->status == 'in_progress')
                                            <span class="badge-soft-warning">Đang thực hiện</span>
                                        @elseif($task->status == 'overdue')
                                            <span class="badge-soft-secondary text-danger border-danger">Quá hạn</span>
                                        @else
                                            <span class="badge-soft-primary bg-light text-muted border">Chờ xử lý</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-4 text-muted small mt-3">
                                        <div><i
                                                class="fas fa-map-marker-alt me-2"></i>{{ $task->elevator->building->name ?? 'N/A' }}
                                        </div>
                                        @php
                                            $displayDate = $task->check_date;
                                        @endphp
                                        <div><i
                                                class="far fa-calendar me-2"></i>{{ $displayDate ? $displayDate->format('Y-m-d') : 'N/A' }}
                                        </div>
                                        @if ($task->start_time)
                                            <div><i class="far fa-clock me-2"></i>{{ $task->start_time }} -
                                                {{ $task->end_time ?? '...' }}</div>
                                        @endif
                                        <div><i class="far fa-user me-2"></i>{{ $task->staff_names ?? 'Chưa phân công' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @can('view_maintenance_schedule')
                                        <a href="{{ route('admin.maintenance.show', $task->id) }}"
                                            class="btn btn-outline-secondary fw-bold px-4 rounded-3">Chi tiết</a>
                                    @endcan
                                    @if ($task->status != 'completed')
                                        @can('update_maintenance_schedule')
                                            <a href="{{ route('admin.maintenance.edit', $task->id) }}"
                                                class="btn btn-success fw-bold px-3 rounded-3 shadow-sm">
                                                <i class="fas fa-check-square me-1"></i> Hoàn thành
                                            </a>
                                        @endcan
                                    @endif

                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0 shadow-none border-0 px-2"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @can('view_maintenance_schedule')
                                                <li>
                                                    <a class="dropdown-item small"
                                                        href="{{ route('admin.maintenance.export', $task->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-print me-2 text-info"></i>
                                                        Xuất phiếu (PDF)
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('update_maintenance_schedule')
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item small"
                                                        href="{{ route('admin.maintenance.edit', $task->id) }}">
                                                        <i class="fas fa-edit me-2 text-primary"></i>
                                                        {{ $task->status == 'completed' ? 'Chỉnh sửa kết quả' : 'Chỉnh sửa' }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('delete_maintenance_schedule')
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.maintenance.destroy', $task->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item small text-danger"><i
                                                                class="fas fa-trash-alt me-2"></i> Xóa lịch</button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted border rounded-4 bg-white">
                            <i class="far fa-calendar-check fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Không có công việc nào sắp tới.</p>
                        </div>
                    @endforelse
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

            <div class="calendar-container shadow-sm">
                <div class="calendar-header">
                    <div>T2</div>
                    <div>T3</div>
                    <div>T4</div>
                    <div>T5</div>
                    <div>T6</div>
                    <div>T7</div>
                    <div>CN</div>
                </div>
                <div class="calendar-body">
                    @php
                        $day = $startOfCalendar->copy();
                    @endphp
                    @while ($day <= $endOfCalendar)
                        @php
                            $isToday = $day->isToday();
                            $isCurrentMonth = $day->month == $date->month;
                            $dateStr = $day->format('Y-m-d');
                            $dayTasks = $groupedTasks[$dateStr] ?? [];
                        @endphp
                        <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ !$isCurrentMonth ? 'empty' : '' }}">
                            <div class="day-number" style="opacity: {{ $isCurrentMonth ? '1' : '0.3' }}">
                                {{ $day->day }}</div>
                            @foreach ($dayTasks as $t)
                                @php
                                    // Default colors based on type
                                    $pillClass = $t->task_type == 'repair' ? 'event-repair' : 'event-periodic';

                                    // Modifier based on status
                                    if ($t->status == 'pending') {
                                        $pillClass .= ' event-pending';
                                    }
                                    if ($t->status == 'overdue') {
                                        $pillClass = 'bg-danger text-white event-pending';
                                    } // Red for overdue
                                @endphp
                                <a href="{{ route('admin.maintenance.show', $t->id) }}"
                                    class="event-pill {{ $pillClass }}" title="{{ $t->elevator->code }}">
                                    {{ $t->elevator->code }}
                                </a>
                            @endforeach
                        </div>
                        @php
                            $day->addDay();
                        @endphp
                    @endwhile
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            // Tab persistence via URL hash
            document.addEventListener('DOMContentLoaded', function() {
                let hash = window.location.hash;
                if (hash) {
                    let tabTrigger = document.querySelector('button[data-bs-target="' + hash + '"]');
                    if (tabTrigger) {
                        let tab = new bootstrap.Tab(tabTrigger);
                        tab.show();
                    }
                }

                // Update URL hash when a tab is clicked
                let tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
                tabEls.forEach(function(tabEl) {
                    tabEl.addEventListener('shown.bs.tab', function(event) {
                        let targetId = event.target.getAttribute('data-bs-target');
                        if (history.replaceState) {
                            history.replaceState(null, null, targetId);
                        } else {
                            window.location.hash = targetId;
                        }
                    });
                });
            });
        </script>
    @endsection
@endsection
