@extends('layouts.admin')

@section('title', 'Lắp đặt thang máy')

@section('styles')
    <style>
        .table-responsive {
            min-height: 350px;
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

        .staff-avatar-circle {
            width: 32px;
            height: 32px;
            background: #e3f2fd;
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            margin-right: 10px;
        }

        .clickable-row:hover {
            background-color: rgba(78, 115, 223, 0.05) !important;
        }

        /* Map Styles */
        #map-complete {
            height: 300px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1;
            margin-top: 10px;
        }

        .map-search-wrapper {
            position: relative;
            z-index: 1001;
        }

        .map-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 2000;
            display: none;
            border: 1px solid #e0e0e0;
        }

        .map-search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .map-search-item:hover {
            background-color: #f8f9fc;
            color: #4e73df;
        }

        .map-search-item i {
            margin-right: 10px;
            color: #4e73df;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Lắp đặt thang máy</h1>
                <p class="mb-0 text-muted small">Quản lý tiến độ lắp đặt và giao việc cho nhân viên</p>
            </div>
            @can('create_installation')
                <a href="{{ route('admin.installations.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-plus me-md-2"></i><span class="d-none d-md-inline"> Tạo đơn lắp đặt</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Đang thực hiện</p>
                        <h2 class="fw-bold mb-0 text-primary">{{ $stats['in_progress'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-blue">
                        <i class="far fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Chờ xử lý</p>
                        <h2 class="fw-bold mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-orange">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Đã hoàn thành</p>
                        <h2 class="fw-bold mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-green">
                        <i class="far fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders List Section --}}
    <div class="tech-card">
        <div class="tech-header" style="background: white; border-bottom: 1px solid #f1f3f9;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center text-nowrap">
                    <i class="fas fa-wrench me-2 bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-none d-md-flex"
                        style="width: 36px; height: 36px; align-items: center; justify-content: center;"></i>
                    <span>Danh sách đơn lắp đặt</span>
                </h6>

                <form action="{{ route('admin.installations.index') }}" method="GET"
                    class="d-flex align-items-center flex-wrap gap-2 w-100 w-sm-auto justify-content-md-end">
                    {{-- Quick Search --}}
                    <div class="bg-light rounded-pill px-3 py-1 d-flex align-items-center flex-grow-1"
                        style="min-width: 250px;">
                        <i class="fas fa-search text-muted me-2"></i>
                        <input type="text" name="search" class="form-control border-0 bg-transparent shadow-none small"
                            placeholder="Tìm kiếm khách hàng, mã..." style="font-size: 0.85rem;"
                            value="{{ request('search') }}">
                    </div>
                    <select name="status" class="form-select border-0 bg-light rounded-pill px-3 py-1 small shadow-none"
                        style="width: 140px; font-size: 0.85rem;">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ giao</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang lắp
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã xong</option>
                    </select>
                    <button type="submit"
                        class="btn btn-primary rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm d-flex align-items-center"
                        style="font-size: 0.8rem;">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    @if (request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.installations.index') }}"
                            class="btn btn-light rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm text-muted"
                            style="font-size: 0.8rem;">
                            Xóa lọc
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0 small fw-bold text-muted text-nowrap" style="width: 120px;">MÃ ĐƠN</th>
                        <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 250px;">KHÁCH HÀNG / ĐỊA
                            CHỈ</th>
                        <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 180px;">NHÂN VIÊN PHỤ
                            TRÁCH</th>
                        <th class="border-0 small fw-bold text-muted text-nowrap" style="min-width: 160px;">THỜI GIAN DỰ
                            KIẾN</th>
                        <th class="border-0 small fw-bold text-muted text-nowrap">TRẠNG THÁI</th>
                        <th class="pe-4 border-0 text-end small fw-bold text-muted text-nowrap" style="width: 150px;">THAO
                            TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installations as $inst)
                        <tr class="clickable-row" data-href="{{ route('admin.installations.edit', $inst->id) }}" style="cursor: pointer;">
                            <td class="ps-4 fw-bold text-nowrap">
                                <a href="{{ route('admin.installations.edit', $inst->id) }}" class="text-primary text-decoration-none">
                                    {{ $inst->code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $inst->building->name ?? 'N/A' }}</div>
                                @if($inst->phone)
                                    <div class="small text-primary fw-bold"><i class="fas fa-phone-alt me-1"></i> {{ $inst->phone }}</div>
                                @endif
                                <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $inst->building->address ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="staff-avatar-circle">
                                        {{ strtoupper(substr($inst->staff->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="small fw-bold text-nowrap">{{ $inst->staff->name ?? 'Chưa giao' }}</span>
                                </div>
                            </td>
                            <td class="small">
                                <div class="text-nowrap">Bắt đầu: <span
                                        class="text-dark fw-bold">{{ $inst->start_date ? $inst->start_date->format('Y-m-d') : '---' }}</span>
                                </div>
                                <div class="text-muted text-nowrap">Dự kiến:
                                    {{ $inst->due_date ? $inst->due_date->format('Y-m-d') : '---' }}</div>
                            </td>
                            <td>
                                @if ($inst->status == 'in_progress')
                                    <span class="badge-pill-modern badge-status-in-progress">Đang lắp</span>
                                @elseif($inst->status == 'pending')
                                    <span class="badge-pill-modern badge-status-pending">Chờ giao</span>
                                @else
                                    <span class="badge-pill-modern badge-status-completed">Đã xong</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    @if ($inst->status == 'pending')
                                        @can('update_installation')
                                            <form action="{{ route('admin.installations.start', $inst->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"
                                                    style="font-size: 0.75rem;">
                                                    <i class="fas fa-play me-1"></i> Bắt đầu lắp đặt
                                                </button>
                                            </form>
                                        @endcan
                                    @elseif($inst->status == 'in_progress')
                                        @can('update_installation')
                                            <button type="button" class="btn-ghost-complete open-complete-modal"
                                                data-id="{{ $inst->id }}" data-code="{{ $inst->code }}"
                                                data-building="{{ $inst->building->name ?? '' }}"
                                                data-branch="{{ $inst->branch->name ?? 'N/A' }}">
                                                <i class="far fa-check-circle me-1"></i> Hoàn thành
                                            </button>
                                        @endcan
                                    @endif
                                    <div class="dropdown {{ $loop->last ? 'dropup' : '' }}">
                                        <button class="btn btn-link text-muted p-0 shadow-none" data-bs-toggle="dropdown"
                                            data-bs-boundary="viewport" data-bs-strategy="fixed">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @can('update_installation')
                                                <li><a class="dropdown-item small"
                                                        href="{{ route('admin.installations.edit', $inst->id) }}"><i
                                                            class="fas fa-edit me-2"></i> Chỉnh sửa</a></li>
                                            @endcan
                                            @can('delete_installation')
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.installations.destroy', $inst->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item small text-danger"><i
                                                                class="fas fa-trash-alt me-2"></i> Xóa</button>
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Chưa có đơn lắp đặt nào.</p>
                                <a href="{{ route('admin.installations.create') }}"
                                    class="btn btn-primary btn-sm mt-3 rounded-pill px-4">Tạo đơn ngay</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($installations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $installations->links() }}
            </div>
        @endif
    </div>

    {{-- Elevator Registration Modal --}}
    <div class="modal fade" id="completeInstallationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white p-4 border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-elevator me-2"></i> Đăng ký thang máy & Hoàn thành lắp đặt
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="completeForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 mb-4 small">
                            <i class="fas fa-info-circle me-2"></i>
                            Đơn lắp đặt <strong id="modal_inst_code"></strong> cho <strong id="modal_building"></strong>
                            sẽ được đánh dấu là
                            <strong>Đã xong</strong> sau khi bạn đăng ký thông tin thang máy dưới đây.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mã thang máy</label>
                                <input type="text" name="elevator_code"
                                    class="form-control bg-light border-0 p-3 rounded-4 fw-bold"
                                    placeholder="Ví dụ: TH-001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Hãng sản xuất</label>
                                <input type="text" name="manufacturer"
                                    class="form-control bg-light border-0 p-3 rounded-4"
                                    placeholder="Ví dụ: Mitsubishi, Fuji...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Model</label>
                                <input type="text" name="model"
                                    class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Loại thang</label>
                                <input type="text" name="type"
                                    class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tải trọng (kg)</label>
                                <input type="text" name="capacity"
                                    class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Số tầng</label>
                                <input type="number" name="floors"
                                    class="form-control bg-light border-0 p-3 rounded-4" placeholder="Ví dụ: 5">
                            </div>

                            <hr class="my-4 opacity-5">

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tỉnh / Thành phố</label>
                                <input type="text" name="province"
                                    class="form-control bg-light border-0 p-3 rounded-4" placeholder="VD: Hà Nội">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Quận / Huyện</label>
                                <input type="text" name="district"
                                    class="form-control bg-light border-0 p-3 rounded-4" placeholder="VD: Cầu Giấy">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Địa chỉ chi tiết</label>
                                <input type="text" name="address"
                                    class="form-control bg-light border-0 p-3 rounded-4" placeholder="Số nhà, tên đường...">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Vị trí trên bản đồ</label>
                                <div class="map-search-wrapper mb-2">
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-search text-muted" id="search-icon-complete"></i>
                                            <div class="spinner-border spinner-border-sm text-primary d-none" id="search-spinner-complete" role="status"></div>
                                        </span>
                                        <input type="text" id="map-search-input-complete" class="form-control bg-white border-start-0 ps-0" placeholder="Tìm địa chỉ để ghim vị trí...">
                                        <button class="btn btn-outline-primary px-3" type="button" id="btn-current-location-complete">
                                            <i class="fas fa-location-arrow"></i>
                                        </button>
                                    </div>
                                    <div id="search-results-complete" class="map-search-results"></div>
                                </div>
                                <div id="map-complete"></div>
                                <input type="hidden" name="map" id="map-coords-complete">
                                <small class="text-muted mt-2 d-block small">
                                    <i class="fas fa-info-circle me-1"></i> Bạn có thể click trực tiếp hoặc kéo marker để chọn vị trí chính xác.
                                </small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Chu kỳ bảo trì
                                    (Ngày)</label>
                                <input type="number" name="cycle_days"
                                    class="form-control bg-light border-0 p-3 rounded-4" value="30" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú</label>
                                <textarea name="note" class="form-control bg-light border-0 p-3 rounded-4" rows="2" placeholder="Thông tin bổ sung khác..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4 bg-light border-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Lưu & Hoàn thành
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('completeInstallationModal');
            const modal = new bootstrap.Modal(modalEl);
            const completeButtons = document.querySelectorAll('.open-complete-modal');
            const completeForm = document.getElementById('completeForm');
            const instCodeSpan = document.getElementById('modal_inst_code');
            const buildingSpan = document.getElementById('modal_building');

            // Map Variables
            let mapComplete;
            let markerComplete;
            const defaultLat = 21.0285;
            const defaultLng = 105.8542;

            function initMap() {
                if (mapComplete) return;

                mapComplete = L.map('map-complete').setView([defaultLat, defaultLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(mapComplete);

                markerComplete = L.marker([defaultLat, defaultLng], {
                    draggable: true
                }).addTo(mapComplete);

                markerComplete.on('dragend', function() {
                    const pos = markerComplete.getLatLng();
                    updateCoords(pos.lat, pos.lng);
                });

                mapComplete.on('click', function(e) {
                    markerComplete.setLatLng(e.latlng);
                    updateCoords(e.latlng.lat, e.latlng.lng);
                });
            }

            function updateCoords(lat, lng) {
                document.getElementById('map-coords-complete').value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            modalEl.addEventListener('shown.bs.modal', function() {
                initMap();
                mapComplete.invalidateSize();
            });

            // Current Location
            document.getElementById('btn-current-location-complete').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        markerComplete.setLatLng([lat, lng]);
                        mapComplete.setView([lat, lng], 16);
                        updateCoords(lat, lng);
                    });
                }
            });

            // Search Address
            const searchInput = document.getElementById('map-search-input-complete');
            const searchResults = document.getElementById('search-results-complete');
            const searchIcon = document.getElementById('search-icon-complete');
            const searchSpinner = document.getElementById('search-spinner-complete');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();
                if (query.length < 3) {
                    searchResults.style.display = 'none';
                    return;
                }
                debounceTimer = setTimeout(() => {
                    performSearch(query);
                }, 600);
            });

            async function performSearch(query) {
                searchIcon.classList.add('d-none');
                searchSpinner.classList.remove('d-none');
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`);
                    const data = await response.json();
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'map-search-item';
                            div.innerHTML = `<i class="fas fa-map-marker-alt"></i> <span>${item.display_name}</span>`;
                            div.addEventListener('click', function() {
                                const lat = parseFloat(item.lat);
                                const lon = parseFloat(item.lon);
                                markerComplete.setLatLng([lat, lon]);
                                mapComplete.setView([lat, lon], 16);
                                updateCoords(lat, lon);
                                searchResults.style.display = 'none';
                                searchInput.value = item.display_name;
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Search error:', error);
                } finally {
                    searchIcon.classList.remove('d-none');
                    searchSpinner.classList.add('d-none');
                }
            }

            completeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const code = this.getAttribute('data-code');
                    const building = this.getAttribute('data-building');

                    completeForm.action = `/admin/installations/${id}/complete`;
                    instCodeSpan.textContent = code;
                    buildingSpan.textContent = building;

                    completeForm.querySelectorAll('input:not([name="_token"]):not([name="cycle_days"])').forEach(input => {
                        input.value = '';
                    });
                    
                    updateCoords(defaultLat, defaultLng);
                    modal.show();
                });
            });
        });
    </script>
@endsection
