@extends('layouts.admin')

@section('title', 'Chi tiết thang máy')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-uppercase;
            color: #6c757d;
            margin-bottom: 4px;
            display: block;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
        }

        .spec-item {
            padding: 15px;
            border-radius: 12px;
            background: #f8f9fc;
            border: 1px solid #edf2f7;
            height: 100%;
            transition: all 0.2s;
        }

        .spec-item:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .spec-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                    <i class="fas fa-elevator fa-2x"></i>
                </div>
                <div>
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">{{ $elevator->code }}</h1>
                    <p class="mb-0 text-muted small">Chi tiết thiết bị & Vị trí lắp đặt</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.elevators.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            @can('update_elevator')
                <a href="{{ route('admin.elevators.edit', $elevator) }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        {{-- Main Info Column --}}
        <div class="col-lg-8">
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-info-circle me-2"></i> THÔNG TIN CHUNG</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="info-label">Khách hàng / Tòa nhà</span>
                            <div class="info-value d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-building text-primary" style="font-size: 0.8rem;"></i>
                                </div>
                                {{ $elevator->customer_name ?? ($elevator->building->name ?? 'N/A') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Số điện thoại</span>
                            <div class="info-value d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-phone text-success" style="font-size: 0.8rem;"></i>
                                </div>
                                {{ $elevator->customer_phone ?? ($elevator->building->contact_phone ?? 'N/A') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Địa chỉ lắp đặt</span>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                {{ $elevator->district }}, {{ $elevator->province }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Chi nhánh quản lý</span>
                            <div class="info-value">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                    <i class="fas fa-code-branch me-1"></i> {{ $elevator->branch->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <h6 class="fw-bold fs-6 mb-4 text-primary text-uppercase small letter-spacing-1">
                        <i class="fas fa-microchip me-2"></i> Thông số kỹ thuật
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="spec-item">
                                <div class="spec-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <span class="info-label">Hãng sản xuất</span>
                                <div class="info-value">{{ $elevator->manufacturer ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="spec-item">
                                <div class="spec-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <span class="info-label">Model</span>
                                <div class="info-value">{{ $elevator->model ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="spec-item">
                                <div class="spec-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <span class="info-label">Số tầng</span>
                                <div class="info-value">{{ $elevator->floors ?? '-' }} tầng</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="spec-item">
                                <div class="spec-icon bg-purple bg-opacity-10 text-purple" style="--bs-bg-opacity: .1; color: #6f42c1;">
                                    <i class="fas fa-list-ul"></i>
                                </div>
                                <span class="info-label">Loại thang máy</span>
                                <div class="info-value">{{ $elevator->type ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="spec-item">
                                <div class="spec-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-weight-hanging"></i>
                                </div>
                                <span class="info-label">Tải trọng</span>
                                <div class="info-value">{{ $elevator->capacity ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Map Card --}}
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-map-marked-alt me-2"></i> VỊ TRÍ LẮP ĐẶT</h6>
                        @if($elevator->map)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $elevator->map }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                                <i class="fas fa-external-link-alt me-1"></i> Google Maps
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="map"></div>
                    @if(!$elevator->map)
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-map-marker-slash fa-3x mb-3 opacity-25"></i>
                            <p>Chưa có dữ liệu vị trí cho thang máy này.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Info Column --}}
        <div class="col-lg-4">
            {{-- Status Card --}}
            <div class="tech-card mb-4">
                <div class="tech-header bg-dark">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-shield-alt me-2"></i> TRẠNG THÁI HỆ THỐNG</h6>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        @if ($elevator->status === 'active')
                            <div class="display-6 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                            <span class="badge bg-success px-4 py-2 rounded-pill fw-bold shadow-sm">ĐANG HOẠT ĐỘNG</span>
                        @elseif($elevator->status === 'error')
                            <div class="display-6 text-danger mb-2"><i class="fas fa-exclamation-triangle"></i></div>
                            <span class="badge bg-danger px-4 py-2 rounded-pill fw-bold shadow-sm">ĐANG CÓ LỖI</span>
                        @elseif($elevator->status === 'maintenance')
                            <div class="display-6 text-warning mb-2"><i class="fas fa-tools"></i></div>
                            <span class="badge bg-warning px-4 py-2 rounded-pill fw-bold shadow-sm">ĐANG BẢO TRÌ</span>
                        @endif
                    </div>
                    
                    <div class="p-3 bg-light rounded-4 border">
                        <span class="info-label">Chu kỳ bảo trì</span>
                        <div class="info-value text-primary fs-4">{{ $elevator->cycle_days }} <small class="text-muted">ngày</small></div>
                    </div>
                </div>
            </div>

            {{-- Maintenance Card --}}
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-calendar-check me-2"></i> LỊCH TRÌNH BẢO TRÌ</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <span class="info-label">Hạn bảo trì tiếp theo</span>
                        <div class="info-value d-flex align-items-center">
                            <i class="fas fa-clock text-warning me-2"></i>
                            {{ $elevator->maintenance_deadline ? $elevator->maintenance_deadline->format('d/m/Y') : 'Chưa thiết lập' }}
                            @if($elevator->maintenance_deadline && $elevator->maintenance_deadline->isPast())
                                <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">QUÁ HẠN</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <span class="info-label">Ngày hết hạn bảo trì hợp đồng</span>
                        <div class="info-value d-flex align-items-center">
                            <i class="fas fa-calendar-times text-danger me-2"></i>
                            {{ $elevator->maintenance_end_date ? $elevator->maintenance_end_date->format('d/m/Y') : 'Chưa thiết lập' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Note Card --}}
            <div class="tech-card mb-4">
                <div class="tech-header bg-secondary">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-sticky-note me-2"></i> GHI CHÚ</h6>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-muted" style="white-space: pre-line;">{{ $elevator->note ?: 'Không có ghi chú nào.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($elevator->map)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const coords = "{{ $elevator->map }}".split(',');
                if (coords.length === 2) {
                    const lat = parseFloat(coords[0]);
                    const lng = parseFloat(coords[1]);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const map = L.map('map', {
                            dragging: false,
                            scrollWheelZoom: false,
                            doubleClickZoom: false,
                            boxZoom: false,
                            touchZoom: false
                        }).setView([lat, lng], 16);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);

                        L.marker([lat, lng]).addTo(map)
                            .bindPopup('<b>{{ $elevator->code }}</b><br>{{ $elevator->customer_name ?? ($elevator->building->name ?? "") }}')
                            .openPopup();
                    }
                }
            });
        </script>
    @endif
@endsection
