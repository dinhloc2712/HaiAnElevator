@extends('layouts.admin')

@section('title', 'Báo cáo sự cố mới')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 52px; border-radius: 1rem; border: 1px solid #d1d3e2; display: flex; align-items: center;
            background-color: #fff; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 50px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 52px; padding-left: 16px; font-size: 0.95rem; }
        .select2-dropdown { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .select2-search--dropdown .select2-search__field { border-radius: 8px; padding: 8px 12px; border: 1px solid #e2e8f0; }
        .select2-results__option { padding: 10px 14px; border-radius: 8px; margin-bottom: 2px; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #e74a3b; }
    </style>
@endsection

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

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#select-elevator').select2({
                placeholder: '-- Chọn thang máy --',
                allowClear: true,
                width: '100%',
                language: { noResults: function() { return 'Không tìm thấy thang máy'; } }
            });
            $('#select-staff').select2({
                placeholder: '-- Chọn nhân viên xử lý --',
                allowClear: true,
                width: '100%',
                language: { noResults: function() { return 'Không tìm thấy nhân viên'; } }
            });
        });
    </script>
@endsection
