@extends('layouts.admin')

@section('title', 'Quản lý Cơ sở Đóng mới')

@section('content')
    {{-- Breadcrumb Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Cơ sở Đóng mới</h1>
            <p class="mb-0 text-muted small">Danh sách các xưởng đóng tàu, cơ sở đóng mới</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="tech-card h-100">
        <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                    <i class="fas fa-industry me-2 bg-white bg-opacity-25 rounded-circle p-2"
                        style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                    Danh sách Cơ sở
                </h6>

                <form method="GET" action="{{ route('admin.shipyards.index') }}"
                    class="d-flex align-items-center flex-wrap gap-2">
                    {{-- Per Page --}}
                    <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                        <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                        <select name="per_page"
                            class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4"
                            style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div class="bg-white rounded-pill shadow-sm" style="flex: 1; min-width: 200px; max-width: 300px;">
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3"
                                style="z-index: 5;"></i>
                            <input type="text" name="search"
                                class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2"
                                placeholder="Tìm tên xưởng, chủ xưởng..." value="{{ request('search') }}"
                                onsearch="this.form.submit()">
                        </div>
                    </div>

                    {{-- Filter --}}
                    <div class="dropdown">
                        <button class="btn bg-white rounded-pill fw-bold text-dark px-3 py-2 shadow-sm" type="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1 text-secondary"></i>
                            @if (request('status'))
                                <span class="text-primary">Đã lọc</span>
                            @else
                                Lọc
                            @endif
                        </button>
                        <div class="dropdown-menu shadow-lg border-0 mt-2 p-3" style="width: 250px;">
                            <h6 class="dropdown-header px-0 text-uppercase fw-bold mb-2 small text-muted">Bộ lọc tìm kiếm
                            </h6>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Trạng thái</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm
                                        ngưng</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Áp dụng</button>
                            @if (request('status'))
                                <a href="{{ route('admin.shipyards.index') }}"
                                    class="btn btn-link btn-sm w-100 mt-1 text-decoration-none text-muted">Xóa bộ lọc</a>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="d-none">Tìm kiếm</button>

                    {{-- Add Button --}}
                    @can('create_shipyard')
                        <a href="{{ route('admin.shipyards.create') }}"
                            class="text-white fw-bold px-2 text-decoration-none d-flex align-items-center">
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
                            <th>ID</th>
                            <th class="ps-4">Tên cơ sở</th>
                            <th>Chủ cơ sở</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                            <th>Tài liệu</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipyards as $shipyard)
                            <tr>
                                <td>{{ $shipyard->id }}</td>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $shipyard->name }}</div>
                                    @if ($shipyard->license_number)
                                        <div class="small text-muted"><i class="fas fa-id-card me-1"></i>GPKD:
                                            {{ $shipyard->license_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $shipyard->owner_name }}</div>
                                    @if ($shipyard->owner_id_card)
                                        <div class="small text-muted">CCCD: {{ $shipyard->owner_id_card }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($shipyard->phone)
                                        <div><i class="fas fa-phone-alt text-muted small me-1"></i>{{ $shipyard->phone }}
                                        </div>
                                    @endif
                                    @if ($shipyard->address)
                                        <div class="small text-muted text-truncate" style="max-width: 200px;"
                                            title="{{ $shipyard->address }}"><i
                                                class="fas fa-map-marker-alt text-muted small me-1"></i>{{ $shipyard->address }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($shipyard->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"
                                            style="font-weight: 600; font-size: 0.75rem;">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"
                                            style="font-weight: 600; font-size: 0.75rem;">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    @php $fileCount = is_array($shipyard->files) ? count($shipyard->files) : 0; @endphp
                                    <span class="badge badge-tech text-primary bg-primary bg-opacity-10">
                                        <i class="fas fa-paperclip me-1"></i> {{ $fileCount }} file
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.shipyards.show', $shipyard->id) }}"
                                        class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;" title="Chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @can('update_shipyard')
                                        <a href="{{ route('admin.shipyards.edit', $shipyard->id) }}"
                                            class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete_shipyard')
                                        <form action="{{ route('admin.shipyards.destroy', $shipyard->id) }}" method="POST"
                                            class="d-inline-block" id="delete-form-{{ $shipyard->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;" title="Xóa"
                                                onclick="confirmDelete({{ $shipyard->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="bg-light rounded-circle p-4 mb-3">
                                            <i class="fas fa-industry fa-3x text-secondary opacity-50"></i>
                                        </div>
                                        <h6 class="text-muted fw-bold">Chưa có cơ sở đóng mới nào</h6>
                                        <p class="text-muted small mb-0">Thử thay đổi bộ lọc hoặc thêm cơ sở mới.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $shipyards->links() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Cơ sở này và các file đính kèm sẽ bị xóa và không thể hoàn tác!",
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
