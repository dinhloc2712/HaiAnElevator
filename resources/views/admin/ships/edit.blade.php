@extends('layouts.admin')

@section('title', 'Chỉnh sửa Tàu thuyền')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Chỉnh sửa Tàu thuyền</h1>
        <p class="text-muted small mb-0">Cập nhật thông tin tàu: <strong>{{ $ship->registration_number }}</strong></p>
    </div>
    <a href="{{ route('admin.ships.index') }}" class="btn btn-tech-outline">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.ships.update', $ship) }}" method="POST">
    @csrf
    @method('PUT')
    
    {{-- Registration Info --}}
    <div class="tech-card">
        <div class="tech-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-passport me-2"></i> Thông tin Đăng ký</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Số đăng ký <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="registration_number" class="form-control modern-form-control" value="{{ old('registration_number', $ship->registration_number) }}" required placeholder="VD: QNg-90123-TS">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Ngày đăng ký
                    </label>
                    <input type="date" name="registration_date" class="form-control modern-form-control" value="{{ old('registration_date', $ship->registration_date ? $ship->registration_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Hạn đăng kiểm
                    </label>
                    <input type="date" name="expiration_date" class="form-control modern-form-control" value="{{ old('expiration_date', $ship->expiration_date ? $ship->expiration_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Tình trạng
                    </label>
                    <select name="status" class="form-select modern-form-control">
                        <option value="active" {{ $ship->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="suspended" {{ $ship->status == 'suspended' ? 'selected' : '' }}>Đình chỉ</option>
                        <option value="expired" {{ $ship->status == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-secondary my-3">Thông tin Kỹ thuật</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tên tàu</label>
                    <input type="text" name="name" class="form-control modern-form-control" value="{{ old('name', $ship->name) }}" placeholder="Tên tàu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số hiệu</label>
                    <input type="text" name="hull_number" class="form-control modern-form-control" value="{{ old('hull_number', $ship->hull_number) }}" placeholder="Số hiệu tàu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Công dụng</label>
                    <input type="text" name="usage" class="form-control modern-form-control" value="{{ old('usage', $ship->usage) }}" placeholder="Nghề cá/...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Vùng hoạt động</label>
                    <input type="text" name="operation_area" class="form-control modern-form-control" value="{{ old('operation_area', $ship->operation_area) }}" placeholder="Khơi/Lộng">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số thuyền viên</label>
                    <input type="number" name="crew_size" class="form-control modern-form-control" value="{{ old('crew_size', $ship->crew_size) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Nghề chính</label>
                    <input type="text" name="main_occupation" class="form-control modern-form-control" value="{{ old('main_occupation', $ship->main_occupation) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Nghề phụ</label>
                    <input type="text" name="secondary_occupation" class="form-control modern-form-control" value="{{ old('secondary_occupation', $ship->secondary_occupation) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Vật liệu vỏ</label>
                    <input type="text" name="hull_material" class="form-control modern-form-control" value="{{ old('hull_material', $ship->hull_material) }}" placeholder="Gỗ/Thép/Composite">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Thông số Kích thước & Trọng tải</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tổng dung tích</label>
                    <input type="number" step="0.01" name="gross_tonnage" class="form-control modern-form-control" value="{{ old('gross_tonnage', $ship->gross_tonnage) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Trọng tải (tấn)</label>
                    <input type="number" step="0.01" name="deadweight" class="form-control modern-form-control" value="{{ old('deadweight', $ship->deadweight) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ltk (m)</label>
                    <input type="number" step="0.01" name="length_design" class="form-control modern-form-control" value="{{ old('length_design', $ship->length_design) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Btk (m)</label>
                    <input type="number" step="0.01" name="width_design" class="form-control modern-form-control" value="{{ old('width_design', $ship->width_design) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Lmax (m)</label>
                    <input type="number" step="0.01" name="length_max" class="form-control modern-form-control" value="{{ old('length_max', $ship->length_max) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Bmax (m)</label>
                    <input type="number" step="0.01" name="width_max" class="form-control modern-form-control" value="{{ old('width_max', $ship->width_max) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Dmax (m)</label>
                    <input type="number" step="0.01" name="depth_max" class="form-control modern-form-control" value="{{ old('depth_max', $ship->depth_max) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">d - Mớn nước (m)</label>
                    <input type="number" step="0.01" name="draft" class="form-control modern-form-control" value="{{ old('draft', $ship->draft) }}">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Năm & Nơi Đóng</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Năm đóng</label>
                    <input type="number" name="build_year" class="form-control modern-form-control" value="{{ old('build_year', $ship->build_year) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Nơi đóng (Tỉnh/Thành)</label>
                    <select name="build_place" id="build_province" class="form-select modern-form-control">
                        <option value="">-- Chọn Nơi Đóng --</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Hồ Sơ An Toàn Kỹ Thuật (ATKT)</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số ATKT</label>
                    <input type="text" name="technical_safety_number" class="form-control modern-form-control" value="{{ old('technical_safety_number', $ship->technical_safety_number) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày cấp ATKT</label>
                    <input type="date" name="technical_safety_date" class="form-control modern-form-control" value="{{ old('technical_safety_date', $ship->technical_safety_date ? $ship->technical_safety_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số Biên bản</label>
                    <input type="text" name="record_number" class="form-control modern-form-control" value="{{ old('record_number', $ship->record_number) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày cấp biên bản</label>
                    <input type="date" name="record_date" class="form-control modern-form-control" value="{{ old('record_date', $ship->record_date ? $ship->record_date->format('Y-m-d') : '') }}">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Hệ Thống Máy Chính</h6>
            <div class="mb-3">
                <div id="engines-container">
                    @php
                        $hp_array     = old('engine_hp_inputs', is_array($ship->engine_hp) ? $ship->engine_hp : []);
                        $kw_array     = old('engine_kw_inputs', is_array($ship->engine_kw) ? $ship->engine_kw : []);
                        $mark_array   = old('engine_mark_inputs', is_array($ship->engine_mark) ? $ship->engine_mark : []);
                        $number_array = old('engine_number_inputs', is_array($ship->engine_number) ? $ship->engine_number : []);
                        $count = max(count($hp_array), count($kw_array), count($mark_array), count($number_array), 1);
                    @endphp
                    @for($i = 0; $i < $count; $i++)
                        <div class="row mb-2 engine-row">
                            <div class="col-md-3">
                                <input type="text" name="engine_mark_inputs[]" class="form-control modern-form-control"
                                    value="{{ $mark_array[$i] ?? '' }}" placeholder="Ký hiệu máy">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="engine_number_inputs[]" class="form-control modern-form-control"
                                    value="{{ $number_array[$i] ?? '' }}" placeholder="Số máy">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" name="engine_hp_inputs[]" class="form-control modern-form-control"
                                    value="{{ $hp_array[$i] ?? '' }}" placeholder="HP">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" name="engine_kw_inputs[]" class="form-control modern-form-control"
                                    value="{{ $kw_array[$i] ?? '' }}" placeholder="KW">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                @if($i === 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-engine-btn"><i class="fas fa-plus"></i> Thêm máy</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-engine-btn"><i class="fas fa-trash"></i> Xóa</button>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Hệ Thống Máy Phụ</h6>
            <div class="mb-3">
                <div id="sub-engines-container">
                    @php
                        $sub_hp_array     = old('sub_engine_hp_inputs', is_array($ship->sub_engine_hp) ? $ship->sub_engine_hp : []);
                        $sub_kw_array     = old('sub_engine_kw_inputs', is_array($ship->sub_engine_kw) ? $ship->sub_engine_kw : []);
                        $sub_mark_array   = old('sub_engine_mark_inputs', is_array($ship->sub_engine_mark) ? $ship->sub_engine_mark : []);
                        $sub_number_array = old('sub_engine_number_inputs', is_array($ship->sub_engine_number) ? $ship->sub_engine_number : []);
                        $sub_count = max(count($sub_hp_array), count($sub_kw_array), count($sub_mark_array), count($sub_number_array), 1);
                    @endphp
                    @for($i = 0; $i < $sub_count; $i++)
                        <div class="row mb-2 sub-engine-row">
                            <div class="col-md-3">
                                <input type="text" name="sub_engine_mark_inputs[]" class="form-control modern-form-control"
                                    value="{{ $sub_mark_array[$i] ?? '' }}" placeholder="Ký hiệu máy phụ">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="sub_engine_number_inputs[]" class="form-control modern-form-control"
                                    value="{{ $sub_number_array[$i] ?? '' }}" placeholder="Số máy phụ">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" name="sub_engine_hp_inputs[]" class="form-control modern-form-control"
                                    value="{{ $sub_hp_array[$i] ?? '' }}" placeholder="HP">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" name="sub_engine_kw_inputs[]" class="form-control modern-form-control"
                                    value="{{ $sub_kw_array[$i] ?? '' }}" placeholder="KW">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                @if($i === 0)
                                    <button type="button" class="btn btn-sm btn-outline-info" id="add-sub-engine-btn"><i class="fas fa-plus"></i> Thêm máy</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-sub-engine-btn"><i class="fas fa-trash"></i> Xóa</button>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Owner Info --}}
    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
            <h5 class="m-0 fw-bold"><i class="fas fa-user-tie me-2"></i> Thông tin Chủ phương tiện</h5>
        </div>
        <div class="card-body p-4">
            
            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase text-muted">Liên kết tài khoản (Tự động điền)</label>
                <select id="user-select" name="user_id" class="form-control" placeholder="Tìm kiếm tài khoản người dùng...">
                    <option value="">-- Chọn tài khoản --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" 
                            {{ (old('user_id', $ship->user_id) == $user->id) ? 'selected' : '' }}
                            data-name="{{ $user->name }}" 
                            data-phone="{{ $user->phone }}" 
                            data-address="{{ $user->street_address }}"
                            data-province="{{ $user->province_id }}"
                            data-ward="{{ $user->ward_id }}"
                            data-tax-code="{{ $user->tax_code }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="owner_name" id="owner_name" class="form-control modern-form-control" value="{{ old('owner_name', $ship->owner_name) }}" required placeholder="Nhập tên chủ tàu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số CMND/CCCD</label>
                    <input type="text" name="owner_id_card" id="owner_id_card" class="form-control modern-form-control" value="{{ old('owner_id_card', $ship->owner_id_card) }}" placeholder="Số CCCD">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Điện thoại</label>
                    <input type="text" name="owner_phone" id="owner_phone" class="form-control modern-form-control" value="{{ old('owner_phone', $ship->owner_phone) }}" placeholder="Số điện thoại">
                </div>
            </div>

             <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Địa chỉ thường trú</label>
                <div class="row g-3">
                    {{-- Cột Tỉnh: display + edit trong cùng 1 col --}}
                    <div class="col-md-6">
                        {{-- Hiển thị (mặc định) --}}
                        <div id="province-display-wrap"
                             class="form-control modern-form-control bg-light text-muted"
                             style="cursor:pointer;"
                             title="Bấm để thay đổi tỉnh/thành">
                            <span id="province-display-text">{{ old('province_id', $ship->province_id) ?: '-- Chưa có --' }}</span>
                            <small class="text-secondary ms-1 fst-italic">(bấm để đổi)</small>
                        </div>
                        <input type="hidden" name="province_id" id="province-hidden" value="{{ old('province_id', $ship->province_id) }}">
                        {{-- Edit (ẩn mặc định) --}}
                        <select id="province" class="form-select modern-form-control d-none">
                            <option value="">-- Đang tải... --</option>
                        </select>
                    </div>

                    {{-- Cột Xã: display + edit trong cùng 1 col --}}
                    <div class="col-md-6">
                        {{-- Hiển thị (mặc định) --}}
                        <div id="ward-display-wrap"
                             class="form-control modern-form-control bg-light text-muted"
                             style="cursor:pointer;"
                             title="Bấm để thay đổi phường/xã">
                            <span id="ward-display-text">{{ old('ward_id', $ship->ward_id) ?: '-- Chưa có --' }}</span>
                            <small class="text-secondary ms-1 fst-italic">(bấm để đổi)</small>
                        </div>
                        <input type="hidden" name="ward_id" id="ward-hidden" value="{{ old('ward_id', $ship->ward_id) }}">
                        {{-- Edit (ẩn mặc định) --}}
                        <select id="ward" class="form-select modern-form-control d-none" disabled>
                            <option value="">-- Chọn tỉnh trước --</option>
                        </select>
                    </div>

                   <div class="col-12 mt-2">
                        <input type="text" name="address" id="address" class="form-control modern-form-control" value="{{ old('address', $ship->address) }}" placeholder="Số nhà, thôn/xóm...">
                   </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.ships.index') }}" class="btn btn-tech-outline">
            <i class="fas fa-times me-1"></i> Hủy bỏ
        </a>
        <button type="submit" class="btn btn-tech-primary">
            <i class="fas fa-save me-1"></i> Cập nhật
        </button>
    </div>
</form>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    // ===== ADDRESS PICKER (chỉ load API khi user bấm Thay đổi) =====
    let addressApiLoaded = false;

    function fetchBuildProvinces() {
        const buildProvinceSelect = document.getElementById('build_province');
        if (!buildProvinceSelect || buildProvinceSelect.dataset.loaded) return;
        const oldBuildProvince = "{{ old('build_place', $ship->build_place) }}";
        fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
            .then(r => r.json())
            .then(data => {
                if (data.error === 0) {
                    data.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.full_name;
                        opt.dataset.id = item.id;
                        opt.textContent = item.full_name;
                        if (item.full_name === oldBuildProvince) opt.selected = true;
                        buildProvinceSelect.appendChild(opt);
                    });
                    buildProvinceSelect.dataset.loaded = '1';
                }
            });
    }

    function loadAddressApi() {
        if (addressApiLoaded) return;
        addressApiLoaded = true;

        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');
        provinceSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        function loadWards(provinceId) {
            wardSelect.innerHTML = '<option value="">-- Đang tải xã... --</option>';
            wardSelect.disabled = true;
            fetch(`https://esgoo.net/api-tinhthanh-new/2/${provinceId}.htm`)
                .then(r => r.json())
                .then(data => {
                    wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
                    if (data.error === 0) {
                        wardSelect.disabled = false;
                        data.data.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.full_name;
                            opt.dataset.id = item.id;
                            opt.textContent = item.full_name;
                            wardSelect.appendChild(opt);
                        });
                        // Cập nhật hidden + display ward
                        document.getElementById('ward-hidden').value = '';
                        document.getElementById('ward-display-text').textContent = '-- Chưa chọn --';
                    }
                });
        }

        fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
            .then(r => r.json())
            .then(data => {
                provinceSelect.innerHTML = '<option value="">-- Tỉnh/Thành phố --</option>';
                if (data.error === 0) {
                    data.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.full_name;
                        opt.dataset.id = item.id;
                        opt.textContent = item.full_name;
                        provinceSelect.appendChild(opt);
                    });
                }
            });

        // Khi chọn tỉnh mới → load xã và cập nhật hidden inputs
        provinceSelect.onchange = function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('province-hidden').value = opt.value || '';
            document.getElementById('province-display-text').textContent = opt.value || '-- Chưa có --';
            if (opt.dataset.id) {
                loadWards(opt.dataset.id);
            } else {
                wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
                wardSelect.disabled = true;
            }
        };

        // Khi chọn xã mới → cập nhật hidden inputs
        wardSelect.onchange = function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('ward-hidden').value = opt.value || '';
            document.getElementById('ward-display-text').textContent = opt.value || '-- Chưa có --';
        };
    }

    function switchToEditMode() {
        // Ẩn display divs, hiện edit selects
        document.getElementById('province-display-wrap').classList.add('d-none');
        document.getElementById('ward-display-wrap').classList.add('d-none');
        document.getElementById('province').classList.remove('d-none');
        document.getElementById('ward').classList.remove('d-none');
        // Gỡ hidden inputs, để select thật submit
        document.getElementById('province-hidden').name = '';
        document.getElementById('ward-hidden').name = '';
        document.getElementById('province').name = 'province_id';
        document.getElementById('ward').name = 'ward_id';
        // Load API
        loadAddressApi();
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchBuildProvinces(); // Load build provinces immediately

        // Click vào ô hiển thị để chuyển sang edit mode
        document.getElementById('province-display-wrap').addEventListener('click', switchToEditMode);
        document.getElementById('ward-display-wrap').addEventListener('click', switchToEditMode);

        // Initialize TomSelect
        var tomSelect = new TomSelect("#user-select",{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        // Handle User Selection Change
        tomSelect.on('change', function(value) {
             const selectedOption = tomSelect.options[value];
             if(selectedOption) {
                 const originalOption = tomSelect.input.querySelector(`option[value="${value}"]`);
                 
                 if(originalOption) {
                     document.getElementById('owner_name').value = originalOption.getAttribute('data-name') || '';
                     document.getElementById('owner_phone').value = originalOption.getAttribute('data-phone') || '';
                     document.getElementById('address').value = originalOption.getAttribute('data-address') || '';
                     document.getElementById('owner_id_card').value = originalOption.getAttribute('data-tax-code') || '';
                     
                     const pId = originalOption.getAttribute('data-province');
                     const wId = originalOption.getAttribute('data-ward');

                     // Cập nhật hidden inputs và display text từ data user (không cần load API)
                     if(pId) {
                         document.getElementById('province-hidden').value = pId;
                         document.getElementById('province-display-text').textContent = pId || '-- Chưa có --';
                     }
                     if(wId) {
                         document.getElementById('ward-hidden').value = wId;
                         document.getElementById('ward-display-text').textContent = wId || '-- Chưa có --';
                     }
                 }
             }
         });

         // Logic for Remove Engine buttons existing on load
         document.querySelectorAll('.remove-engine-btn').forEach(btn => {
             btn.addEventListener('click', function() {
                 this.closest('.engine-row').remove();
             });
         });

         // Add Engine Logic
         const addEngineBtn = document.getElementById('add-engine-btn');
         if(addEngineBtn) {
             addEngineBtn.addEventListener('click', function() {
                 const container = document.getElementById('engines-container');
                 const newRow = document.createElement('div');
                 newRow.className = 'row mb-2 engine-row';
                 newRow.innerHTML = `
                     <div class="col-md-5">
                         <input type="number" step="0.01" name="engine_hp_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy chính (HP)">
                     </div>
                     <div class="col-md-5">
                         <input type="number" step="0.01" name="engine_kw_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy chính (KW)">
                     </div>
                     <div class="col-md-2 d-flex align-items-center">
                         <button type="button" class="btn btn-sm btn-outline-danger remove-engine-btn"><i class="fas fa-trash"></i> Xóa</button>
                     </div>
                 `;
                 container.appendChild(newRow);
                 
                 newRow.querySelector('.remove-engine-btn').addEventListener('click', function() {
                     newRow.remove();
                 });
             });
         }

         // Logic for Remove Sub-Engine buttons existing on load
         document.querySelectorAll('.remove-sub-engine-btn').forEach(btn => {
             btn.addEventListener('click', function() {
                 this.closest('.sub-engine-row').remove();
             });
         });

         // Add Sub Engine Logic
         const addSubEngineBtn = document.getElementById('add-sub-engine-btn');
         if(addSubEngineBtn) {
             addSubEngineBtn.addEventListener('click', function() {
                 const container = document.getElementById('sub-engines-container');
                 const newRow = document.createElement('div');
                 newRow.className = 'row mb-2 sub-engine-row';
                 newRow.innerHTML = `
                     <div class="col-md-5">
                         <input type="number" step="0.01" name="sub_engine_hp_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy phụ (HP)">
                     </div>
                     <div class="col-md-5">
                         <input type="number" step="0.01" name="sub_engine_kw_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy phụ (KW)">
                     </div>
                     <div class="col-md-2 d-flex align-items-center">
                         <button type="button" class="btn btn-sm btn-outline-danger remove-sub-engine-btn"><i class="fas fa-trash"></i> Xóa</button>
                     </div>
                 `;
                 container.appendChild(newRow);
                 
                 newRow.querySelector('.remove-sub-engine-btn').addEventListener('click', function() {
                     newRow.remove();
                 });
             });
         }
    });
</script>
@endsection
