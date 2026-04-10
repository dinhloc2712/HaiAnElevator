@extends('layouts.admin')

@section('title', 'Thêm mới tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Thêm mới tài khoản</h1>
        <p class="text-muted small mb-0">Tạo tài khoản người dùng mới trong hệ thống</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-tech-outline">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="tech-card">
        <div class="tech-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-user-circle me-2"></i> Thông tin cá nhân</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- Avatar Section -->
                <div class="col-md-4 mb-4 text-center">
                    <label class="form-label fw-bold small text-uppercase text-muted d-block mb-3">Ảnh đại diện</label>
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="avatar-placeholder d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" id="avatarPreview" style="width: 120px; height: 120px; border-radius: 50%; font-size: 2.5rem; font-weight: bold; border: 3px solid #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    
                    <div class="upload-area p-3 text-center border rounded-3 bg-light position-relative mx-auto" id="drop-zone" style="max-width: 200px; border: 2px dashed #cbd5e1 !important; cursor: pointer; transition: all 0.2s;">
                        <i class="fas fa-camera fa-2x text-secondary mb-2 opacity-50" id="upload-icon"></i>
                        <p class="mb-0 text-dark fw-bold small" id="upload-text">Nhấn để đổi ảnh</p>
                        <input type="file" name="avatar" class="form-control d-none" accept="image/*" id="avatarInput" onchange="previewAvatar(this)">
                    </div>
                    
                    <div class="form-text small mt-2">
                        <i class="fas fa-info-circle me-1"></i> JPG, PNG (Max 2MB)
                    </div>
                </div>

                <!-- Info Section -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">
                                Họ tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="userNameInput" class="form-control modern-form-control" value="{{ old('name') }}" required placeholder="Nhập họ và tên đầy đủ">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control modern-form-control" value="{{ old('email') }}" required placeholder="example@domain.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control modern-form-control" value="{{ old('phone') }}" placeholder="0xxx xxx xxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Mã nhân viên</label>
                            <input type="text" name="code" class="form-control modern-form-control" value="{{ old('code') }}" placeholder="NV-0001">
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Mật khẩu <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password" class="form-control modern-form-control" required placeholder="Nhập mật khẩu">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Xác nhận mật khẩu <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password_confirmation" class="form-control modern-form-control" required placeholder="Nhập lại mật khẩu">
                </div>
            </div>
        </div>
    </div>

    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
            <h5 class="m-0 fw-bold"><i class="fas fa-building me-2"></i> Thông tin công việc</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Vai trò (Chức vụ)</label>
                    <input type="text" class="form-control modern-form-control" value="Mặc định: Customer / Employee" disabled>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i> Bạn có thể thay đổi chức vụ sau khi tạo.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="form-control modern-form-control" value="{{ old('start_date') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Địa chỉ liên hệ</label>
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
                        <input type="text" name="street_address" id="street" class="form-control modern-form-control" value="{{ old('street_address') }}" placeholder="Số nhà, tên đường cụ thể...">
                   </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="activeCheck" name="is_active" value="1" checked>
                    <label class="form-check-label fw-bold" for="activeCheck">
                        <i class="fas fa-check-circle me-1 text-success"></i> Kích hoạt tài khoản
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
            <h5 class="m-0 fw-bold"><i class="fas fa-briefcase me-2"></i> Thông tin Doanh nghiệp</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tên Doanh nghiệp / Công ty</label>
                    <input type="text" name="company_name" class="form-control modern-form-control" value="{{ old('company_name') }}" placeholder="Nhập tên công ty (nếu có)">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control modern-form-control" value="{{ old('tax_code') }}" placeholder="Nhập mã số thuế">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số tài khoản ngân hàng</label>
                    <input type="text" name="bank_account" class="form-control modern-form-control" value="{{ old('bank_account') }}" placeholder="Nhập số tài khoản">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tên ngân hàng</label>
                    <input type="text" name="bank_name" class="form-control modern-form-control" value="{{ old('bank_name') }}" placeholder="Ví dụ: Techcombank, Vietcombank...">
                </div>
            </div>
        </div>
    </div>

    {{-- Mắt Bão CA - ẩn tạm, thay bằng MySign --}}
    {{-- <div class="tech-card mt-4" style="display:none;">
        <div class="tech-header" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
            <h5 class="m-0 fw-bold text-white"><i class="fas fa-signature me-2"></i> Cấu hình chữ ký số Mắt Bão CA</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="matbao_taxcode" class="form-control modern-form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="text" name="matbao_username" class="form-control modern-form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="password" name="matbao_password" class="form-control modern-form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="file" name="matbao_signature_image" class="form-control modern-form-control" accept="image/png, image/jpeg">
                </div>
            </div>
        </div>
    </div> --}}

    {{-- === CẤU HÌNH KÝ SỐ VIETTEL MYSIGN === --}}
    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
            <h5 class="m-0 fw-bold text-white"><i class="fas fa-signature me-2"></i> Cấu hình chữ ký số Viettel MySign</h5>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-warning border-0" style="background: #fff3cd; border-left: 4px solid #f6c23e !important;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Thông tin nội bộ:</strong> Vui lòng liên hệ bộ phận kỹ thuật để được cấp phép thông tin API Cổng ký số Viettel MySign (chỉ áp dụng nếu người dùng có quyền Ký duyệt).
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Client ID</label>
                    <input type="text" name="mysign_client_id" class="form-control modern-form-control" value="{{ old('mysign_client_id') }}" placeholder="Nhập Client ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Client Secret</label>
                    <input type="password" name="mysign_client_secret" class="form-control modern-form-control" value="{{ old('mysign_client_secret') }}" placeholder="Nhập Client Secret">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">User ID</label>
                    <input type="text" name="mysign_user_id" class="form-control modern-form-control" value="{{ old('mysign_user_id') }}" placeholder="Nhập User ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Profile ID <span class="text-muted fw-normal">(mặc định: adss:ras:profile:001)</span></label>
                    <input type="text" name="mysign_profile_id" class="form-control modern-form-control" value="{{ old('mysign_profile_id') }}" placeholder="adss:ras:profile:001">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Credential ID <span class="text-muted fw-normal">(tuỳ chọn)</span></label>
                    <input type="text" name="mysign_credential_id" class="form-control modern-form-control" value="{{ old('mysign_credential_id') }}" placeholder="Nhập Credential ID nếu có">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ảnh chữ ký <span class="text-muted fw-normal">(Nền trong suốt, .png)</span></label>
                    <input type="file" name="mysign_signature_image" class="form-control modern-form-control" accept="image/png, image/jpeg">
                    <div class="form-text small mt-1">
                        <i class="fas fa-info-circle me-1"></i> Ảnh được đính vào tài liệu PDF khi ký số.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-tech-outline">
            <i class="fas fa-times me-1"></i> Hủy bỏ
        </a>
        <button type="submit" class="btn btn-tech-primary">
            <i class="fas fa-save me-1"></i> Lưu lại
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function initializeAddressPicker() {
       const provinceSelect = document.getElementById('province');
       const wardSelect = document.getElementById('ward');

       if (!provinceSelect || !wardSelect) return;

       // 1. Fetch Provinces
       if (provinceSelect.options.length <= 1) { 
           fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
               .then(response => response.json())
               .then(data => {
                   if (data.error === 0) {
                       data.data.forEach(item => {
                           const option = document.createElement('option');
                           // Use the full_name directly as the value so the DB saves the string
                           option.value = item.full_name;
                           // Store ID in a data attribute in case we need it for the next API call
                           option.dataset.id = item.id;
                           option.text = item.full_name;
                           provinceSelect.add(option);
                       });
                   }
               });
       }

       // 2. Attach Listeners
       provinceSelect.onchange = function() {
           wardSelect.length = 1; // Clear wards
           wardSelect.disabled = true;
           
           if (this.value) {
               // Retrieve the ID that we stored in a data attribute
               const selectedOption = this.options[this.selectedIndex];
               const provinceId = selectedOption.dataset.id;
               
               if(!provinceId) return;

               wardSelect.disabled = false;
               fetch(`https://esgoo.net/api-tinhthanh-new/2/${provinceId}.htm`)
                   .then(response => response.json())
                   .then(data => {
                       if (data.error === 0) {
                           data.data.forEach(item => {
                               const option = document.createElement('option');
                               // Use the full_name directly as the value so the DB saves the string
                               option.value = item.full_name;
                               // Store ID in a data attribute in case we need it for the next API call
                               option.dataset.id = item.id;
                               option.text = item.full_name;
                               wardSelect.add(option);
                           });
                       }
                   });
           }
       };

        // Avatar Preview
        window.previewAvatar = function(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarPreview = document.getElementById('avatarPreview');
                    
                    if (avatarPreview.tagName === 'IMG') {
                        avatarPreview.src = e.target.result;
                    } else {
                        // Replace placeholder with image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'avatar-preview shadow-sm';
                        img.id = 'avatarPreview';
                        img.style.cssText = 'width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;';
                        avatarPreview.replaceWith(img);
                    }
                    
                    // Update text to indicate file is selected
                    document.getElementById('upload-text').textContent = 'Đã chọn ảnh mới';
                    document.getElementById('upload-text').classList.add('text-success');
                    document.getElementById('upload-icon').classList.replace('fa-camera', 'fa-check-circle');
                    document.getElementById('upload-icon').classList.replace('text-secondary', 'text-success');
                    document.getElementById('upload-icon').classList.remove('opacity-50');
                };
                reader.readAsDataURL(file);
            }
        };

        // Dynamic initial avatar name based on input
        const nameInput = document.getElementById('userNameInput');
        if(nameInput) {
            nameInput.addEventListener('input', function() {
                const preview = document.getElementById('avatarPreview');
                // Only update if it's still a placeholder (not an image)
                if(preview && preview.tagName !== 'IMG' && this.value.trim().length > 0) {
                    preview.innerHTML = this.value.trim().charAt(0).toUpperCase();
                } else if (preview && preview.tagName !== 'IMG') {
                    preview.innerHTML = '<i class="fas fa-user"></i>';
                }
            });
        }

        // Drag and drop for avatar
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('avatarInput');

        if (dropZone && fileInput) {
            dropZone.addEventListener('click', () => fileInput.click());

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('bg-white', 'border-primary');
                    dropZone.style.borderColor = '#4e73df';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('bg-white', 'border-primary');
                    dropZone.style.borderColor = '#cbd5e1';
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files && files.length > 0) {
                    fileInput.files = files;
                    window.previewAvatar(fileInput);
                }
            }, false);
        }
    }

    document.addEventListener('DOMContentLoaded', initializeAddressPicker);
</script>
@endsection
