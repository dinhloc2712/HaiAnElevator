@extends('layouts.admin')

@section('title', 'Quản lý Tàu thuyền')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Tàu thuyền</h1>
        <p class="mb-0 text-muted small">Danh sách và thông tin chi tiết tàu cá</p>
    </div>
</div>

{{-- Top Dashboard Stats --}}
<div class="row g-3 mb-4">
    {{-- Tổng số --}}
    <div class="col-xl mb-2">
        <a href="{{ route('admin.ships.index') }}" class="text-decoration-none d-block h-100">
            <div class="tech-card h-100 mb-0 d-flex flex-column align-items-center justify-content-center text-center p-4 transition-all hover-shadow {{ !request('status') && !request('expiration') ? 'border border-primary border-2' : '' }}" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; border-radius: 12px;">
                <div class="rounded-3 bg-white bg-opacity-25 d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="fas fa-ship fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng số tàu</h6>
                <h3 class="fw-bold mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            </div>
        </a>
    </div>
    
    {{-- Đạt chuẩn (Xanh) --}}
    <div class="col-xl mb-2">
        <a href="{{ route('admin.ships.index', ['expiration' => 'passed']) }}" class="text-decoration-none d-block h-100">
            <div class="tech-card h-100 mb-0 d-flex flex-column align-items-center justify-content-center text-center p-4 transition-all hover-shadow bg-white {{ request('expiration') == 'passed' ? 'border border-success border-2' : '' }}" style="border-radius: 12px;">
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="far fa-check-circle fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đã Đăng Kiểm</h6>
                <h3 class="fw-bold text-dark mb-2">{{ number_format($stats['passed'] ?? 0) }}</h3>
                <div class="w-100 bg-light rounded-pill" style="height: 4px;">
                    <div class="bg-success rounded-pill" style="height: 100%; width: {{ $stats['total'] > 0 ? ($stats['passed'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </a>
    </div>
    
    {{-- Hết hạn (Đỏ) --}}
    <div class="col-xl mb-2">
        <a href="{{ route('admin.ships.index', ['expiration' => 'expired']) }}" class="text-decoration-none d-block h-100">
            <div class="tech-card h-100 mb-0 d-flex flex-column align-items-center justify-content-center text-center p-4 transition-all hover-shadow bg-white {{ request('expiration') == 'expired' ? 'border border-danger border-2' : '' }}" style="border-radius: 12px;">
                <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Hết hạn ĐK</h6>
                <h3 class="fw-bold text-dark mb-2">{{ number_format($stats['expired'] ?? 0) }}</h3>
                <div class="w-100 bg-light rounded-pill" style="height: 4px;">
                    <div class="bg-danger rounded-pill" style="height: 100%; width: {{ $stats['total'] > 0 ? ($stats['expired'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </a>
    </div>
    
    {{-- Sắp hết hạn (Vàng) --}}
    <div class="col-xl mb-2">
        <a href="{{ route('admin.ships.index', ['expiration' => 'expiring_soon']) }}" class="text-decoration-none d-block h-100">
            <div class="tech-card h-100 mb-0 d-flex flex-column align-items-center justify-content-center text-center p-4 transition-all hover-shadow bg-white {{ request('expiration') == 'expiring_soon' ? 'border border-warning border-2' : '' }}" style="border-radius: 12px;">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="far fa-clock fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sắp hết hạn</h6>
                <h3 class="fw-bold text-dark mb-2">{{ number_format($stats['expiring_soon'] ?? 0) }}</h3>
                <div class="w-100 bg-light rounded-pill" style="height: 4px;">
                    <div class="bg-warning rounded-pill" style="height: 100%; width: {{ $stats['total'] > 0 ? ($stats['expiring_soon'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </a>
    </div>

    {{-- Đang xử lý --}}
    <div class="col-xl mb-2">
        <a href="{{ route('admin.ships.index', ['status' => 'processing']) }}" class="text-decoration-none d-block h-100">
            <div class="tech-card h-100 mb-0 d-flex flex-column align-items-center justify-content-center text-center p-4 transition-all hover-shadow bg-white {{ request('status') == 'processing' ? 'border border-info border-2' : '' }}" style="border-radius: 12px;">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="fas fa-tasks fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đang xử lý</h6>
                <h3 class="fw-bold text-dark mb-2">{{ number_format($stats['processing'] ?? 0) }}</h3>
                <div class="w-100 bg-light rounded-pill" style="height: 4px;">
                    <div class="bg-info rounded-pill" style="height: 100%; width: {{ $stats['total'] > 0 ? ($stats['processing'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="tech-card h-100">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 18px 25px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-ship me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Danh sách Tàu thuyền
            </h6>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Per page selector (standalone form) --}}
                <form method="GET" action="{{ route('admin.ships.index') }}" id="perPageForm">
                    @foreach(request()->except('per_page') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                        <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                        <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4" style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </form>

                {{-- Search Input --}}
                <form action="{{ route('admin.ships.index') }}" method="GET" class="bg-white rounded-pill shadow-sm" style="flex: 1; min-width: 200px; max-width: 300px;">
                    @foreach(request()->except(['search', 'page']) as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $k => $v)
                                <input type="hidden" name="{{ $key }}[{{ $k }}]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3" style="z-index: 5;"></i>
                        <input type="text" name="search" class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2" placeholder="Tìm tên, email..." value="{{ request('search') }}">
                    </div>
                </form>

                {{-- Advanced Filter Toggle Button --}}
                <button class="btn bg-white rounded-pill fw-bold text-dark px-3 py-2 shadow-sm d-flex align-items-center gap-2" type="button" id="btnAdvancedFilter" onclick="toggleAdvancedFilter()">
                    <i class="fas fa-sliders-h text-primary"></i>
                    @php
                        $hasAdvancedFilter = request()->hasAny(['adv_registration','adv_owner','adv_status','adv_expiration','adv_expiring_days','adv_main_occupation','adv_secondary_occupation','adv_usage','adv_power_min','adv_power_max','adv_sort_expiration', 'adv_province', 'adv_ward', 'adv_inspection_start', 'adv_inspection_end']);
                    @endphp
                    @if($hasAdvancedFilter)
                        <span class="text-primary fw-bold">Bộ lọc <span class="badge bg-primary text-white rounded-pill ms-1" style="font-size:0.65rem;">ON</span></span>
                    @else
                        Bộ lọc nâng cao
                    @endif
                </button>

                {{-- Add & Import & Export Buttons --}}
                @can('create_ship')
                <button type="button" class="btn btn-light rounded-pill shadow-sm fw-bold px-3 py-2 text-decoration-none d-flex align-items-center text-success border" data-bs-toggle="modal" data-bs-target="#importShipModal">
                    <i class="fas fa-file-excel me-1"></i> Nhập từ Excel
                </button>
                <a href="{{ route('admin.ships.export', request()->all()) }}" class="btn btn-light rounded-pill shadow-sm fw-bold px-3 py-2 text-decoration-none d-flex align-items-center text-primary border mx-1">
                    <i class="fas fa-file-export me-1"></i> Xuất Excel
                </a>
                <a href="{{ route('admin.ships.create') }}" class="btn btn-success fw-bold px-3 py-2 text-decoration-none d-flex align-items-center rounded-pill shadow-sm text-white">
                    <i class="fas fa-plus me-1"></i> Thêm mới
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Advanced Filter Panel (ProTechUI) --}}
    <div id="advancedFilterPanel" style="display: {{ $hasAdvancedFilter ? 'block' : 'none' }}; background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%); border-bottom: 1px solid #dde3f7;">
        <form method="GET" action="{{ route('admin.ships.index') }}" id="advancedFilterForm">
            <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

            <div class="px-4 pt-4 pb-3">
                {{-- Panel Header --}}
                <div class="d-flex align-items-center mb-3 gap-2">
                    <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                        <i class="fas fa-sliders-h text-primary" style="font-size:.75rem;"></i>
                    </div>
                    <span class="fw-bold text-primary" style="font-size:.85rem; letter-spacing:.3px;">BỘ LỌC NÂNG CAO</span>
                    @if($hasAdvancedFilter)
                    <span class="badge rounded-pill ms-1" style="background: linear-gradient(135deg,#4e73df,#224abe); font-size:.65rem;">
                        <i class="fas fa-check me-1"></i>Đang lọc
                    </span>
                    @endif
                </div>

                {{-- Grid --}}
                <div class="row g-3">

                    {{-- Số đăng ký --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-hashtag me-1 text-primary opacity-75"></i>Số đăng ký
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_registration" class="filter-input" placeholder="VD: NA-90001-TS" value="{{ request('adv_registration') }}">
                        </div>
                    </div>

                    {{-- Chủ phương tiện --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-user me-1 text-indigo opacity-75"></i>Chủ phương tiện
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_owner" class="filter-input" placeholder="Tên, SĐT hoặc Email chủ tàu..." value="{{ request('adv_owner') }}">
                        </div>
                    </div>

                    {{-- Tình trạng tàu --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-traffic-light me-1 text-warning opacity-75"></i>Tình trạng tàu
                        </label>
                        <div class="filter-input-wrap">
                            <select name="adv_status" class="filter-select">
                                <option value="">-- Tất cả --</option>
                                <option value="active" {{ request('adv_status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="suspended" {{ request('adv_status') == 'suspended' ? 'selected' : '' }}>Đình chỉ</option>
                                <option value="processing" {{ request('adv_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            </select>
                        </div>
                    </div>

                    {{-- Hạn đăng kiểm --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-calendar-check me-1 text-success opacity-75"></i>Hạn đăng kiểm
                        </label>
                        <div class="filter-input-wrap">
                            <select name="adv_expiration" class="filter-select" id="adv_expiration_select"
                                onchange="toggleExpiringDaysInput(this.value)">
                                <option value="">-- Tất cả --</option>
                                <option value="valid" {{ request('adv_expiration') == 'valid' ? 'selected' : '' }}>Còn hạn</option>
                                <option value="expiring_soon" {{ request('adv_expiration') == 'expiring_soon' ? 'selected' : '' }}>Sắp hết hạn</option>
                                <option value="expired" {{ request('adv_expiration') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                            </select>
                        </div>
                        <div id="expiring_days_wrap" class="mt-2" style="display: {{ request('adv_expiration') == 'expiring_soon' ? 'block' : 'none' }};">
                            <div class="d-flex align-items-center gap-2">
                                <div class="filter-input-wrap flex-grow-1">
                                    <input type="number" name="adv_expiring_days" id="adv_expiring_days"
                                        class="filter-input" placeholder="Số ngày" min="1" max="3650"
                                        value="{{ request('adv_expiring_days', 30) }}">
                                </div>
                                <span class="small text-muted fw-bold" style="white-space:nowrap;">ngày tới</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tỉnh/Thành phố --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-map-marked-alt me-1 text-primary opacity-75"></i>Tỉnh/Thành phố
                        </label>
                        <div class="filter-input-wrap">
                            <select id="adv_province_select" name="adv_province" class="filter-select">
                                <option value="">-- Tỉnh/Thành phố --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Xã/Phường --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-map-pin me-1 text-danger opacity-75"></i>Xã/Phường
                        </label>
                        <div class="filter-input-wrap">
                            <select id="adv_ward_select" name="adv_ward" class="filter-select" disabled>
                                <option value="">-- Chọn tỉnh trước --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Nghề chính --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-anchor me-1 text-info opacity-75"></i>Nghề chính
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_main_occupation" class="filter-input" list="list_main_occ" placeholder="-- Nghề chính --" value="{{ request('adv_main_occupation') }}">
                            <datalist id="list_main_occ">
                                @foreach($topMainOccupations as $occ)
                                    <option value="{{ $occ }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- Nghề phụ --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-anchor me-1 text-secondary opacity-75"></i>Nghề phụ
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_secondary_occupation" class="filter-input" list="list_secondary_occ" placeholder="-- Nghề phụ --" value="{{ request('adv_secondary_occupation') }}">
                            <datalist id="list_secondary_occ">
                                @foreach($topSecondaryOccupations as $occ)
                                    <option value="{{ $occ }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- Công dụng --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-cogs me-1 text-danger opacity-75"></i>Công dụng
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_usage" class="filter-input" list="list_usage" placeholder="-- Công dụng --" value="{{ request('adv_usage') }}">
                            <datalist id="list_usage">
                                @foreach($topUsages as $u)
                                    <option value="{{ $u }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- Công suất KW --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-bolt me-1 text-warning opacity-75"></i>Công suất (KW)
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="filter-input-wrap flex-grow-1">
                                <input type="number" name="adv_power_min" class="filter-input" placeholder="Từ" value="{{ request('adv_power_min') }}" min="0">
                            </div>
                            <span class="text-muted fw-bold small">–</span>
                            <div class="filter-input-wrap flex-grow-1">
                                <input type="number" name="adv_power_max" class="filter-input" placeholder="Đến" value="{{ request('adv_power_max') }}" min="0">
                            </div>
                        </div>
                    </div>

                    {{-- Sắp xếp HĐK --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-sort me-1 text-primary opacity-75"></i>Sắp xếp hạn ĐK
                        </label>
                        <div class="filter-input-wrap">
                            <select name="adv_sort_expiration" class="filter-select">
                                <option value="">-- Mặc định --</option>
                                <option value="asc" {{ request('adv_sort_expiration') == 'asc' ? 'selected' : '' }}>⬆ Gần nhất trước</option>
                                <option value="desc" {{ request('adv_sort_expiration') == 'desc' ? 'selected' : '' }}>⬇ Xa nhất trước</option>
                            </select>
                        </div>
                    </div>

                    {{-- Ngày ĐK gần nhất --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label" title="Ngày tạo Đề xuất đăng kiểm gần nhất">
                            <i class="fas fa-calendar-alt me-1 text-info opacity-75"></i>Lịch ĐK gần nhất
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="filter-input-wrap flex-grow-1">
                                <input type="date" name="adv_inspection_start" class="filter-input px-2" value="{{ request('adv_inspection_start') }}" title="Từ ngày">
                            </div>
                            <span class="text-muted fw-bold small">–</span>
                            <div class="filter-input-wrap flex-grow-1">
                                <input type="date" name="adv_inspection_end" class="filter-input px-2" value="{{ request('adv_inspection_end') }}" title="Đến ngày">
                            </div>
                        </div>
                    </div>

                </div>{{-- /row --}}
            </div>

            {{-- Action Bar --}}
            <div class="px-4 py-3 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.7); border-top: 1px solid #dde3f7;">
                <span class="text-muted small">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Điền một hoặc nhiều điều kiện rồi nhấn <strong>Tìm kiếm</strong>.
                </span>
                <div class="d-flex gap-2">
                    @if($hasAdvancedFilter)
                    <a href="{{ route('admin.ships.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">
                        <i class="fas fa-undo me-1"></i>Xóa lọc
                    </a>
                    @endif
                    <button type="submit" class="btn btn-sm rounded-pill px-5 fw-bold text-white d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); box-shadow: 0 4px 12px rgba(78,115,223,.35);">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .filter-label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .filter-input-wrap {
            position: relative;
        }
        .filter-input,
        .filter-select {
            width: 100%;
            height: 36px;
            padding: 0 12px;
            font-size: .85rem;
            border: 1.5px solid #dde3f7;
            border-radius: 10px;
            background: #fff;
            color: #495057;
            box-shadow: 0 1px 4px rgba(78,115,223,.06);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            appearance: none;
        }
        .filter-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%234e73df' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
            padding-right: 32px;
        }
        .filter-input:focus,
        .filter-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78,115,223,.15);
        }
        .filter-input::placeholder { color: #adb5bd; }
        #advancedFilterPanel {
            animation: slideDown .2s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {!! nl2br(e(session('error'))) !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mx-4 mt-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {!! nl2br(e(session('warning'))) !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {!! nl2br(e(session('success'))) !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{!! nl2br(e($error)) !!}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <x-admin.table-header key="registration_number" label="Số đăng ký" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="owner_name" label="Chủ phương tiện" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <th>Tỉnh/Huyện</th>
                        <x-admin.table-header key="status" label="Trạng thái" :sortColumn="$sortColumn" :sortOrder="$sortOrder" class="text-center" />
                        <x-admin.table-header key="expiration_date" label="Hạn đăng kiểm" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="created_at" label="Ngày tạo" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ships as $ship)
                    <tr>
                        <td class="ps-4">{{ $ship->id }}</td>
                        <td class="fw-bold text-primary">{{ $ship->registration_number }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $ship->owner_name }}</span>
                                <span class="small text-muted">{{ $ship->owner_phone }}</span>
                            </div>
                        </td>
                        <td class="small text-muted">
                            @if($ship->province_id || $ship->ward_id)
                                <div>{{ $ship->province_id }}</div>
                                @if($ship->ward_id)
                                    <div class="text-secondary" style="font-size:.75rem;">{{ $ship->ward_id }}</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($ship->status == 'active')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">Hoạt động</span>
                            @elseif($ship->status == 'suspended')
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">Đình chỉ</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">{{ ucfirst($ship->status) }}</span>
                            @endif
                        </td>

                        {{-- Inline-edit: Hạn đăng kiểm --}}
                        <td class="expiry-cell" data-ship-id="{{ $ship->id }}"
                            data-url="{{ route('admin.ships.update-expiration', $ship) }}"
                            data-expiry="{{ $ship->expiration_date ? $ship->expiration_date->format('Y-m-d') : '' }}">
                            @php
                                $hHntText = '<span class="text-muted small">Chưa có</span>';
                                if($ship->expiration_date) {
                                    $expiry = $ship->expiration_date;
                                    $formatted = $expiry->format('d/m/Y');
                                    if($expiry->endOfDay()->isPast()) {
                                        $hHntText = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Hết hạn - ' . $formatted . '</span>';
                                    } elseif($expiry->isBetween(now(), now()->addDays(30))) {
                                        $hHntText = '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2" title="Sắp hết hạn">Sắp hết - ' . $formatted . '</span>';
                                    } else {
                                        $hHntText = '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">' . $formatted . '</span>';
                                    }
                                }
                            @endphp
                            @can('update_ship')
                            <span class="expiry-display" title="Click để sửa hạn ĐK" style="cursor:pointer;">{!! $hHntText !!} <i class="fas fa-pen ms-1 text-muted opacity-50" style="font-size:.65rem;"></i></span>
                            <input type="date" class="expiry-input form-control form-control-sm d-none" style="width:140px;"
                                value="{{ $ship->expiration_date ? $ship->expiration_date->format('Y-m-d') : '' }}">
                            @else
                            <span class="expiry-display">{!! $hHntText !!}</span>
                            @endcan
                        </td>

                        <td>{{ $ship->created_at->format('d/m/Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.ships.show', $ship) }}" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update_ship')
                            <a href="{{ route('admin.ships.edit', $ship) }}" class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            
                            @can('delete_ship')
                            <form action="{{ route('admin.ships.destroy', $ship) }}" method="POST" class="d-inline-block" id="delete-form-{{ $ship->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xóa" onclick="confirmDelete({{ $ship->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="fas fa-ship fa-3x text-secondary"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Không tìm thấy tàu thuyền nào</h6>
                                <p class="text-muted small mb-0">Thử thay đổi bộ lọc hoặc thêm mới.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top">
            {{ $ships->links() }}
        </div>
    </div>
</div>

{{-- Import Ship Modal --}}
@can('create_ship')
<div class="modal fade" id="importShipModal" tabindex="-1" aria-labelledby="importShipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="importShipModalLabel">
                    <span class="bg-success bg-opacity-10 text-success p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-file-excel fa-lg"></i>
                    </span>
                    Nhập Dữ Liệu Tàu Hàng Loạt
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.ships.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="alert alert-info border-info shadow-sm mb-4" style="border-radius: 12px; border-left-width: 4px;">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> Hướng dẫn nhập dữ liệu:</h6>
                        <ul class="mb-0 small ps-3">
                            <li class="mb-1">Hỗ trợ tải lên file Excel (<strong>.xlsx</strong>, .xls) hoặc file <strong>.csv</strong>.</li>
                            <li class="mb-1">Dòng đầu tiên (tiêu đề cột) sẽ bị bỏ qua. Dữ liệu bắt đầu từ dòng 2 trở đi.</li>
                            <li class="mb-1">Hệ thống yêu cầu 33 cột dữ liệu. Cột <strong>SĐK (Số đăng ký)</strong> là bắt buộc để phát hiện hoặc tạo tàu. Tàu đã có SĐK sẽ được tự động cập nhật nếu nhập trùng.</li>
                            <li>Định dạng ngày tháng: <code>YYYY-MM-DD</code> hoặc <code>DD/MM/YYYY</code>.</li>
                        </ul>
                    </div>

                    <div class="mb-2">
                        <label for="import_file" class="form-label fw-bold small text-muted text-uppercase mb-2">Chọn file upload (*)</label>
                        <div class="input-group input-group-lg" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-upload text-muted"></i></span>
                            <input type="file" class="form-control border-0 bg-light px-3 py-3" id="import_file" name="file" accept=".xlsx, .xls, .csv, .txt" required style="font-size: 0.95rem;">
                        </div>
                        
                        <div class="form-text mt-3">
                            <span class="text-muted small"><i class="fas fa-file-excel me-1"></i> Kích thước file tối đa: 10MB</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light fw-bold text-muted border shadow-sm rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill shadow-sm px-5 d-flex align-items-center" id="btn-import-submit">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Bắt đầu Nhập
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('scripts')
<script>
    // ---- Inline Edit Expiration Logic ----
    document.addEventListener('DOMContentLoaded', function() {
        const cells = document.querySelectorAll('.expiry-cell');
        
        cells.forEach(cell => {
            const displayObj = cell.querySelector('.expiry-display');
            const inputObj = cell.querySelector('.expiry-input');
            const shipId = cell.dataset.shipId;
            const updateUrl = cell.dataset.url;
            
            if(!displayObj || !inputObj) return;

            // Click to edit
            displayObj.addEventListener('click', function() {
                displayObj.classList.add('d-none');
                inputObj.classList.remove('d-none');
                inputObj.focus();
            });

            // Handle save on blur or enter
            const saveHandler = async function() {
                const newValue = inputObj.value;
                const oldValue = cell.dataset.expiry;
                
                // Hide input, show display
                inputObj.classList.add('d-none');
                displayObj.classList.remove('d-none');
                
                // If unchanged, do nothing
                if (newValue === oldValue) return;

                // Show loading state
                const originalHtml = displayObj.innerHTML;
                displayObj.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span> <span class="small text-muted">Đang lưu...</span>';

                try {
                    const response = await fetch(updateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            expiration_date: newValue
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        // Reload page to re-render server-side badges (warning, expired, etc) properly
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'Lỗi cập nhật');
                    }
                } catch (error) {
                    console.error('Error updating expiration:', error);
                    alert('Lỗi cập nhật hạn đăng kiểm. Thử lại sau.');
                    displayObj.innerHTML = originalHtml; // Revert on error
                }
            };

            inputObj.addEventListener('blur', saveHandler);
            inputObj.addEventListener('keyup', function(e) {
                if(e.key === 'Enter') {
                    inputObj.blur(); // Triggers saveHandler
                } else if(e.key === 'Escape') {
                    // Cancel edit
                    inputObj.value = cell.dataset.expiry; // revert to original
                    inputObj.classList.add('d-none');
                    displayObj.classList.remove('d-none');
                }
            });
        });
    });

    function toggleExpiringDaysInput(val) {
        const wrap = document.getElementById('expiring_days_wrap');
        if (wrap) wrap.style.display = val === 'expiring_soon' ? 'block' : 'none';
    }

    // ---- Advanced Filter Province/Ward Logic ----
    let filterProvincesLoaded = false;

    function toggleAdvancedFilter() {
        const panel = document.getElementById('advancedFilterPanel');
        if (!panel) return;
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';

        // Load provinces from API on first open
        if (!isVisible && !filterProvincesLoaded) {
            loadFilterProvinces();
        }
    }

    function loadFilterProvinces(restoreProvince = null, restoreWard = null) {
        const sel = document.getElementById('adv_province_select');
        if (!sel) return;

        sel.innerHTML = '<option value="">Đang tải...</option>';

        fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
            .then(r => r.json())
            .then(data => {
                filterProvincesLoaded = true;
                sel.innerHTML = '<option value="">-- Tỉnh/Thành phố --</option>';
                data.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.full_name;
                    opt.dataset.id = item.id;
                    opt.textContent = item.full_name;
                    if (restoreProvince && item.full_name === restoreProvince) opt.selected = true;
                    sel.appendChild(opt);
                });

                // If we need to restore ward too, trigger wards loading
                if (restoreProvince && restoreWard) {
                    const selectedOpt = Array.from(sel.options).find(o => o.value === restoreProvince);
                    if (selectedOpt) loadFilterWards(selectedOpt.dataset.id, restoreWard);
                }

                // Attach change event
                sel.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    loadFilterWards(opt.dataset.id, null);
                });
            })
            .catch(() => {
                filterProvincesLoaded = false;
                sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
    }

    function loadFilterWards(provinceId, restoreWard = null) {
        const wardSel = document.getElementById('adv_ward_select');
        if (!wardSel) return;

        wardSel.innerHTML = '<option value="">Đang tải...</option>';
        wardSel.disabled = true;

        if (!provinceId) {
            wardSel.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>';
            return;
        }

        fetch(`https://esgoo.net/api-tinhthanh-new/2/${provinceId}.htm`)
            .then(r => r.json())
            .then(data => {
                wardSel.innerHTML = '<option value="">-- Xã/Phường --</option>';
                wardSel.disabled = false;
                data.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.full_name;
                    opt.textContent = item.full_name;
                    if (restoreWard && item.full_name === restoreWard) opt.selected = true;
                    wardSel.appendChild(opt);
                });
            })
            .catch(() => {
                wardSel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
    }

    // On page load: if filter panel is already open (has active filter), restore province/ward
    document.addEventListener('DOMContentLoaded', function() {
        const savedProvince = {!! json_encode(request("adv_province")) !!};
        const savedWard = {!! json_encode(request("adv_ward")) !!};
        const panel = document.getElementById('advancedFilterPanel');

        if (panel && panel.style.display !== 'none' && savedProvince) {
            loadFilterProvinces(savedProvince, savedWard);
        } else if (panel && panel.style.display !== 'none') {
            loadFilterProvinces();
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('import_file');
        const btnSubmit = document.getElementById('btn-import-submit');

        // Reset modal state on close
        var myModalEl = document.getElementById('importShipModal')
        if (myModalEl) {
            myModalEl.addEventListener('hidden.bs.modal', function (event) {
                if (fileInput) fileInput.value = '';
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i> Bắt đầu Nhập';
                }
            })
        }

        if (btnSubmit) {
            btnSubmit.addEventListener('click', function() {
                if(fileInput && fileInput.files.length > 0) {
                    setTimeout(() => {
                        this.disabled = true;
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang xử lý...';
                    }, 50);
                }
            });
        }
    });
</script>
@endsection
