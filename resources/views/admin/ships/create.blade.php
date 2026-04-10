@extends('layouts.admin')

@section('title', 'Thêm mới Tàu thuyền')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Thêm mới Tàu thuyền</h1>
        <p class="text-muted small mb-0">Nhập thông tin đăng ký và kỹ thuật của tàu cá</p>
    </div>
    <a href="{{ route('admin.ships.index') }}" class="btn btn-tech-outline">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.ships.store') }}" method="POST">
    @csrf
    
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
                    <input type="text" name="registration_number" class="form-control modern-form-control" value="{{ old('registration_number') }}" required placeholder="VD: QNg-90123-TS">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Ngày đăng ký
                    </label>
                    <input type="date" name="registration_date" class="form-control modern-form-control" value="{{ old('registration_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Hạn đăng kiểm
                    </label>
                    <input type="date" name="expiration_date" class="form-control modern-form-control" value="{{ old('expiration_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Tình trạng
                    </label>
                    <select name="status" class="form-select modern-form-control">
                        <option value="active" selected>Hoạt động</option>
                        <option value="suspended">Đình chỉ</option>
                        <option value="expired">Hết hạn</option>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-secondary my-3">Thông tin Kỹ thuật</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tên tàu</label>
                    <input type="text" name="name" class="form-control modern-form-control" value="{{ old('name') }}" placeholder="Tên tàu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số hiệu</label>
                    <input type="text" name="hull_number" class="form-control modern-form-control" value="{{ old('hull_number') }}" placeholder="Số hiệu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Công dụng</label>
                    <input type="text" name="usage" class="form-control modern-form-control" value="{{ old('usage') }}" placeholder="Nghề cá/...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Vùng hoạt động</label>
                    <input type="text" name="operation_area" class="form-control modern-form-control" value="{{ old('operation_area') }}" placeholder="Khơi/Lộng">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số thuyền viên</label>
                    <input type="number" name="crew_size" class="form-control modern-form-control" value="{{ old('crew_size') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Nghề chính</label>
                    <input type="text" name="main_occupation" class="form-control modern-form-control" value="{{ old('main_occupation') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Nghề phụ</label>
                    <input type="text" name="secondary_occupation" class="form-control modern-form-control" value="{{ old('secondary_occupation') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Vật liệu vỏ</label>
                    <input type="text" name="hull_material" class="form-control modern-form-control" value="{{ old('hull_material') }}" placeholder="Gỗ/Thép/Composite">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Thông số Kích thước & Trọng tải</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tổng dung tích</label>
                    <input type="number" step="0.01" name="gross_tonnage" class="form-control modern-form-control" value="{{ old('gross_tonnage') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Trọng tải (tấn)</label>
                    <input type="number" step="0.01" name="deadweight" class="form-control modern-form-control" value="{{ old('deadweight') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ltk (m)</label>
                    <input type="number" step="0.01" name="length_design" class="form-control modern-form-control" value="{{ old('length_design') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Btk (m)</label>
                    <input type="number" step="0.01" name="width_design" class="form-control modern-form-control" value="{{ old('width_design') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Lmax (m)</label>
                    <input type="number" step="0.01" name="length_max" class="form-control modern-form-control" value="{{ old('length_max') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Bmax (m)</label>
                    <input type="number" step="0.01" name="width_max" class="form-control modern-form-control" value="{{ old('width_max') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Dmax (m)</label>
                    <input type="number" step="0.01" name="depth_max" class="form-control modern-form-control" value="{{ old('depth_max') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">d - Mớn nước (m)</label>
                    <input type="number" step="0.01" name="draft" class="form-control modern-form-control" value="{{ old('draft') }}">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Năm & Nơi Đóng</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Năm đóng</label>
                    <input type="number" name="build_year" class="form-control modern-form-control" value="{{ old('build_year') }}">
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
                    <input type="text" name="technical_safety_number" class="form-control modern-form-control" value="{{ old('technical_safety_number') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày cấp ATKT</label>
                    <input type="date" name="technical_safety_date" class="form-control modern-form-control" value="{{ old('technical_safety_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số Biên bản</label>
                    <input type="text" name="record_number" class="form-control modern-form-control" value="{{ old('record_number') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày cấp biên bản</label>
                    <input type="date" name="record_date" class="form-control modern-form-control" value="{{ old('record_date') }}">
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Hệ Thống Máy Chính</h6>
            <div class="mb-3">
                <div id="engines-container">
                    <div class="row mb-2 engine-row">
                        <div class="col-md-3">
                            <input type="text" name="engine_mark_inputs[]" class="form-control modern-form-control" placeholder="Ký hiệu máy (VD: YANMAR)">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="engine_number_inputs[]" class="form-control modern-form-control" placeholder="Số máy (VD: A10385)">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="engine_hp_inputs[]" class="form-control modern-form-control" placeholder="HP">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="engine_kw_inputs[]" class="form-control modern-form-control" placeholder="KW">
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-engine-btn"><i class="fas fa-plus"></i> Thêm máy</button>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3 mt-3">Hệ Thống Máy Phụ</h6>
            <div class="mb-3">
                <div id="sub-engines-container">
                    <div class="row mb-2 sub-engine-row">
                        <div class="col-md-3">
                            <input type="text" name="sub_engine_mark_inputs[]" class="form-control modern-form-control" placeholder="Ký hiệu máy phụ">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="sub_engine_number_inputs[]" class="form-control modern-form-control" placeholder="Số máy phụ">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="sub_engine_hp_inputs[]" class="form-control modern-form-control" placeholder="HP">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="sub_engine_kw_inputs[]" class="form-control modern-form-control" placeholder="KW">
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info" id="add-sub-engine-btn"><i class="fas fa-plus"></i> Thêm máy</button>
                        </div>
                    </div>
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
                    <input type="text" name="owner_name" id="owner_name" class="form-control modern-form-control" value="{{ old('owner_name') }}" required placeholder="Nhập tên chủ tàu">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số CMND/CCCD</label>
                    <input type="text" name="owner_id_card" id="owner_id_card" class="form-control modern-form-control" value="{{ old('owner_id_card') }}" placeholder="Số CCCD">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Điện thoại</label>
                    <input type="text" name="owner_phone" id="owner_phone" class="form-control modern-form-control" value="{{ old('owner_phone') }}" placeholder="Số điện thoại">
                </div>
            </div>

             <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Địa chỉ thường trú</label>
                <div class="row g-3">
                   <div class="col-md-6">
                       <select name="province_id" id="province" class="form-select modern-form-control">
                            <option value="">-- Tỉnh/Thành phố --</option>
                        </select>
                   </div>
                   <div class="col-md-6">
                        <select name="ward_id" id="ward" class="form-select modern-form-control" disabled>
                            <option value="">-- Phường/Xã (Quận/Huyện) --</option>
                        </select>
                   </div>
                   <div class="col-12 mt-2">
                        <input type="text" name="address" id="address" class="form-control modern-form-control" value="{{ old('address') }}" placeholder="Số nhà, thôn/xóm...">
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
            <i class="fas fa-save me-1"></i> Lưu lại
        </button>
    </div>
