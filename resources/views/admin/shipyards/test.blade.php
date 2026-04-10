@extends('layouts.admin')

@section('title', 'Chỉnh sửa Cơ sở Đóng mới')

@section('content')
    <div x-data="shipyardEditor()">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Chỉnh sửa Cơ sở Đóng mới</h1>
                <p class="text-muted small mb-0">Cập nhật thông tin và quản lý hồ sơ của xưởng: <strong
                        class="text-primary">{{ $shipyard->name }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.shipyards.show', $shipyard->id) }}"
                    class="btn btn-info text-white shadow-sm d-flex align-items-center">
                    <i class="fas fa-external-link-alt me-1"></i> Xem Chi Tiết
                </a>
                <a href="{{ route('admin.shipyards.index') }}" class="btn btn-tech-outline d-flex align-items-center">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
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

        <div class="row">
            <!-- Main Form -->
            <div class="col-xl-8 mb-4">
                <form action="{{ route('admin.shipyards.update', $shipyard->id) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                                        value="{{ old('name', $shipyard->name) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Giấy Phép Kinh
                                        Doanh</label>
                                    <input type="text" name="license_number" class="form-control modern-form-control"
                                        value="{{ old('license_number', $shipyard->license_number) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Trạng Thái <span
                                            class="text-danger">*</span></label>
                                    <select name="status" class="form-select modern-form-control" required>
                                        <option value="active"
                                            {{ old('status', $shipyard->status) === 'active' ? 'selected' : '' }}>Hoạt động
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $shipyard->status) === 'inactive' ? 'selected' : '' }}>Tạm
                                            ngưng</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-0">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú bổ
                                        sung</label>
                                    <textarea name="notes" rows="4" class="form-control modern-form-control">{{ old('notes', $shipyard->notes) }}</textarea>
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
                                    <label class="form-label fw-bold small text-uppercase text-muted">Họ Tên Chủ Cơ Sở <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control modern-form-control"
                                        value="{{ old('owner_name', $shipyard->owner_name) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Số CCCD / CMND</label>
                                    <input type="text" name="owner_id_card" class="form-control modern-form-control"
                                        value="{{ old('owner_id_card', $shipyard->owner_id_card) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Số liên hệ</label>
                                    <input type="text" name="phone" class="form-control modern-form-control"
                                        value="{{ old('phone', $shipyard->phone) }}">
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
                                        <select name="ward_id" id="ward" class="form-select modern-form-control"
                                            disabled>
                                            <option value="">-- Phường/Xã (Quận/Huyện) --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <input type="text" name="address" id="street"
                                            class="form-control modern-form-control"
                                            value="{{ old('address', $shipyard->address) }}"
                                            placeholder="Số nhà, tên đường cụ thể...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.shipyards.index') }}" class="btn btn-tech-outline">
                            <i class="fas fa-times me-1"></i> Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-tech-primary">
                            <i class="fas fa-save me-1"></i> Cập Nhật Thông Tin
                        </button>
                    </div>
                </form>
            </div>

            <!-- File Management Column -->
            <div class="col-xl-4 mb-4">
                <div class="tech-card h-100">
                    <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                        <h5 class="m-0 fw-bold text-white"><i class="fas fa-folder-open me-2"></i> Kho Lưu Trữ File</h5>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <!-- File Upload Form -->
                        <div class="mb-4 bg-white p-3 rounded-4 border shadow-sm">
                            <label class="form-label fw-bold small text-uppercase text-primary mb-2"><i
                                    class="fas fa-cloud-upload-alt me-1"></i> Tải Thêm File Trực Tiếp</label>
                            <form id="uploadForm" @submit.prevent="uploadFiles">
                                <input type="file" id="fileInputs"
                                    class="form-control form-control-sm modern-form-control mb-2" multiple required>
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100 fw-bold border-dashed"
                                    :disabled="isUploading">
                                    <span x-show="!isUploading"><i class="fas fa-upload me-1"></i> Tải Lên</span>
                                    <span x-show="isUploading"><i class="fas fa-spinner fa-spin me-1"></i> Đang
                                        tải...</span>
                                </button>
                            </form>
                        </div>

                        <!-- File List -->
                        <div class="file-list-container">
                            <h6 class="fw-bold small text-uppercase text-muted mb-3">Danh sách tài liệu (<span
                                    x-text="files.length"></span>)</h6>

                            <template x-if="files.length === 0">
                                <div class="text-center py-4 bg-white rounded-4 border"
                                    style="border-style: dashed !important; border-width: 2px !important; border-color: #cbd5e1 !important;">
                                    <i class="fas fa-folder-minus text-muted mb-2 fs-3 opacity-50"></i>
                                    <p class="text-muted small mb-0 fw-bold">Chưa có tài liệu nào.</p>
                                </div>
                            </template>

                            <div class="d-flex flex-column gap-2"
                                style="max-height: 480px; overflow-y: auto; padding-right: 5px;">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="bg-white p-2 rounded-3 border d-flex align-items-center shadow-sm">
                                        <div class="bg-light rounded p-2 text-primary me-2 flex-shrink-0">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="flex-grow-1 text-truncate pe-2">
                                            <a :href="file.url" target="_blank"
                                                class="fw-bold small text-dark text-decoration-none d-block text-truncate"
                                                x-text="file.filename" :title="file.filename"></a>
                                        </div>
                                        <button type="button"
                                            class="btn btn-sm btn-light text-danger rounded-circle flex-shrink-0"
                                            @click="deleteFile(file.path)" title="Xóa file">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function shipyardEditor() {
            return {
                shipyardId: {{ $shipyard->id }},
                files: @json($shipyard->files ?? []),
                isUploading: false,

                async uploadFiles() {
                    const input = document.getElementById('fileInputs');
                    if (!input.files || input.files.length === 0) return;

                    this.isUploading = true;
                    const formData = new FormData();
                    for (let i = 0; i < input.files.length; i++) {
                        formData.append('files[]', input.files[i]);
                    }

                    try {
                        const res = await fetch(`/admin/shipyards/${this.shipyardId}/upload-file`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.files = data.files;
                            input.value = ''; // clear input

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Tải file lên thành công'
                            });
                        } else {
                            throw new Error(data.message || 'Lỗi server');
                        }
                    } catch (error) {
                        Swal.fire('Thất bại', error.message || 'Không thể tải lên', 'error');
                    } finally {
                        this.isUploading = false;
                    }
                },

                async deleteFile(path) {
                    const confirmed = await Swal.fire({
                        title: 'Xác nhận xóa?',
                        text: "File đã xóa không thể khôi phục định dạng cứng!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Vâng, Xóa',
                        cancelButtonText: 'Hủy'
                    });

                    if (confirmed.isConfirmed) {
                        try {
                            const res = await fetch(`/admin/shipyards/${this.shipyardId}/delete-file`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': CSRF,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    path: path
                                })
                            });

                            const data = await res.json();
                            if (data.success) {
                                this.files = this.files.filter(f => f.path !== path);
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Đã xóa file'
                                });
                            } else {
                                throw new Error(data.message || 'Lỗi bất định');
                            }
                        } catch (error) {
                            Swal.fire('Lỗi', 'Không thể xóa file: ' + error.message, 'error');
                        }
                    }
                }
            };
        }

        function initializeAddressPicker() {
            const provinceSelect = document.getElementById('province');
            const wardSelect = document.getElementById('ward');

            if (!provinceSelect || !wardSelect) return;

            // Fetch Provinces
            fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
                .then(response => response.json())
                .then(data => {
                    if (data.error === 0) {
                        data.data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.full_name;
                            option.dataset.id = item.id;
                            option.text = item.full_name;

                            const oldProvince = "{{ old('province_id', $shipyard->province_id) }}";
                            if (item.full_name === oldProvince || item.id == oldProvince) {
                                option.selected = true;
                                loadWards(item.id, "{{ old('ward_id', $shipyard->ward_id) }}");
                            }

                            provinceSelect.add(option);
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

            // Attach Listeners
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
