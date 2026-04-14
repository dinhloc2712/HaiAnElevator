@extends('layouts.admin')

@section('title', 'Cài đặt hệ thống bảo trì')

@section('styles')
<style>
    .settings-header-title {
        font-weight: 800;
        color: #111827;
        font-size: 1.75rem;
        letter-spacing: -0.5px;
    }
    
    .settings-card {
        background: #fff;
        border: 1px solid #f1f3f9;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .nav-tabs-settings {
        border-bottom: 2px solid #f3f4f6;
    }

    .nav-tabs-settings .nav-link {
        border: none;
        color: #6b7280;
        font-weight: 700;
        padding: 15px 25px;
        position: relative;
        transition: all 0.2s;
    }

    .nav-tabs-settings .nav-link:hover {
        color: #3b82f6;
    }

    .nav-tabs-settings .nav-link.active {
        color: #2563eb;
        background: transparent;
    }

    .nav-tabs-settings .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #2563eb;
        border-radius: 3px;
    }

    .group-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .group-header {
        background: #f9fafb;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .group-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-size: 1.25rem;
    }

    .item-list {
        padding: 10px 20px;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid #f3f4f6;
        border-radius: 10px;
        background: #fff;
        margin-bottom: 8px;
        transition: all 0.2s;
    }

    .item-row:hover {
        border-color: #dbeafe;
        background: #f8fafc;
    }

    .item-dot {
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
        margin-right: 12px;
        opacity: 0.6;
    }

    .btn-add-item {
        width: 100%;
        padding: 12px;
        border: 1px dashed #3b82f6;
        background: #f0f7ff;
        color: #2563eb;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
        text-align: left;
    }

    .btn-add-item:hover {
        background: #e0efff;
        border-style: solid;
    }

    /* Action Buttons Simple */
    .btn-icon-sm {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        background: transparent;
        border: none;
        transition: all 0.2s;
    }

    .btn-icon-sm:hover {
        background: #f1f5f9;
        color: #4b5563;
    }

    .btn-icon-sm-danger:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .btn-icon-sm-primary:hover {
        background: #dbeafe;
        color: #3b82f6;
    }
</style>
@endsection

@section('content')
<div class="mb-5">
    <h1 class="settings-header-title mb-1">Cài đặt hệ thống</h1>
    <p class="text-muted">Cấu hình các hạng mục bảo trì và trạng thái kiểm tra.</p>
</div>

<ul class="nav nav-tabs nav-tabs-settings mb-4" id="settingTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-pane" type="button" role="tab">Hạng mục bảo trì</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status-pane" type="button" role="tab">Trạng thái kiểm tra</button>
    </li>
</ul>

