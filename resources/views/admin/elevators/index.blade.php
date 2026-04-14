@extends('layouts.admin')

@section('title', 'Quản lý Thang máy')

@section('content')
    {{-- Breadcrumb Header --}}
    <div class="d-flex justify-content-between align-items-start align-items-sm-center mb-4 flex-column flex-sm-row gap-3">
        <div class="text-truncate me-2">
            <h1 class="h3 mb-1 text-gray-800 fw-bold text-truncate">Quản lý Thang máy</h1>
            <p class="mb-0 text-muted small d-none d-sm-block">Danh sách thang máy được phân loại theo địa giới hành chính
            </p>
        </div>
        @can('create_elevator')
            <a href="{{ route('admin.elevators.create') }}" class="btn-add flex-shrink-0">
                <i class="fas fa-plus me-sm-1"></i> <span class="d-none d-sm-inline">Thêm thang máy</span>
            </a>
        @endcan
    </div>

    {{-- Statistics Dashboard --}}
    <div class="row g-3 mb-4">
        {{-- Tỷ lệ trạng thái --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium" style="animation-delay: 0.1s;">
                <h6 class="text-muted small fw-bold mb-4 text-uppercase">
                    <i class="fas fa-chart-pie me-1"></i> Tỷ lệ trạng thái
                </h6>
                <div class="d-flex align-items-center justify-content-center chart-glass-wrapper" style="height: 180px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Phân bổ hãng --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium" style="animation-delay: 0.2s;">
                <h6 class="text-muted small fw-bold mb-4 text-uppercase">
                    <i class="fas fa-chart-bar me-1"></i> Phân bổ hãng
                </h6>
                <div class="chart-glass-wrapper" style="height: 180px;">
                    <canvas id="manufacturerChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Tổng thang máy --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 text-white position-relative overflow-hidden stats-card-premium"
                style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); min-height: 240px; animation-delay: 0.3s;">
                <div class="h-100 d-flex flex-column justify-content-center text-center">
                    <h6 class="small fw-bold text-uppercase opacity-75 mb-3">Tổng thang máy</h6>
                    <div>
                        <h2 class="display-2 fw-bold mb-0" id="totalElevatorsCount">0</h2>
                        <div class="badge bg-white bg-opacity-25 rounded-pill px-4 py-2 mt-3">
                            <i class="fas fa-chart-line me-2"></i>
                            @if ($growth >= 0)
                                +{{ number_format($growth, 1) }}% so với tháng trước
                            @else
                                {{ number_format($growth, 1) }}% so với tháng trước
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tech-card h-100">
        <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <div class="d-flex justify-content-between align-items-center flex-nowrap gap-2">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center text-nowrap me-2">
                    <i class="fas fa-elevator me-2 bg-white bg-opacity-25 rounded-circle p-2 d-none d-md-flex"
                        style="width: 36px; height: 36px; align-items: center; justify-content: center;"></i>
                    <span class="d-none d-sm-inline">Danh sách thiết bị</span>
                </h6>

                <div class="d-flex align-items-center flex-nowrap gap-2">
                    {{-- Quick Search --}}
                    <form method="GET" action="{{ route('admin.elevators.index') }}"
                        class="d-flex align-items-center flex-nowrap gap-1">
                        <div class="bg-white rounded-pill shadow-sm"
                            style="min-width: 200px; width: 100%; max-width: 350px;">
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3"
                                    style="z-index: 5; font-size: 0.8rem;"></i>
                                <input type="text" name="search"
                                    class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-2 py-2"
                                    style="font-size: 0.85rem;" placeholder="Tìm nhanh..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-light rounded-pill px-3 shadow-none fw-bold flex-shrink-0"
                            style="font-size: 0.8rem;">Tìm</button>
                    </form>

                    {{-- Advanced Filter Toggle --}}
                    <button class="btn btn-outline-light rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm" type="button"
                        data-bs-toggle="collapse" data-bs-target="#advancedFilter" aria-expanded="false"
                        style="font-size: 0.8rem; border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.1);">
                        <i class="fas fa-filter me-sm-1"></i> <span class="d-none d-sm-inline">Bộ lọc nâng cao</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Advanced Filter Section (Moved below header) --}}
        <div class="collapse {{ request()->except(['sort', 'direction', 'search']) ? 'show' : '' }}" id="advancedFilter">
            <div class="card-body bg-light border-bottom p-4">
                <h6 class="fw-bold text-primary mb-4 d-flex align-items-center">
                    <i class="fas fa-filter me-2"></i> BỘ LỌC NÂNG CAO
                </h6>
                <form action="{{ route('admin.elevators.index') }}" method="GET">
                    <div class="row g-3">
                        {{-- Mã Thang máy --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-hashtag me-1"></i> MÃ THANG
                                MÁY</label>
                            <input type="text" name="code" class="form-control modern-form-control"
                                placeholder="VD: HA-90001-EM" value="{{ request('code') }}">
                        </div>

                        {{-- Tòa nhà / Khách hàng --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-user me-1"></i> TÒA NHÀ /
                                KHÁCH HÀNG</label>
                            <input type="text" name="customer" class="form-control modern-form-control"
                                placeholder="Tên, SĐT khách" value="{{ request('customer') }}">
                        </div>

                        {{-- Trạng thái --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-bolt me-1"></i> TRẠNG
                                THÁI</label>
                            <select name="status" class="form-select modern-form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động
                                </option>
                                <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Lỗi</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Bảo
                                    trì</option>
                            </select>
                        </div>

                        {{-- Hạn bảo trì --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-calendar me-1"></i> HẠN
                                BẢO
                                TRÌ</label>
                            <select name="deadline_status" class="form-select modern-form-control">
                                <option value="">-- Tất cả --</option>
                                <option value="upcoming" {{ request('deadline_status') == 'upcoming' ? 'selected' : '' }}>
                                    Sắp đến hạn (7 ngày)</option>
                                <option value="overdue" {{ request('deadline_status') == 'overdue' ? 'selected' : '' }}>
                                    Quá hạn</option>
                            </select>
                        </div>

                        {{-- Tỉnh --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-map-marker-alt me-1"></i>
                                TỈNH/THÀNH PHỐ</label>
                            <select name="province" class="form-select modern-form-control">
                                <option value="">-- Tất cả --</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province }}"
                                        {{ request('province') == $province ? 'selected' : '' }}>{{ $province }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Hãng SX --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-tools me-1"></i> HÃNG SẢN
                                XUẤT</label>
                            <select name="manufacturer" class="form-select modern-form-control">
                                <option value="">-- Hãng sản xuất --</option>
                                @foreach ($manufacturers as $m)
                                    <option value="{{ $m }}"
                                        {{ request('manufacturer') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Model --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-barcode me-1"></i>
                                MODEL</label>
                            <select name="model" class="form-select modern-form-control">
                                <option value="">-- Model --</option>
                                @foreach ($models as $mod)
                                    <option value="{{ $mod }}" {{ request('model') == $mod ? 'selected' : '' }}>
                                        {{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Loại thang --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-list me-1"></i> LOẠI
                                THANG</label>
                            <select name="type" class="form-select modern-form-control">
                                <option value="">-- Loại thang --</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>
                                        {{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tải trọng --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-weight-hanging me-1"></i>
                                TẢI TRỌNG (KG)</label>
                            <div class="input-group">
                                <input type="number" name="capacity_from" class="form-control modern-form-control"
                                    placeholder="Từ" value="{{ request('capacity_from') }}">
                                <input type="number" name="capacity_to" class="form-control modern-form-control"
                                    placeholder="Đến" value="{{ request('capacity_to') }}">
                            </div>
                        </div>

                        {{-- Ngày tạo --}}
                        <div class="col-md-3 col-12">
                            <label class="form-label small fw-bold text-muted"><i class="fas fa-calendar-alt me-1"></i>
                                NGÀY TẠO</label>
                            <div class="input-group">
                                <input type="date" name="created_from" class="form-control modern-form-control"
                                    value="{{ request('created_from') }}">
                                <input type="date" name="created_to" class="form-control modern-form-control"
                                    value="{{ request('created_to') }}">
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-none rounded-pill">
                                <i class="fas fa-filter me-1"></i> Lọc dữ liệu
                            </button>
                            <a href="{{ route('admin.elevators.index') }}"
                                class="btn btn-outline-secondary px-4 fw-bold shadow-none rounded-pill">
                                Làm mới
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern table-horizontal-mobile text-nowrap mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th style="width: 150px;">Mã thang máy</th>
                        <th style="width: 200px;">Tòa nhà / Khách hàng</th>
                        <th style="width: 150px;">Tỉnh/Huyện</th>
                        <th class="text-center">Chu kỳ</th>
                        <th class="text-center">Chi nhánh</th>
                        <th class="text-center">Trạng thái</th>
                        @php
                            $currentSort = request('sort');
                            $currentDir = request('direction', 'desc');
                            $nextDir =
                                $currentSort === 'maintenance_deadline' && $currentDir === 'asc' ? 'desc' : 'asc';
                            $sortIcon =
                                $currentSort === 'maintenance_deadline'
                                    ? ($currentDir === 'asc'
                                        ? 'fa-sort-up'
                                        : 'fa-sort-down')
                                    : 'fa-sort';
                        @endphp
                        <th class="text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'maintenance_deadline', 'direction' => $nextDir]) }}"
                                class="text-secondary text-decoration-none">
                                Hạn bảo trì <i class="fas {{ $sortIcon }} ms-1"></i>
                            </a>
                        </th>
                        <th class="text-center">Ngày tạo</th>
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedElevators as $province => $districts)
                        {{-- Province Header --}}
                        <tr>
                            <td colspan="10" class="group-header-province">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i> TỈNH:
                                {{ mb_strtoupper($province) }}
                            </td>
                        </tr>

                        @foreach ($districts as $district => $elevators)
                            {{-- District Header --}}
                            <tr>
                                <td colspan="10" class="group-header-district">
                                    <i class="fas fa-street-view me-2 text-muted"></i> HUYỆN:
                                    {{ mb_strtoupper($district) }}
                                </td>
                            </tr>

                            @foreach ($elevators as $elevator)
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $elevator->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.elevators.edit', $elevator) }}"
                                            class="fw-bold text-primary text-decoration-none">
                                            {{ $elevator->code }}
                                        </a>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            {{ $elevator->manufacturer }} {{ $elevator->model }}
                                            @if ($elevator->type)
                                                · {{ $elevator->type }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $elevator->customer_name ?? ($elevator->building->name ?? 'N/A') }}</div>
                                        <div class="text-muted small">
                                            {{ $elevator->customer_phone ?? ($elevator->building->contact_phone ?? '') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $elevator->province }}</div>
                                        <div class="text-muted small">{{ $elevator->district }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center text-muted small">
                                            <i class="far fa-calendar-alt me-1"></i> {{ $elevator->cycle_days }} ngày
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($elevator->branch)
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold"
                                                style="font-size: 0.7rem;">
                                                <i class="fas fa-code-branch me-1"></i> {{ $elevator->branch->name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($elevator->status === 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold"
                                                style="font-size: 0.7rem;">Hoạt động</span>
                                        @elseif($elevator->status === 'error')
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold"
                                                style="font-size: 0.7rem;">Lỗi</span>
                                        @elseif($elevator->status === 'maintenance')
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold"
                                                style="font-size: 0.7rem;">Bảo trì</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($elevator->maintenance_deadline)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold"
                                                style="font-size: 0.72rem;">
                                                {{ $elevator->maintenance_deadline->format('d/m/Y') }}
                                                <i class="fas fa-pencil-alt ms-1" style="font-size: 0.6rem;"></i>
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ $elevator->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.elevators.show', $elevator) }}"
                                                class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;" title="Xem">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update_elevator')
                                                <a href="{{ route('admin.elevators.edit', $elevator) }}"
                                                    class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete_elevator')
                                                <form action="{{ route('admin.elevators.destroy', $elevator) }}"
                                                    method="POST" class="d-inline-block"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa thang máy này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center opacity-50">
                                    <i class="fas fa-elevator fa-3x mb-3 text-muted"></i>
                                    <h6 class="fw-bold">Chưa có dữ liệu thang máy</h6>
                                    <p class="small text-muted">Hãy thêm thiết bị mới vào hệ thống</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 0. Number Counter Animation for Total Elevators
            function animateNumber(id, finalValue, duration = 2000) {
                const obj = document.getElementById(id);
                if (!obj) return;

                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const current = Math.floor(progress * finalValue);
                    obj.innerHTML = current.toLocaleString();
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }

            // Start number animation
            setTimeout(() => {
                animateNumber('totalElevatorsCount', {{ $totalElevators }});
            }, 300);

            // 1. Status Donut Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = {
                labels: ['Hoạt động', 'Lỗi', 'Bảo trì'],
                datasets: [{
                    data: [
                        {{ $statusStats['active'] ?? 0 }},
                        {{ $statusStats['error'] ?? 0 }},
                        {{ $statusStats['maintenance'] ?? 0 }}
                    ],
                    backgroundColor: ['#41c77f', '#ec5e51', '#5479e5'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            };
            new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    cutout: '70%',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2500,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });

            // 2. Manufacturer Bar Chart
            const mCtx = document.getElementById('manufacturerChart').getContext('2d');
            const mData = {
                labels: {!! json_encode($manufacturerStats->pluck('manufacturer')) !!},
                datasets: [{
                    label: 'Số lượng',
                    data: {!! json_encode($manufacturerStats->pluck('count')) !!},
                    backgroundColor: '#5479e5',
                    hoverBackgroundColor: '#4162c7',
                    borderRadius: 50,
                    borderSkipped: false,
                    barThickness: 12
                }]
            };
            new Chart(mCtx, {
                type: 'bar',
                data: mData,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        delay: (context) => context.dataIndex * 300,
                        easing: 'easeOutElastic'
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
