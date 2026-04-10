@extends('layouts.admin')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Hồ sơ cá nhân</h1>
        <p class="mb-0 text-muted small">Thông tin tài khoản và cập nhật bảo mật</p>
    </div>
    <a href="{{ route('admin.profile.edit') }}" class="btn btn-tech-primary btn-sm rounded-pill px-3">
        <i class="fas fa-edit me-1"></i> Chỉnh sửa hồ sơ
    </a>
</div>

<div class="row">
    {{-- Left: Avatar Card --}}
    <div class="col-md-4">
        <div class="tech-card mb-4 text-center p-4">
            <div class="mb-3 position-relative d-inline-block">
                @if($user->avatar)
                    <img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar" class="rounded-circle border border-4 border-light shadow" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="rounded-circle border border-4 border-light shadow d-flex align-items-center justify-content-center bg-gradient text-white fw-bold" 
                         style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 3rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-light rounded-circle">
                    <span class="visually-hidden">Online</span>
                </span>
            </div>
            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
            <p class="text-muted small mb-3">{{ $user->email }}</p>
            <div class="d-flex justify-content-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    {{ $user->role->display_name ?? 'N/A' }}
                </span>
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                    {{ $user->code ?? 'NV-Undefined' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Right: Info Details --}}
    <div class="col-md-8">
        <div class="tech-card">
            <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <h6 class="m-0 fw-bold text-white"><i class="fas fa-user me-2"></i> Thông tin chi tiết</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Họ và tên</label>
                    <div class="col-sm-8 fw-bold">{{ $user->name }}</div>
                </div>
                <div class="row mb-4">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Email</label>
                    <div class="col-sm-8">{{ $user->email }}</div>
                </div>
                <div class="row mb-4">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Số điện thoại</label>
                    <div class="col-sm-8">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
                </div>
                <div class="row mb-4">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Địa chỉ</label>
                    <div class="col-sm-8">
                        {{ $user->street_address }}
                        @if($user->ward_id || $user->province_id)
                            <br><small class="text-muted">(Đang cập nhật logic hiển thị Tỉnh/Xã)</small>
                        @endif
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-wallet me-2"></i> Thông tin thanh toán</h6>
                <div class="row mb-3">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Ngân hàng</label>
                    <div class="col-sm-8">{{ $user->bank_name ?? '---' }}</div>
                </div>
                <div class="row mb-0">
                    <label class="col-sm-4 text-muted small text-uppercase fw-bold">Số tài khoản</label>
                    <div class="col-sm-8 font-monospace">{{ $user->bank_account ?? '---' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
