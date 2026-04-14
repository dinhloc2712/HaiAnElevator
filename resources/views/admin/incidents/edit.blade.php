@extends('layouts.admin')

@section('title', 'Cập nhật sự cố: ' . $incident->code)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Hệ thống</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.incidents.index') }}">Quản lý Sự cố</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sửa: {{ $incident->code }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="far fa-edit text-primary me-2"></i> Chỉnh sửa sự cố: {{ $incident->code }}</h1>
        <a href="{{ route('admin.incidents.index') }}" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="tech-card p-4 p-md-5 mb-5">
            <form action="{{ route('admin.incidents.update', $incident->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                @include('admin.incidents._form')

                <hr class="my-5 opacity-10">

                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-light px-5 py-3 rounded-4 fw-bold">Hoàn tác</button>
                    <button type="submit" class="btn btn-primary px-5 py-3 rounded-4 fw-bold shadow">
                        <i class="fas fa-save me-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
