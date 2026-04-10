@extends('layouts.admin')

@section('title', 'Phê duyệt & Đề xuất')

@section('content')
@php
    $canApprove = auth()->user()->hasPermission('approve_proposal');
    $canCreate = auth()->user()->hasPermission('create_proposal');
    $canDelete = auth()->user()->hasPermission('delete_proposal');
@endphp

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">


<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Đề xuất & Phê duyệt</h1>
        <p class="mb-0 text-muted small">Mọi đề xuất công việc đều phải thông qua Giám đốc phê duyệt.</p>
    </div>
    @if($canCreate)
    <div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#createProposalModal">
            <i class="fas fa-plus me-1"></i> Tạo đề xuất mới
        </button>
    </div>
    @endif
</div>

<div class="row" x-data="proposalManager()">
    {{-- Left Column: List & Filters --}}
    @include('admin.proposals.partials.list')

    {{-- Right Column: Details --}}
    @include('admin.proposals.partials.detail')

    {{-- Modals and Scripts (The closing div for x-data is inside modal.blade.php) --}}
    @include('admin.proposals.partials.modal')

@endsection