</form>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    function initializeAddressPicker() {
       const provinceSelect = document.getElementById('province');
       const wardSelect = document.getElementById('ward');

       if (!provinceSelect || !wardSelect) return;

       // Fetch Provinces for build place as well
       const buildProvinceSelect = document.getElementById('build_province');
       
       fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
           .then(response => response.json())
           .then(data => {
               if (data.error === 0) {
                   data.data.forEach(item => {
                       // Main Address Provinces
                       if(provinceSelect) {
                           const option = document.createElement('option');
                           option.value = item.full_name;
                           option.dataset.id = item.id;
                           option.text = item.full_name;
                           provinceSelect.add(option);
                       }
                       
                       // Build Place Provinces
                       if(buildProvinceSelect) {
                           const clone = document.createElement('option');
                           clone.value = item.full_name;
                           clone.dataset.id = item.id;
                           clone.text = item.full_name;
                           buildProvinceSelect.add(clone);
                       }
                   });
               }
           });

       // Helper function to load wards
       function loadWards(provinceId, selectedWard = null) {
           wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
           wardSelect.disabled = true;

           if (!provinceId) return;

           fetch(`https://esgoo.net/api-tinhthanh-new/2/${provinceId}.htm`)
               .then(response => response.json())
               .then(data => {
                   if (data.error === 0) {
                       wardSelect.disabled = false;
                       data.data.forEach(item => {
                           const option = document.createElement('option');
                           // Use full_name for database
                           option.value = item.full_name;
                           // Keep ID for API requests
                           option.dataset.id = item.id;
                           option.text = item.full_name;
                           
                           // Select old option if matches
                           if (selectedWard && (item.id == selectedWard || item.full_name === selectedWard)) {
                               option.selected = true;
                           }
                           wardSelect.add(option);
                       });
                   }
               });
       }

       // 2. Attach Listeners
       provinceSelect.onchange = function() {
           const selectedOption = this.options[this.selectedIndex];
           const provinceId = selectedOption.dataset.id;
           if(provinceId) {
               loadWards(provinceId);
           } else {
               wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
               wardSelect.disabled = true;
           }
       };

       return loadWards;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const loadWards = initializeAddressPicker();

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
                     
                     if(pId) {
                         // Find the option in the select that has this ID or full_name
                         let foundOption = false;
                         let numericPId = null;
                         for (let i = 0; i < document.getElementById('province').options.length; i++) {
                             let opt = document.getElementById('province').options[i];
                             if (opt.dataset.id == pId || opt.value === pId) {
                                 opt.selected = true;
                                 foundOption = true;
                                 numericPId = opt.dataset.id;
                                 break;
                             }
                         }

                         if (foundOption && numericPId) {
                             loadWards(numericPId, wId);
                         }
                     }
                 }
             }
         });

         // Add Engine Logic
         document.getElementById('add-engine-btn').addEventListener('click', function() {
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

         // Add Sub Engine Logic
         document.getElementById('add-sub-engine-btn').addEventListener('click', function() {
             const container = document.getElementById('sub-engines-container');
             const newRow = document.createElement('div');
             newRow.className = 'row mb-2 engine-row';
             newRow.innerHTML = `
                 <div class="col-md-5">
                     <input type="number" step="0.01" name="sub_engine_hp_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy phụ (HP)">
                 </div>
                 <div class="col-md-5">
                     <input type="number" step="0.01" name="sub_engine_kw_inputs[]" class="form-control modern-form-control" placeholder="Công suất máy phụ (KW)">
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
    });
</script>
@endsection
