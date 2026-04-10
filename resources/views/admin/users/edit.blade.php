@extends('layouts.admin')

@section('title', 'Chỉnh sửa tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Chỉnh sửa tài khoản</h1>
        <p class="text-muted small mb-0">Cập nhật thông tin của: <strong>{{ $user->name }}</strong></p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-tech-outline">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
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
                        @if($user->avatar)
                            <img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar" class="avatar-preview shadow-sm" id="avatarPreview" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;">
                        @else
                            <div class="avatar-placeholder d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" id="avatarPreview" style="width: 120px; height: 120px; border-radius: 50%; font-size: 2.5rem; font-weight: bold; border: 3px solid #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
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
                            <input type="text" name="name" class="form-control modern-form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control modern-form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control modern-form-control" value="{{ old('phone', $user->phone) }}" placeholder="0xxx xxx xxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Mã nhân viên</label>
                            <input type="text" name="code" class="form-control modern-form-control" value="{{ old('code', $user->code) }}" placeholder="NV-0001">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #f6c23e 0%, #daa520 100%);">
            <h5 class="m-0 fw-bold"><i class="fas fa-lock me-2"></i> Bảo mật</h5>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196f3 !important;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Lưu ý:</strong> Để trống mật khẩu nếu không muốn thay đổi
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Mã khẩu mới</label>
                    <input type="password" name="password" class="form-control modern-form-control" placeholder="Nhập mật khẩu mới">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" class="form-control modern-form-control" placeholder="Nhập lại mật khẩu mới">
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
                    <input type="text" class="form-control modern-form-control" value="{{ $user->role->display_name ?? 'N/A' }}" disabled>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i> Bạn không thể sửa chức vụ tại đây.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="form-control modern-form-control" value="{{ old('start_date', $user->start_date ? $user->start_date->format('Y-m-d') : '') }}">
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
                        <input type="text" name="street_address" id="street" class="form-control modern-form-control" value="{{ old('street_address', $user->street_address) }}" placeholder="Số nhà, tên đường cụ thể...">
                   </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="activeCheck" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
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
                    <input type="text" name="company_name" class="form-control modern-form-control" value="{{ old('company_name', $user->company_name) }}" placeholder="Nhập tên công ty (nếu có)">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control modern-form-control" value="{{ old('tax_code', $user->tax_code) }}" placeholder="Nhập mã số thuế">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Số tài khoản ngân hàng</label>
                    <input type="text" name="bank_account" class="form-control modern-form-control" value="{{ old('bank_account', $user->bank_account) }}" placeholder="Nhập số tài khoản">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tên ngân hàng</label>
                    <input type="text" name="bank_name" class="form-control modern-form-control" value="{{ old('bank_name', $user->bank_name) }}" placeholder="Ví dụ: Techcombank, Vietcombank...">
                </div>
            </div>
        </div>
    </div>

    {{-- === TÀU PHỤ TRÁCH === --}}
    <div class="tech-card mt-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
            <i class="fas fa-ship me-2"></i>
            Tàu phụ trách
            <span class="badge bg-white text-primary ms-2" id="shipCountBadge">{{ count($assignedShipIds) }}</span>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <input type="text" id="shipSearch" class="form-control modern-form-control"
                       placeholder="Tìm kiếm theo số hiệu hoặc chủ tàu...">
            </div>
            <div class="row g-2" id="shipList" style="max-height: 320px; overflow-y: auto;">
                @forelse($ships as $ship)
                    <div class="col-md-6 ship-item"
                         data-search="{{ strtolower($ship->registration_number . ' ' . $ship->owner_name) }}">
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3 border ship-checkbox-label"
                               style="cursor:pointer; transition: all 0.2s; border-color: #eaecf4 !important;">
                            <input type="checkbox" name="ship_ids[]" value="{{ $ship->id }}"
                                   class="form-check-input ship-check flex-shrink-0 mt-0"
                                   {{ in_array($ship->id, $assignedShipIds) ? 'checked' : '' }}>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-truncate" style="font-size: 0.85rem; color: #4e73df;">
                                    {{ $ship->registration_number }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.78rem;">
                                    {{ $ship->owner_name ?? 'Chưa có chủ tàu' }}
                                </div>
                            </div>
                            <span class="ms-auto flex-shrink-0">
                                @if($ship->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.65rem;">Đạt chuẩn</span>
                                @elseif($ship->status === 'expired')
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill" style="font-size: 0.65rem;">Hết hạn</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.65rem;">{{ $ship->status }}</span>
                                @endif
                            </span>
                        </label>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-ship fa-2x mb-2 d-block opacity-25"></i>
                        Chưa có tàu nào trong hệ thống
                    </div>
                @endforelse
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
                    <input type="text" name="matbao_taxcode" value="{{ old('matbao_taxcode', $user->matbao_taxcode) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="text" name="matbao_username" value="{{ old('matbao_username', $user->matbao_username) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="password" name="matbao_password">
                </div>
                <div class="col-md-6 mb-3">
                    <input type="file" name="matbao_signature_image" accept="image/png, image/jpeg">
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
                    <input type="text" name="mysign_client_id" class="form-control modern-form-control" value="{{ old('mysign_client_id', $user->mysign_client_id) }}" placeholder="Nhập Client ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted d-flex align-items-center gap-2">
                        Client Secret
                        @if($user->mysign_client_secret)
                            <span class="badge bg-success-subtle text-success fw-normal" style="font-size: 0.7rem; border: 1px solid #198754;">
                                <i class="fas fa-check-circle me-1"></i>Đã lưu
                            </span>
                        @endif
                    </label>
                    <input type="password" name="mysign_client_secret" class="form-control modern-form-control"
                        placeholder="{{ $user->mysign_client_secret ? '•••••••••••••••••• (để trống = không đổi)' : 'Nhập Client Secret' }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">User ID</label>
                    <input type="text" name="mysign_user_id" class="form-control modern-form-control" value="{{ old('mysign_user_id', $user->mysign_user_id) }}" placeholder="Nhập User ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Profile ID <span class="text-muted fw-normal">(mặc định: adss:ras:profile:001)</span></label>
                    <input type="text" name="mysign_profile_id" class="form-control modern-form-control" value="{{ old('mysign_profile_id', $user->mysign_profile_id) }}" placeholder="adss:ras:profile:001">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Credential ID <span class="text-muted fw-normal">(tuỳ chọn)</span></label>
                    <input type="text" name="mysign_credential_id" class="form-control modern-form-control" value="{{ old('mysign_credential_id', $user->mysign_credential_id) }}" placeholder="Nhập Credential ID nếu có">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Ảnh chữ ký <span class="text-muted fw-normal">(Nền trong suốt, .png)</span></label>
                    <div class="d-flex align-items-center gap-3">
                        @if($user->mysign_signature_image)
                            <div class="bg-light border rounded p-2" style="width: 100px; height: 60px; display:flex; align-items:center; justify-content:center;">
                                <img src="{{ asset('storage/' . $user->mysign_signature_image) }}" alt="Chữ ký MySign" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        @endif
                        <input type="file" name="mysign_signature_image" class="form-control modern-form-control flex-grow-1" accept="image/png, image/jpeg">
                    </div>
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
            <i class="fas fa-save me-1"></i> Cập nhật
        </button>
    </div>
</form>

<style>
    /* Avatar Styles */
    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e3e6f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .avatar-placeholder {
        width: 150px;
        height: 150px;
        border: 4px solid #e3e6f0;
        border-radius: 50%;
        background: linear-gradient(135deg, #858796 0%, #60616f 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
</style>
@endsection

@section('scripts')
<script>
    function initializeAddressPicker() {
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
                        // Use the full_name directly as the value so the DB saves the string
                        option.value = item.full_name;
                        // Store ID in a data attribute in case we need it for the next API call
                        option.dataset.id = item.id;
                        option.textContent = item.full_name;
                        
                        // Check if the saved text matches the API full_name text
                        if (item.full_name === oldProvince || item.id == oldProvince) {
                            option.selected = true;
                            // Trigger change to load wards if province is selected
                            setTimeout(() => {
                                loadWards(item.id, oldWard);
                            }, 100);
                        }
                        provinceSelect.appendChild(option);
                    });
                }
            });

        // Function to load wards
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
                            // Use the full_name directly as the value so the DB saves the string
                            option.value = item.full_name;
                            // Store ID in a data attribute
                            option.dataset.id = item.id;
                            option.textContent = item.full_name;
                            
                            // Check if the saved text matches the API full_name text
                            if (selectedWard && (item.full_name === selectedWard || item.id == selectedWard)) {
                                option.selected = true;
                            }
                            wardSelect.appendChild(option);
                        });
                    }
                });
        }

        // Change Event
        provinceSelect.addEventListener('change', function() {
            // Retrieve the ID that we stored in a data attribute for the API call
            const selectedOption = this.options[this.selectedIndex];
            const provinceId = selectedOption.dataset.id;
            if(provinceId) {
                loadWards(provinceId);
            } else {
                wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
                wardSelect.disabled = true;
            }
        });

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

    // Ship search + highlight
    const shipSearch = document.getElementById('shipSearch');
    if (shipSearch) {
        shipSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.ship-item').forEach(item => {
                const match = !q || item.dataset.search.includes(q);
                item.style.display = match ? '' : 'none';
            });
        });
    }

    // Highlight checked ships
    document.querySelectorAll('.ship-check').forEach(cb => {
        const label = cb.closest('label');
        function updateHighlight() {
            label.style.borderColor = cb.checked ? '#4e73df' : '#eaecf4';
            label.style.background  = cb.checked ? '#eff6ff' : '';
        }
        updateHighlight();
        cb.addEventListener('change', () => {
            updateHighlight();
            const count = document.querySelectorAll('.ship-check:checked').length;
            const badge = document.getElementById('shipCountBadge');
            if (badge) badge.textContent = count;
        });
    });
</script>
@endsection
