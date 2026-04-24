@extends('layouts.admin')

@section('title', 'Quản lý chức vụ (Roles)')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Chức vụ</h1>
        <p class="mb-0 text-muted small">Quản lý vai trò và phân quyền hệ thống</p>
    </div>
</div>

<div class="tech-card">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-user-shield me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Danh sách Chức vụ
            </h6>

            <form method="GET" action="{{ route('admin.roles.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                {{-- Per Page --}}
                <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                    <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                    <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4" style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="bg-white rounded-pill shadow-sm" style="flex: 1; min-width: 200px; max-width: 300px;">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3" style="z-index: 5;"></i>
                        <input type="text" name="search" class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2" placeholder="Tìm tên chức vụ..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Add Button --}}
                @can('create_role')
                <a href="{{ route('admin.roles.create') }}" class="btn-add">
                    <i class="fas fa-plus me-md-1"></i><span class="d-none d-md-inline"> Thêm chức vụ</span>
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
                        <x-admin.table-header key="id" label="ID" :sortColumn="$sortColumn" :sortOrder="$sortOrder" class="ps-4" style="width: 60px;" />
                        <x-admin.table-header key="name" label="Mã chức vụ (Name)" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="display_name" label="Tên hiển thị" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="users_count" label="Số người dùng" :sortColumn="$sortColumn" :sortOrder="$sortOrder" class="text-center" />
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td class="ps-4 small text-muted">#{{ $role->id }}</td>
                        <td><code class="badge bg-light text-dark border">{{ $role->name }}</code></td>
                        <td class="fw-bold text-dark">{{ $role->display_name }}</td>
                        <td class="text-center">
                            <span class="badge badge-tech bg-info">{{ $role->users_count }} users</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('update_role')
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Sửa">
                                    <i class="fas fa-edit" style="font-size: 0.8rem;"></i>
                                </a>
                                @endcan
                                
                                @can('delete_role')
                                @if(!in_array($role->name, ['admin', 'employee', 'customer']))
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa chức vụ này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xóa">
                                            <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" disabled title="Mặc định hệ thống">
                                        <i class="fas fa-lock" style="font-size: 0.8rem;"></i>
                                    </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle p-4 mb-3" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-shield fa-3x text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Không tìm thấy chức vụ nào</h6>
                                <p class="text-muted small mb-0">Tạo chức vụ mới để phân quyền</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
