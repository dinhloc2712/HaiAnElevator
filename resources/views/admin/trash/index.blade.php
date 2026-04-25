@extends('layouts.admin')

@section('title', 'Thùng rác hệ thống')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Thùng rác hệ thống</h1>
        <p class="text-muted small mb-0">
            Quản lý các dữ liệu đã xóa tạm thời. 
            <span class="text-danger fw-semibold"><i class="fas fa-clock me-1"></i> Lưu ý: Các mục trong thùng rác sẽ bị xóa vĩnh viễn sau 30 ngày.</span>
        </p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-0 p-0">
        <ul class="nav nav-tabs nav-fill border-0" id="trashTabs" role="tablist">
            @php $first = true; @endphp
            @foreach($trashedData as $type => $items)
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-semibold {{ $first ? 'active' : '' }}" 
                            id="{{ $type }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $type }}-pane" 
                            type="button" role="tab">
                        @php
                            $labels = [
                                'buildings' => 'Tòa nhà',
                                'elevators' => 'Thang máy',
                                'installations' => 'Đơn lắp đặt',
                                'incidents' => 'Sự cố',
                                'users' => 'Tài khoản',
                                'branches' => 'Chi nhánh',
                                'maintenance_checks' => 'Bảo trì',
                            ];
                            $icons = [
                                'buildings' => 'fa-building',
                                'elevators' => 'fa-elevator',
                                'installations' => 'fa-tools',
                                'incidents' => 'fa-exclamation-triangle',
                                'users' => 'fa-users',
                                'branches' => 'fa-code-branch',
                                'maintenance_checks' => 'fa-clipboard-check',
                            ];
                        @endphp
                        <i class="fas {{ $icons[$type] }} me-1"></i> {{ $labels[$type] }}
                        <span class="badge rounded-pill bg-light text-dark ms-1">{{ count($items) }}</span>
                    </button>
                </li>
                @php $first = false; @endphp
            @endforeach
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="trashTabsContent">
            @php $first = true; @endphp
            @foreach($trashedData as $type => $items)
                <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="{{ $type }}-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Thông tin</th>
                                    <th>Ngày xóa</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">
                                                {{ $item->name ?? $item->code ?? $item->full_name ?? $item->email ?? 'N/A' }}
                                            </div>
                                            <div class="text-muted small">ID: {{ $item->id }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark small">{{ $item->deleted_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-muted smaller">{{ $item->deleted_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('admin.trash.restore', [$type, $item->id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                        <i class="fas fa-undo-alt me-1"></i> Khôi phục
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.trash.force_delete', [$type, $item->id]) }}" method="POST" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn? Hành động này không thể hoàn tác.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="fas fa-trash-alt me-1"></i> Xóa vĩnh viễn
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-trash-restore fa-3x mb-3 opacity-25"></i>
                                                <p class="mb-0">Thùng rác trống</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @php $first = false; @endphp
            @endforeach
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        border-radius: 0;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: #f8f9fa;
        border-bottom: 2px solid #0d6efd;
    }
    .smaller {
        font-size: 0.75rem;
    }
</style>
@endsection
