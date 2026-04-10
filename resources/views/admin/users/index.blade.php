@extends('layouts.admin')

@section('title', 'Quản lý tài khoản')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Tài khoản</h1>
        <p class="mb-0 text-muted small">Danh sách nhân viên và phân quyền hệ thống</p>
    </div>
</div>

{{-- Quick Filter: Roles --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.users.index') }}" 
       class="btn {{ !request('role_id') ? 'btn-primary' : 'btn-white border text-muted' }} rounded-pill px-3 fw-bold shadow-sm"
       style="transition: all 0.2s;">
        Tất cả
    </a>
    @foreach($roles as $role)
        <a href="{{ route('admin.users.index', ['role_id' => $role->id]) }}" 
           class="btn {{ request('role_id') == $role->id ? 'btn-primary' : 'btn-white border text-muted' }} rounded-pill px-3 fw-bold shadow-sm"
           style="transition: all 0.2s;">
            {{ $role->display_name ?? $role->name }}
        </a>
    @endforeach
</div>

<div class="tech-card h-100">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-users me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Danh sách Tài khoản
            </h6>

            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex align-items-center flex-wrap gap-2">
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
                        <input type="text" name="search" class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2" placeholder="Tìm tên, email..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter --}}
                <div class="dropdown">
                    <button class="btn bg-white rounded-pill fw-bold text-dark px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1 text-secondary"></i>
                        @if(request('role_id') || request('is_active'))
                            <span class="text-primary">Đã lọc</span>
                        @else
                            Lọc
                        @endif
                    </button>
                    <div class="dropdown-menu shadow-lg border-0 mt-2 p-3" style="width: 320px;">
                        <h6 class="dropdown-header px-0 text-uppercase fw-bold mb-2 small text-muted">Bộ lọc tìm kiếm</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Chức vụ</label>
                            <select name="role_id" class="form-select form-select-sm">
                                <option value="">-- Tất cả chức vụ --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name ?? $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Trạng thái</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">-- Tất cả --</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Vô hiệu</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Áp dụng</button>
                        @if(request('role_id') || request('is_active'))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-link btn-sm w-100 mt-1 text-decoration-none text-muted">Xóa bộ lọc</a>
                        @endif
                    </div>
                </div>

                {{-- Add Button --}}
                @can('create_user')
                <a href="{{ route('admin.users.create') }}" class="text-white fw-bold px-2 text-decoration-none d-flex align-items-center">
                    <i class="fas fa-plus me-1"></i> Thêm mới
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
                        <th class="ps-4">ID</th>
                        <th class="text-center" style="width: 60px;">Avatar</th>
                        <x-admin.table-header key="name" label="Họ tên" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="email" label="Email" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <th>Chức vụ</th>
                        <x-admin.table-header key="is_active" label="Trạng thái" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <x-admin.table-header key="created_at" label="Ngày tạo" :sortColumn="$sortColumn" :sortOrder="$sortOrder" />
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">{{ $user->id }}</td>
                        <td class="text-center">
                            @if($user->avatar)
                                <img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #fff;">
                            @else
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light text-secondary fw-bold shadow-sm" style="width: 40px; height: 40px; border: 2px solid #fff;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td style="min-width: 150px;">
                            @can('assign_role')
                                <form action="{{ route('admin.users.update_role', $user) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role_id" class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary shadow-none py-0 ps-0" 
                                            onchange="if(confirm('Bạn có chắc chắn muốn đổi chức vụ cho {{ $user->name }}?')) this.form.submit(); else this.value='{{ $user->role_id }}';" 
                                            style="cursor: pointer; width: auto;">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                {{ $role->display_name ?? $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                @if($user->role)
                                    <span class="badge badge-tech text-primary bg-primary bg-opacity-10">{{ $user->role->display_name ?? $user->role->name }}</span>
                                @else
                                    <span class="text-muted small fst-italic">Chưa cấp quyền</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill" style="font-weight: 600; font-size: 0.75rem;">Hoạt động</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill" style="font-weight: 600; font-size: 0.75rem;">Vô hiệu</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end pe-4">
                            @can('update_user')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            
                            @can('delete_user')
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" id="delete-form-{{ $user->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xóa" onclick="confirmDelete({{ $user->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; cursor: not-allowed;" title="Không thể xóa chính mình" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="fas fa-users-slash fa-3x text-secondary"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Không tìm thấy tài khoản nào</h6>
                                <p class="text-muted small mb-0">Thử thay đổi bộ lọc hoặc thêm tài khoản mới.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Hành động này không thể hoàn tác!",
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
