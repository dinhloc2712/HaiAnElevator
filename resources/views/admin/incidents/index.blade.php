@extends('layouts.admin')

@section('title', 'Quản lý Sự cố')

@section('styles')
    <style>
        .incident-header-title {
            font-weight: 800;
            color: #111827;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
        }

        .filter-card {
            background: #fff;
            border: 1px solid #f1f3f9;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .filter-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-input {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 10px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            background-color: #f9fafb;
        }

        .filter-input:focus {
            background-color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        /* Table Styling */
        .table-incident thead th {
            background: #f9fafb;
            color: #4b5563;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-incident tbody td {
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        /* Badges */
        .badge-priority {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.7rem;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 100px;
        }

        .priority-emergency {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        .priority-high {
            background: #f97316;
            color: #fff;
        }

        .priority-medium {
            background: #fef08a;
            color: #854d0e;
        }

        .priority-low {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-status-dot {
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .status-new {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
        }

        .status-processing {
            background: #fefce8;
            color: #a16207;
            border: 1px solid #fef08a;
        }

        .status-resolved {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #dcfce7;
        }

        .status-canceled {
            background: #f9fafb;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        /* Action Buttons */
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #4b5563;
            text-decoration: none;
        }

        .btn-action:hover {
            background: #f9fafb;
            transform: translateY(-2px);
        }

        .btn-action-view:hover {
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .btn-action-edit:hover {
            color: #10b981;
            border-color: #10b981;
        }

        .btn-action-delete:hover {
            color: #ef4444;
            border-color: #ef4444;
        }

        .reporter-info {
            font-size: 0.8rem;
            color: #6b7280;
        }

        /* Stats Cards */
        .stat-card {
            background: #fff;
            border: 1px solid #f1f3f9;
            border-radius: 20px;
            padding: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.2s;
        }

        .stat-card-label {
            font-size: 0.8rem;
            font-weight: 800;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card-emergency {
            background: #fff5f5;
            border-color: #fee2e2;
        }

        .stat-card-emergency .stat-card-label {
            color: #e11d48;
        }

        .stat-card-emergency .stat-card-value {
            color: #e11d48;
        }

        .stat-card-processing {
            background: #f0f7ff;
            border-color: #dbeafe;
        }

        .stat-card-processing .stat-card-label {
            color: #2563eb;
        }

        .stat-card-processing .stat-card-value {
            color: #2563eb;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="incident-header-title mb-1">Quản lý Sự cố</h1>
            <p class="text-muted mb-0">Tổng hợp và xử lý các sự cố được báo từ khách hàng.</p>
        </div>
        @can('create_incident')
            <a href="{{ route('admin.incidents.create') }}" class="btn btn-danger fw-bold rounded-3 px-4 py-2 shadow-sm">
                <i class="fas fa-plus me-2"></i> Báo sự cố mới
            </a>
        @endcan
    </div>

    <!-- Thống kê -->
    <div class="row g-4 mb-4">
        <!-- Xu hướng -->
        <div class="col-lg-6">
            <div class="stat-card p-4">
                <div class="stat-card-label mb-3">
                    <i class="fas fa-chart-line text-danger me-1"></i> Xu hướng sự cố tuần này
                </div>
                <div style="height: 120px; width: 100%;">
                    <canvas id="incidentTrendChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Khẩn cấp -->
        <div class="col-lg-3">
            <div class="stat-card stat-card-emergency">
                <div class="stat-card-label">Sự cố khẩn cấp</div>
                <div class="stat-card-value">{{ str_pad($emergencyCount, 2, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
        <!-- Đang xử lý -->
        <div class="col-lg-3">
            <div class="stat-card stat-card-processing">
                <div class="stat-card-label">Đang xử lý</div>
                <div class="stat-card-value">{{ str_pad($processingCount, 2, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="filter-card p-4 mb-4">
        <div class="filter-label mb-3">
            <i class="fas fa-filter text-primary"></i> Bộ lọc sự cố
        </div>
        <form action="{{ route('admin.incidents.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="filter-label small"># Mã sự cố / Thang máy</label>
                    <input type="text" name="search" class="form-control filter-input" placeholder="VD: INC-2026-001..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="filter-label small"><i class="far fa-building"></i> Tòa nhà / Khách hàng</label>
                    <input type="text" name="building" class="form-control filter-input"
                        placeholder="Tìm kiếm khách hàng..." value="{{ request('building') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="filter-label small"><i class="fas fa-exclamation-triangle"></i> Mức độ ưu tiên</label>
                    <select name="priority" class="form-select filter-input">
                        <option value="">-- Tất cả --</option>
                        <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Khẩn cấp
                        </option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Cao</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="filter-label small"><i class="far fa-clock"></i> Trạng thái xử lý</label>
                    <select name="status" class="form-select filter-input">
                        <option value="">-- Tất cả --</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Mới báo</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý
                        </option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Hoàn thành
                        </option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-tech-primary w-100 py-2">
                        <i class="fas fa-search me-2"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách -->
    <div class="tech-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-incident mb-0">
                <thead>
                    <tr>
                        <th style="width: 180px;">Mã sự cố</th>
                        <th style="width: 180px;">Thang máy & Tòa nhà</th>
                        <th style="width: 300px; min-width: 250px;">Nội dung sự cố</th>
                        <th style="width: 200px;">Người báo</th>
                        <th style="width: 250px;">Nhân viên xử lý</th>
                        <th class="text-center" style="width: 90px;">Ưu tiên</th>
                        <th class="text-center" style="width: 110px;">Trạng thái</th>
                        <th style="width: 90px;">Ngày báo</th>
                        <th class="text-center" style="width: 100px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $incident)
                        <tr>
                            <td class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $incident->code }}</td>
                            <td>
                                <div class="fw-bold text-primary mb-1" style="font-size: 0.85rem;">
                                    {{ $incident->elevator->code ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $incident->elevator->building->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem; line-height: 1.4;">
                                    {{ $incident->description }}</div>
                            </td>
                            <td>
                                <div class="mb-1" style="font-size: 0.8rem;"><i
                                        class="far fa-user fa-fw me-1 text-muted"></i>
                                    <strong>{{ $incident->reporter_name }}</strong>
                                </div>
                                @if ($incident->reporter_phone)
                                    <div style="font-size: 0.8rem;"><i class="fas fa-phone-alt fa-fw me-1 text-muted"></i>
                                        <span class="text-primary">{{ $incident->reporter_phone }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($incident->staff_names)
                                    <div class="fw-semibold text-primary" style="font-size: 0.8rem;"><i
                                            class="fas fa-tools fa-fw me-1"></i>{{ $incident->staff_names }}</div>
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem;">Chưa phân công</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $pLabel =
                                        [
                                            'emergency' => 'Khẩn cấp',
                                            'high' => 'Cao',
                                            'medium' => 'Trung bình',
                                            'low' => 'Thấp',
                                        ][$incident->priority] ?? $incident->priority;
                                    $pClass = 'priority-' . $incident->priority;
                                @endphp
                                <span class="badge-priority {{ $pClass }}">{{ $pLabel }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $sLabel =
                                        [
                                            'new' => 'MỚI BÁO',
                                            'processing' => 'ĐANG XỬ LÝ',
                                            'resolved' => 'HOÀN THÀNH',
                                            'canceled' => 'ĐÃ HỦY',
                                        ][$incident->status] ?? $incident->status;
                                    $sClass = 'status-' . $incident->status;
                                @endphp
                                <span class="badge-status-dot {{ $sClass }}">{{ $sLabel }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $incident->reported_at ? $incident->reported_at->format('d/m/Y') : '' }}</div>
                                <div class="small text-muted">
                                    {{ $incident->reported_at ? $incident->reported_at->format('H:i') : '' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @can('view_incident')
                                        <a href="{{ route('admin.incidents.show', $incident->id) }}"
                                            class="btn-action btn-action-view" data-bs-toggle="tooltip" title="Xem chi tiết">
                                            <i class="far fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update_incident')
                                        <a href="{{ route('admin.incidents.edit', $incident->id) }}"
                                            class="btn-action btn-action-edit" data-bs-toggle="tooltip" title="Sửa">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete_incident')
                                        <form action="{{ route('admin.incidents.destroy', $incident->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Xác nhận xóa sự cố này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete"
                                                data-bs-toggle="tooltip" title="Xóa">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                                <p>Không tìm thấy sự cố nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="p-3 border-top">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips Initialization
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Incident Trend Chart
            const ctxTrend = document.getElementById('incidentTrendChart').getContext('2d');
            const trendLabels = {!! json_encode($chartLabels) !!};
            const trendData = {!! json_encode($chartData) !!};

            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        data: trendData,
                        borderColor: '#ef4444',
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#fff',
                            titleColor: '#111827',
                            bodyColor: '#ef4444',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    return 'count : ' + context.raw;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false,
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endsection
