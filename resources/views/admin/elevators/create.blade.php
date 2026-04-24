@extends('layouts.admin')

@section('title', 'Thêm thang máy mới')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 350px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .map-search-wrapper {
            position: relative;
            z-index: 10;
            margin-bottom: 10px;
        }

        .map-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            border: 1px solid #e0e0e0;
        }

        .map-search-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.88rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .map-search-item:last-child {
            border-bottom: none;
        }

        .map-search-item i {
            margin-right: 10px;
            color: #4e73df;
            font-size: 0.9rem;
        }

        .map-search-item:hover {
            background-color: #f8f9fc;
            color: #4e73df;
        }
    </style>
@endsection

@section('content')
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Thêm thang máy mới</h1>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.elevators.index') }}" class="btn-add" style="background: #6c757d;">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <form action="{{ route('admin.elevators.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="tech-card mb-4">
                    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i> Thông tin thiết bị
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Mã thang máy <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="code"
                                    class="form-control modern-form-control @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}" required placeholder="Ví dụ: HA-91226-EM">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tòa nhà</label>
                                <select name="building_id" class="form-select modern-form-control">
                                    <option value="">-- Chọn tòa nhà (Không bắt buộc) --</option>
                                    @foreach ($buildings as $building)
                                        <option value="{{ $building->id }}"
                                            {{ old('building_id') == $building->id ? 'selected' : '' }}>
                                            {{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Chi nhánh</label>
                                <select name="branch_id" class="form-select modern-form-control">
                                    <option value="">-- Chọn chi nhánh (Không bắt buộc) --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tên khách hàng</label>
                                <input type="text" name="customer_name" class="form-control modern-form-control"
                                    value="{{ old('customer_name') }}"
                                    placeholder="Nhập tên khách hàng nếu không có tòa nhà">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Số điện thoại</label>
                                <input type="text" name="customer_phone" class="form-control modern-form-control"
                                    value="{{ old('customer_phone') }}" placeholder="Số điện thoại khách hàng">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tỉnh / Thành phố <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="province"
                                    class="form-control modern-form-control @error('province') is-invalid @enderror"
                                    value="{{ old('province') }}" required placeholder="Ví dụ: Ninh Bình">
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Quận / Huyện <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="district"
                                    class="form-control modern-form-control @error('district') is-invalid @enderror"
                                    value="{{ old('district') }}" required placeholder="Ví dụ: Giao Thủy">
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Địa chỉ chi tiết</label>
                                <input type="text" name="address" class="form-control modern-form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address') }}" placeholder="Số nhà, tên đường, thôn/xóm...">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Vị trí trên bản đồ</label>
                                <div class="map-search-wrapper">
                                    <div class="input-group mb-2 shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-search text-muted" id="search-icon"></i>
                                            <div class="spinner-border spinner-border-sm text-primary d-none" id="search-spinner" role="status"></div>
                                        </span>
                                        <input type="text" id="map-search-input" class="form-control modern-form-control border-start-0 ps-0" placeholder="Gõ địa chỉ để tìm kiếm vị trí...">
                                        <button class="btn btn-success px-3 fw-bold" type="button" id="btn-current-location">
                                            <i class="fas fa-location-arrow me-1"></i> Hiện tại
                                        </button>
                                    </div>
                                    <div id="search-results" class="map-search-results"></div>
                                </div>
                                <div id="map"></div>
                                <input type="hidden" name="map" id="map-coords" value="{{ old('map') }}">
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i> Bản đồ tự động hiển thị gợi ý khi bạn nhập địa chỉ. Bạn cũng có thể click trực tiếp để chọn.
                                </small>
                            </div>
                        </div>

                        <hr class="my-4 opacity-50">
                        <h6 class="fw-bold fs-6 mb-3 small text-uppercase text-primary"><i class="fas fa-microchip me-2"></i> Thông số kỹ thuật</h6>
                        <div class="row">
                            <div class="col-4 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Hãng sản xuất</label>
                                <input type="text" name="manufacturer" class="form-control modern-form-control"
                                    value="{{ old('manufacturer') }}" placeholder="Mitsubishi, Otis...">
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">MODEL</label>
                                <input type="text" name="model" class="form-control modern-form-control"
                                    value="{{ old('model') }}" placeholder="Nhập model...">
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Số tầng</label>
                                <input type="number" name="floors" class="form-control modern-form-control"
                                    value="{{ old('floors') }}" placeholder="Số tầng...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Loại thang máy</label>
                                <input type="text" name="type" class="form-control modern-form-control"
                                    value="{{ old('type') }}" placeholder="Ví dụ: Thang khách, Thang tải hàng...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tải trọng (kg)</label>
                                <input type="text" name="capacity" class="form-control modern-form-control"
                                    value="{{ old('capacity') }}" placeholder="Ví dụ: 630kg, 1000kg...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú</label>
                            <textarea name="note" class="form-control modern-form-control" rows="3" placeholder="Nhập ghi chú...">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tech-card mb-4">
                    <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-tools me-2"></i> Bảo trì & Trạng thái
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Chu kỳ bảo trì (ngày)</label>
                            <input type="number" name="cycle_days" class="form-control modern-form-control"
                                value="{{ old('cycle_days', 30) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Hạn bảo trì tiếp theo</label>
                            <input type="date" name="maintenance_deadline" class="form-control modern-form-control"
                                value="{{ old('maintenance_deadline') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ngày kết thúc thời hạn bảo trì</label>
                            <input type="date" name="maintenance_end_date" class="form-control modern-form-control"
                                value="{{ old('maintenance_end_date') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Trạng thái</label>
                            <select name="status" class="form-select modern-form-control">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="error" {{ old('status') == 'error' ? 'selected' : '' }}>Lỗi</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-tech-primary btn-lg fw-bold">
                        <i class="fas fa-save me-2"></i> Lưu thang máy
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Default location: Hanoi, Vietnam
            const defaultLat = 21.0285;
            const defaultLng = 105.8542;
            
            // Initialize map
            const map = L.map('map').setView([defaultLat, defaultLng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Marker
            let marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            function updateCoords(lat, lng) {
                document.getElementById('map-coords').value = lat.toFixed(6) + ',' + lng.toFixed(6);
            }

            // Initial coords
            const oldCoords = document.getElementById('map-coords').value;
            if (oldCoords && oldCoords.includes(',')) {
                const parts = oldCoords.split(',');
                const lat = parseFloat(parts[0]);
                const lng = parseFloat(parts[1]);
                if (!isNaN(lat) && !isNaN(lng)) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 16);
                }
            } else {
                updateCoords(defaultLat, defaultLng);
            }

            // Events
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateCoords(position.lat, position.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoords(e.latlng.lat, e.latlng.lng);
            });

            // Current Location
            document.getElementById('btn-current-location').addEventListener('click', function() {
                const btn = this;
                const originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], 16);
                        updateCoords(lat, lng);
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }, function() {
                        alert('Không thể lấy vị trí hiện tại của bạn.');
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    });
                } else {
                    alert('Trình duyệt của bạn không hỗ trợ định vị.');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            });

            // Search Address with Suggestions (Debounced)
            const searchInput = document.getElementById('map-search-input');
            const searchResults = document.getElementById('search-results');
            const searchIcon = document.getElementById('search-icon');
            const searchSpinner = document.getElementById('search-spinner');
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
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`);
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
                                marker.setLatLng([lat, lon]);
                                map.setView([lat, lon], 16);
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

            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });

            // Prevent form submission on Enter in search input
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