<div class="tab-content" id="settingTabsContent">
    <!-- TAB 1: HẠNG MỤC BẢO TRÌ -->
    <div class="tab-pane fade show active" id="items-pane" role="tabpanel">
        <div class="settings-card p-4 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-list-check text-primary me-2"></i> Cấu trúc hạng mục bảo trì</h5>
                <p class="small text-muted mb-0">Thiết lập các nhóm và hạng mục kiểm tra định kỳ cho kỹ thuật viên.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                <i class="fas fa-plus me-2"></i> Thêm nhóm mới
            </button>
        </div>

        <div class="row">
            @foreach($categories as $category)
            <div class="col-lg-12">
                <div class="group-card">
                    <div class="group-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="group-icon"><i class="fas fa-list-ul"></i></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 text-uppercase">{{ $category->name }}</h6>
                                <span class="small text-primary fw-bold">{{ $category->items->count() }} HẠNG MỤC CHI TIẾT</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn-icon-sm btn-icon-sm-primary" onclick="editCategory({{ $category->id }}, '{{ $category->name }}')">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <form action="{{ route('admin.maintenance.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Xóa nhóm này sẽ xóa toàn bộ hạng mục bên trong. Tiếp tục?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-sm btn-icon-sm-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="item-list p-4">
                        <div class="row g-3">
                            @foreach($category->items as $item)
                            <div class="col-md-6">
                                <div class="item-row">
                                    <div class="d-flex align-items-center">
                                        <div class="item-dot"></div>
                                        <span class="fw-medium text-dark">{{ $item->name }}</span>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button class="btn-icon-sm btn-icon-sm-primary" onclick="editItem({{ $item->id }}, '{{ $item->name }}')">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <form action="{{ route('admin.maintenance.items.destroy', $item->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon-sm btn-icon-sm-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-6">
                                <button class="btn-add-item" onclick="prepareAddItem({{ $category->id }}, '{{ $category->name }}')">
                                    <i class="fas fa-plus me-2"></i> Thêm hạng mục mới vào {{ $category->name }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 2: TRẠNG THÁI KIỂM TRA -->
    <div class="tab-pane fade" id="status-pane" role="tabpanel">
        <div class="settings-card p-4">
             <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1"><i class="fas fa-check-double text-success me-2"></i> Trạng thái kiểm tra</h5>
                    <p class="small text-muted mb-0">Quản lý các trạng thái lựa chọn khi kỹ thuật viên thực hiện kiểm tra.</p>
                </div>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAddStatus">
                    <i class="fas fa-plus me-2"></i> Thêm trạng thái
                </button>
            </div>

            <div class="row g-3">
                @foreach($statuses as $status)
                <div class="col-md-4">
                    <div class="item-row border-success bg-light" style="border-left-width: 4px;">
                        <span class="fw-bold text-dark">{{ $status->name }}</span>
                        <div class="d-flex gap-1">
                            <button class="btn-icon-sm btn-icon-sm-primary" onclick="editStatus({{ $status->id }}, '{{ $status->name }}')">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <form action="{{ route('admin.maintenance.statuses.destroy', $status->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-sm btn-icon-sm-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal Add Category -->
<div class="modal fade" id="modalAddCategory" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('admin.maintenance.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Thêm nhóm hạng mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tên nhóm hạng mục</label>
                    <input type="text" name="name" class="form-control p-3 rounded-3" placeholder="VD: HỆ THỐNG ĐỘNG CƠ..." required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Item -->
<div class="modal fade" id="modalAddItem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('admin.maintenance.items.store') }}" method="POST">
                @csrf
                <input type="hidden" name="category_id" id="add_item_category_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Thêm hạng mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted">Nhóm: <span id="add_item_category_name" class="fw-bold text-dark"></span></p>
                    <label class="form-label small fw-bold text-muted text-uppercase">Tên hạng mục kiểm tra</label>
                    <input type="text" name="name" class="form-control p-3 rounded-3" placeholder="VD: Kiểm tra dầu mỡ..." required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Status -->
<div class="modal fade" id="modalAddStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('admin.maintenance.statuses.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Thêm trạng thái mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tên trạng thái</label>
                    <input type="text" name="name" class="form-control p-3 rounded-3" placeholder="VD: Đã hoàn thành..." required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals (Using JS to avoid too many HTML elements) -->
<div class="modal fade" id="modalEditGeneral" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form id="editGeneralForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editGeneralTitle">Cập nhật</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tên chính xác</label>
                    <input type="text" name="name" id="editGeneralName" class="form-control p-3 rounded-3" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function prepareAddItem(catId, catName) {
        document.getElementById('add_item_category_id').value = catId;
        document.getElementById('add_item_category_name').innerText = catName;
        new bootstrap.Modal(document.getElementById('modalAddItem')).show();
    }

    function editCategory(id, name) {
        document.getElementById('editGeneralTitle').innerText = 'Chỉnh sửa nhóm hạng mục';
        document.getElementById('editGeneralName').value = name;
        document.getElementById('editGeneralForm').action = `{{ url('admin/maintenance-settings/categories') }}/${id}`;
        new bootstrap.Modal(document.getElementById('modalEditGeneral')).show();
    }

    function editItem(id, name) {
        document.getElementById('editGeneralTitle').innerText = 'Chỉnh sửa hạng mục';
        document.getElementById('editGeneralName').value = name;
        document.getElementById('editGeneralForm').action = `{{ url('admin/maintenance-settings/items') }}/${id}`;
        new bootstrap.Modal(document.getElementById('modalEditGeneral')).show();
    }

    function editStatus(id, name) {
        document.getElementById('editGeneralTitle').innerText = 'Chỉnh sửa trạng thái';
        document.getElementById('editGeneralName').value = name;
        document.getElementById('editGeneralForm').action = `{{ url('admin/maintenance-settings/statuses') }}/${id}`;
        new bootstrap.Modal(document.getElementById('modalEditGeneral')).show();
    }
</script>
@endsection
