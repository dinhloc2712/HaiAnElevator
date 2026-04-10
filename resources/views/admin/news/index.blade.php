@extends('layouts.admin')

@section('title', 'Tin Tức & Thông Báo')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Tin tức & Thông báo</h1>
        <p class="mb-0 text-muted small">Danh sách các thông báo của hệ thống</p>
    </div>
</div>

<div class="tech-card h-100 mb-4">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-bullhorn me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Danh sách Thông báo
            </h6>

            <form method="GET" action="{{ route('admin.news.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                {{-- Per Page --}}
                <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                    <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                    <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4" style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="bg-white rounded-pill shadow-sm" style="flex: 1; min-width: 200px; max-width: 300px;">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3" style="z-index: 5;"></i>
                        <input type="text" name="search" class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2" placeholder="Tìm tiêu đề..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Add Button --}}
                @can('create_news')
                <a href="{{ route('admin.news.create') }}" class="btn-add">
                    <i class="fas fa-plus me-1"></i> Tạo thông báo
                </a>
                @endcan
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 40%;">Tiêu đề</th>
                        <th>Ngày gửi</th>
                        <th>Người gửi</th>
                        @if(auth()->user()->can('update_news') || auth()->user()->can('delete_news'))
                        <th>Đối tượng</th>
                        @else
                        <th>Trạng thái</th>
                        @endif
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $item->title }}</div>
                            @if($item->attachment)
                            <small class="text-muted"><i class="fas fa-paperclip"></i> Có đính kèm</small>
                            @endif
                        </td>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($item->creator->name ?? 'S', 0, 1)) }}
                                </div>
                                <span>{{ $item->creator->name ?? 'Hệ thống' }}</span>
                            </div>
                        </td>
                        @if(auth()->user()->can('update_news') || auth()->user()->can('delete_news'))
                        <td>
                            @if($item->recipient_type === 'all')
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">Tất cả</span>
                            @elseif($item->recipient_type === 'role')
                                <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded">Chức vụ ({{ count((array)$item->recipient_ids) }})</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">Cá nhân ({{ count((array)$item->recipient_ids) }})</span>
                            @endif
                        </td>
                        @else
                        <td>
                            @if($item->isReadBy(auth()->user()))
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded">Đã xem</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded">Mới</span>
                            @endif
                        </td>
                        @endif
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.news.show', $item) }}" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update_news')
                            <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete_news')
                            <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="d-inline-block" id="delete-form-{{ $item->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xóa" onclick="confirmDelete({{ $item->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                            <p class="mb-0">Chưa có thông báo nào.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($news->hasPages())
    <div class="card-footer bg-white border-0 mt-2">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Tin tức này sẽ bị xóa và không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endsection
