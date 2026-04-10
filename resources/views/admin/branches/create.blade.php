@extends('layouts.admin')
@section('title', 'Thêm chi nhánh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Thêm chi nhánh</h1>
        <p class="text-muted small mb-0">Tạo chi nhánh mới trong hệ thống.</p>
    </div>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.branches.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên chi nhánh <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="VD: Chi nhánh Hà Nội" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone') }}" placeholder="024 xxxx xxxx">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address') }}" placeholder="Số nhà, đường, quận, tỉnh/thành phố">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Đang hoạt động</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end gap-2 px-4 pb-4 pt-0 border-0">
            <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Lưu chi nhánh
            </button>
        </div>
    </div>
</form>
@endsection
