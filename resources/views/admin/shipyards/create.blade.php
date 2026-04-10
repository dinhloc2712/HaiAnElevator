@extends('layouts.admin')

@section('title', 'Thêm Cơ sở Đóng mới')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Thêm Cơ sở Đóng mới</h1>
            <p class="text-muted small mb-0">Nhập thông tin chi tiết xưởng đóng tàu mới</p>
        </div>
        <a href="{{ route('admin.shipyards.index') }}" class="btn btn-tech-outline">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Có lỗi xảy ra:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.shipyards.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="tech-card">
            <div class="tech-header">
                <h5 class="m-0 fw-bold"><i class="fas fa-industry me-2"></i> Thông tin Cơ sở</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">
                            Tên Cơ Sở / Xưởng <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control modern-form-control"
                            placeholder="Nhập tên xưởng..." value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Giấy Phép Kinh Doanh</label>
                        <input type="text" name="license_number" class="form-control modern-form-control"
                            placeholder="Số đăng ký kinh doanh..." value="{{ old('license_number') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Trạng Thái <span
                                class="text-danger">*</span></label>
                        <select name="status" class="form-select modern-form-control" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-0">
                        <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú bổ sung</label>
                        <textarea name="notes" rows="4" class="form-control modern-form-control"
                            placeholder="Thêm ghi chú đặc biệt về cơ sở này... (Không bắt buộc)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="tech-card mt-4">
            <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <h5 class="m-0 fw-bold"><i class="fas fa-user-tie me-2"></i> Thông tin Chủ Cơ Sở</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">
                            Họ Tên Chủ Cơ Sở <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="owner_name" class="form-control modern-form-control"
                            placeholder="Nhập họ tên chủ xưởng..." value="{{ old('owner_name') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Số CCCD / CMND</label>
                        <input type="text" name="owner_id_card" class="form-control modern-form-control"
                            placeholder="Nhập số CCCD..." value="{{ old('owner_id_card') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control modern-form-control"
                            placeholder="Nhập SĐT liên hệ..." value="{{ old('phone') }}">
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
                            <input type="text" name="address" id="street" class="form-control modern-form-control"
                                value="{{ old('address') }}" placeholder="Số nhà, tên đường cụ thể...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tech-card mt-4">
            <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                <h5 class="m-0 fw-bold"><i class="fas fa-paperclip me-2"></i> Hồ sơ đính kèm</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Tải lên file tài liệu</label>
                    <div class="border rounded-4 bg-light p-4 position-relative text-center"
                        style="border: 2px dashed #cbd5e1 !important;">
                        <i class="fas fa-cloud-upload-alt fs-1 text-primary mb-3 opacity-50"></i>
                        <p class="mb-3 text-muted">Kéo thả file vào đây hoặc bấm tải lên (<code
                                class="text-primary">.pdf</code>, <code class="text-primary">.doc</code>, <code
                                class="text-primary">.jpg</code>...)</p>
                        <input type="file" name="files[]" id="fileUpload"
                            class="form-control modern-form-control mx-auto" style="max-width: 400px;" multiple>
                    </div>
                    <p class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Có thể chọn nhiều file cùng
                        lúc. Các file sẽ được lưu ẩn tư nhân.</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.shipyards.index') }}" class="btn btn-tech-outline">
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
                                option.value = item.full_name;
                                option.dataset.id = item.id;
                                option.text = item.full_name;

                                // Check if old value exists
                                const oldProvince = "{{ old('province_id') }}";
                                if (item.full_name === oldProvince || item.id == oldProvince) {
                                    option.selected = true;
                                    // Load wards
                                    loadWards(item.id, "{{ old('ward_id') }}");
                                }

                                provinceSelect.add(option);
                            });
                        }
                    });
            }

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
                                option.value = item.full_name;
                                option.dataset.id = item.id;
                                option.text = item.full_name;

                                if (selectedWard && (item.full_name === selectedWard || item.id ==
                                        selectedWard)) {
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
                const provinceId = selectedOption ? selectedOption.dataset.id : null;

                if (provinceId) {
                    loadWards(provinceId);
                } else {
                    wardSelect.innerHTML = '<option value="">-- Phường/Xã (Quận/Huyện) --</option>';
                    wardSelect.disabled = true;
                }
            };
        }

        document.addEventListener('DOMContentLoaded', initializeAddressPicker);
    </script>
@endsection
