@extends('layouts.admin')

@section('title', 'Chi nhánh')


@section('content')
{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-header-title mb-1">Chi nhánh</h1>
        <p class="page-header-sub mb-0">Quản lý danh sách chi nhánh trong hệ thống.</p>
    </div>
    <a href="{{ route('admin.branches.create') }}" class="btn btn-add">
        <i class="fas fa-plus me-md-2"></i><span class="d-none d-md-inline"> Thêm chi nhánh</span>
    </a>
</div>

{{-- Table Card --}}
<div class="branches-card">
    <div class="p-3 border-bottom">
        <form method="GET" action="{{ route('admin.branches.index') }}">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Tìm kiếm chi nhánh..."
                       value="{{ request('search') }}" autocomplete="off">
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Chi nhánh</th>
                <th>Địa chỉ</th>
                <th>Số điện thoại</th>
                <th class="text-center">Nhân viên</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="branch-icon">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <div>
                            <div class="branch-name">{{ $branch->name }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($branch->address)
                        <span class="branch-addr">
                            <i class="fas fa-map-marker-alt me-1 text-muted" style="font-size:0.75rem;"></i>
                            {{ $branch->address }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($branch->phone)
                        <i class="fas fa-phone-alt me-1 text-muted" style="font-size:0.75rem;"></i>
                        {{ $branch->phone }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="users-count">{{ $branch->users_count }}</span>
                    <small class="text-muted d-block" style="font-size:0.75rem;">nhân viên</small>
                </td>
                <td class="text-center">
                    @if($branch->is_active)
                        <span class="badge-active"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Hoạt động</span>
                    @else
                        <span class="badge-inactive"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Tạm dừng</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{{ route('admin.branches.edit', $branch) }}" class="action-btn edit" title="Chỉnh sửa">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
                              onsubmit="return confirm('Xóa chi nhánh \'{{ $branch->name }}\'?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn del" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-code-branch"></i>
                        <p class="mb-0 fw-semibold">Chưa có chi nhánh nào.</p>
                        <p class="small mt-1">Nhấn <strong>Thêm chi nhánh</strong> để bắt đầu.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($branches->hasPages())
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $branches->links() }}
    </div>
    @endif
</div>
@endsection
