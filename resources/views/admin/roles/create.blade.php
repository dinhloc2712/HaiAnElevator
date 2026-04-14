@extends('layouts.admin')

@section('title', 'Thêm chức vụ mới')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Thêm chức vụ mới</h1>
        <p class="mb-0 text-muted small">Tạo chức vụ và phân quyền</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-add" style="background: #6c757d;">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    <div class="row">
        {{-- Left Column: Basic Info --}}
        <div class="col-lg-4">
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 16px 20px;">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin chức vụ
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Tên hiển thị <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" class="form-control modern-form-control" value="{{ old('display_name') }}" required placeholder="Ví dụ: Quản lý kho">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Mã chức vụ (Name) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control modern-form-control" value="{{ old('name') }}" required placeholder="Ví dụ: inventory_manager">
                        <div class="form-text small">Mã chức vụ viết liền không dấu (tiếng Anh)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Permissions --}}
        <div class="col-lg-8">
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); padding: 16px 20px;">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                        <i class="fas fa-lock me-2"></i>
                        Phân quyền theo Module
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 220px;" class="ps-4">Module</th>
                                    <th class="text-center" style="width: 80px;">Xem</th>
                                    <th class="text-center" style="width: 80px;">Thêm</th>
                                    <th class="text-center" style="width: 80px;">Sửa</th>
                                    <th class="text-center" style="width: 80px;">Xóa</th>
                                    <th class="text-center" style="width: 80px;">Duyệt</th>
                                    <th class="text-center pe-4" style="width: 100px;">Chọn tất cả</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $modules = [
                                        'dashboard' => 'Tổng quan',
                                        'user'      => 'Tài khoản',
                                        'role'      => 'Phân quyền',
                                        'branch'    => 'Chi nhánh',
                                        'building'  => 'Tòa nhà',
                                        'elevator'  => 'Thang máy',
                                        'news'      => 'Tin tức & Thông báo',
                                    ];
                                    $actions = ['view', 'create', 'update', 'delete', 'approve'];
                                @endphp

                                @foreach($modules as $moduleKey => $moduleName)
                                <tr class="module-row" data-module="{{ $moduleKey }}">
                                    <td class="fw-bold ps-4">{{ $moduleName }}</td>
                                    @foreach($actions as $action)
                                        @php
                                            $permissionName = "{$action}_{$moduleKey}";
                                            $permission = \App\Models\Permission::where('name', $permissionName)->first();
                                        @endphp
                                        <td class="text-center {{ $permission ? 'cursor-pointer' : '' }}" onclick="{{ $permission ? 'toggleCheckbox(event, \'perm_' . $permission->id . '\')' : '' }}" style="{{ $permission ? 'cursor: pointer;' : '' }}">
                                            @if($permission)
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input module-permission" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" data-module="{{ $moduleKey }}">
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 select-all-module" data-module="{{ $moduleKey }}">
                                            <i class="fas fa-check-double me-1" style="font-size: 0.7rem;"></i> Tất cả
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-tech-outline">Hủy bỏ</a>
                <button type="submit" class="btn btn-tech-primary">
                    <i class="fas fa-save me-1"></i> Lưu chức vụ
                </button>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
    // Toggle checkbox when clicking anywhere in the cell
    function toggleCheckbox(event, checkboxId) {
        // Prevent double-toggle if clicking directly on checkbox
        if (event.target.type === 'checkbox') return;
        
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
        }
    }

    // Select all permissions for a specific module
    document.querySelectorAll('.select-all-module').forEach(button => {
        button.addEventListener('click', function() {
            const module = this.dataset.module;
            const checkboxes = document.querySelectorAll(`input.module-permission[data-module="${module}"]`);
            
            // Check if all are already checked
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            // Toggle: if all checked, uncheck all; otherwise check all
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            // Update button text and icon
            const icon = this.querySelector('i');
            const text = this.querySelector('i').nextSibling;
            if (!allChecked) {
                icon.className = 'fas fa-times me-1';
                text.textContent = ' Bỏ chọn';
            } else {
                icon.className = 'fas fa-check-double me-1';
                text.textContent = ' Tất cả';
            }
        });
    });
</script>
@endsection
@endsection
