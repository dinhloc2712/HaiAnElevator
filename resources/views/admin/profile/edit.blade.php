@extends('layouts.admin')

@section('title', 'Chỉnh sửa hồ sơ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Chỉnh sửa hồ sơ</h1>
        <p class="mb-0 text-muted small">Cập nhật thông tin cá nhân</p>
    </div>
    <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-4">
            <div class="tech-card mb-4 text-center p-4">
                <label for="avatarInput" class="position-relative d-inline-block cursor-pointer">
                    <div class="avatar-preview-container">
                        @if($user->avatar)
                            <img src="{{ asset('storage/'.$user->avatar) }}" id="previewImg" class="rounded-circle border border-4 border-light shadow" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div id="previewPlaceholder" class="rounded-circle border border-4 border-light shadow d-flex align-items-center justify-content-center bg-gradient text-white fw-bold" 
                                 style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 3rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow border">
                        <i class="fas fa-camera text-primary"></i>
                    </div>
                </label>
                <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*">
                <p class="text-muted small mt-3 mb-0">Nhấn vào ảnh để thay đổi</p>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <h6 class="m-0 fw-bold text-white"><i class="fas fa-user-edit me-2"></i> Thông tin chung</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Họ và tên</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email (Không thể đổi)</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Mã nhân viên</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->code }}" readonly>
                        </div>
                    </div>

                    {{-- Address Picker Section --}}
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
                                <input type="text" name="street_address" class="form-control modern-form-control" value="{{ old('street_address', $user->street_address) }}" placeholder="Số nhà, tên đường cụ thể...">
                           </div>
                        </div>
                    </div>
                    
                    {{-- Bank Info --}}
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-wallet me-2"></i> Tài khoản nhận lương</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Tên ngân hàng</label>
                            <input type="text" name="bank_name" class="form-control modern-form-control" value="{{ old('bank_name', $user->bank_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Số tài khoản</label>
                            <input type="text" name="bank_account" class="form-control modern-form-control" value="{{ old('bank_account', $user->bank_account) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
                    <h6 class="m-0 fw-bold text-white"><i class="fas fa-lock me-2"></i> Đổi mật khẩu</h6>
                </div>
                <div class="card-body p-4">
                     <div class="alert alert-warning border-0 small">
                        <i class="fas fa-exclamation-triangle me-1"></i> Chỉ nhập bên dưới nếu bạn muốn đổi mật khẩu.
                     </div>
                     <div class="row">
                         <div class="col-md-12 mb-3">
                             <label class="form-label fw-bold small text-muted">Mật khẩu hiện tại (để xác nhận)</label>
                             <input type="password" name="current_password" class="form-control modern-form-control">
                         </div>
                         <div class="col-md-6 mb-3">
                             <label class="form-label fw-bold small text-muted">Mật khẩu mới</label>
                             <input type="password" name="new_password" class="form-control modern-form-control">
                         </div>
                         <div class="col-md-6 mb-3">
                             <label class="form-label fw-bold small text-muted">Xác nhận mật khẩu mới</label>
                             <input type="password" name="new_password_confirmation" class="form-control modern-form-control">
                         </div>
                     </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.profile.show') }}" class="btn btn-light px-4">Hủy</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</form>

<style>
    .modern-form-control {
        padding: 10px 15px;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .modern-form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.15);
    }
</style>

@section('scripts')
<script>
    // Avatar Preview
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const container = document.querySelector('.avatar-preview-container');
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" class="rounded-circle border border-4 border-light shadow" style="width: 150px; height: 150px; object-fit: cover;">`;
            }
            reader.readAsDataURL(file);
        }
    });

    // Address Picker Logic (Matches GiaBao)
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');
        
        if (!provinceSelect || !wardSelect) return;

        const oldProvince = "{{ old('province_id', $user->province_id) }}";
        const oldWard = "{{ old('ward_id', $user->ward_id) }}";

        // Fetch Provinces
        fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
            .then(response => response.json())
            .then(data => {
                if (data.error === 0) {
                    data.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.full_name;
                        // Loose comparison for strings vs numbers
                        if (oldProvince && item.id == oldProvince) {
                            option.selected = true;
                        }
                        provinceSelect.appendChild(option);
                    });

                    // Trigger change to load wards if province is selected
                    if (oldProvince) {
                        loadWards(oldProvince, oldWard);
                    }
                }
            })
            .catch(error => console.error('Error loading provinces:', error));

        // Function to load wards
        function loadWards(provinceId, selectedWardId = null) {
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
                            option.value = item.id;
                            option.textContent = item.full_name;
                            if (selectedWardId && item.id == selectedWardId) {
                                option.selected = true;
                            }
                            wardSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading wards:', error));
        }

        // Change Event using addEventListener
        provinceSelect.addEventListener('change', function() {
            loadWards(this.value);
        });
    });
</script>
@endsection
@endsection
