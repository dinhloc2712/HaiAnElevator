@extends('layouts.admin')

@section('title', 'Thêm thang máy mới')

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

                        <hr class="my-4 opacity-50">
                        <h6 class="fw-bold fs-6 mb-3 small text-uppercase text-primary"><i class="fas fa-microchip me-2"></i> Thông số kỹ thuật</h6>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Hãng sản xuất</label>
                                <input type="text" name="manufacturer" class="form-control modern-form-control"
                                    value="{{ old('manufacturer') }}" placeholder="Ví dụ: Mitsubishi, Otis...">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">MODEL</label>
                                <input type="text" name="model" class="form-control modern-form-control"
                                    value="{{ old('model') }}" placeholder="Nhập model máy...">
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
