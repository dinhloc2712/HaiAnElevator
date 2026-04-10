@extends('layouts.admin')
@section('title', 'Chỉnh sửa chi nhánh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Chỉnh sửa chi nhánh</h1>
        <p class="text-muted small mb-0">Cập nhật thông tin: <strong>{{ $branch->name }}</strong></p>
    </div>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.branches.update', $branch) }}" method="POST">
    @csrf @method('PUT')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên chi nhánh <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $branch->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $branch->phone) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address', $branch->address) }}">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               value="1" {{ $branch->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">Đang hoạt động</label>
                    </div>
                </div>
                @if($branch->users_count > 0)
                <div class="col-12">
                    <div class="alert alert-info border-0 rounded-3 py-2 px-3" style="font-size:0.88rem;">
                        <i class="fas fa-info-circle me-2"></i>
                        Chi nhánh này hiện có <strong>{{ $branch->users_count }}</strong> nhân viên.
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end gap-2 px-4 pb-4 pt-0 border-0">
            <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Cập nhật
            </button>
        </div>
    </div>
</form>
@endsection
