@extends('layouts.admin')

@section('title', 'Báo cáo sự cố mới')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Hệ thống</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.incidents.index') }}">Quản lý Sự cố</a></li>
            <li class="breadcrumb-item active" aria-current="page">Báo sự cố mới</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-plus-circle text-danger me-2"></i> Báo cáo sự cố mới</h1>
        <a href="{{ route('admin.incidents.index') }}" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="tech-card p-4 p-md-5 mb-5">
            <form action="{{ route('admin.incidents.store') }}" method="POST">
                @csrf
                
                @include('admin.incidents._form')

                <hr class="my-5 opacity-10">

                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-light px-5 py-3 rounded-4 fw-bold">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-5 py-3 rounded-4 fw-bold shadow">
                        <i class="fas fa-paper-plane me-2"></i> Gửi báo cáo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
