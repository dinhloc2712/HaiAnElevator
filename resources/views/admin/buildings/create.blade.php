@extends('layouts.admin')

@section('title', 'Thêm tòa nhà')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Thêm tòa nhà mới</h1>
        <p class="text-muted small mb-0">Điền thông tin tòa nhà và khách hàng.</p>
    </div>
    <a href="{{ route('admin.buildings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.buildings.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên tòa nhà <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="VD: Landmark 81" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Khách hàng / Đơn vị sở hữu</label>
                    <input type="text" name="customer_name" class="form-control"
                           value="{{ old('customer_name') }}" placeholder="VD: Vingroup">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address') }}" placeholder="Số nhà, đường, quận, tỉnh/thành phố">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Người liên hệ</label>
                    <input type="text" name="contact_name" class="form-control"
                           value="{{ old('contact_name') }}" placeholder="VD: Nguyễn Văn A">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại liên hệ</label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="{{ old('contact_phone') }}" placeholder="0901234567">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số lượng thang máy</label>
                    <input type="number" name="elevator_count" class="form-control"
                           value="{{ old('elevator_count', 0) }}" min="0">
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Đang hoạt động</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Thông tin bổ sung...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end gap-2 px-4 pb-4 pt-0 border-0">
            <a href="{{ route('admin.buildings.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Lưu tòa nhà
            </button>
        </div>
    </div>
</form>
@endsection
