@extends('layouts.admin')

@section('title', 'Cấu hình Quy trình Đăng kiểm')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Quy trình Đăng kiểm</h1>
        <p class="mb-0 text-muted small">Danh sách và thông tin chi tiết quy trình đăng kiểm</p>
    </div>
</div>

<div class="row" x-data="inspectionBuilder(initialProcesses)">
    <!-- Left Sidebar: Process List -->
    <div class="col-md-3 col-12 mb-4">
        <div class="tech-card h-100">
            <div class="tech-header justify-content-between">
                <span>Danh sách Quy trình</span>
                <span class="badge bg-white text-primary rounded-pill" x-text="processes.length"></span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <template x-for="process in processes" :key="process.id">
                        <button type="button" 
                                class="list-group-item list-group-item-action border-0 py-3"
                                :class="{ 'active-process': activeProcess && activeProcess.id === process.id }"
                                @click="selectProcess(process)">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold" :class="{ 'text-primary': activeProcess && activeProcess.id === process.id }" x-text="process.name"></h6>
                                <i class="fas fa-chevron-right small transition-transform" :class="{ 'text-primary transform-rotate-90': activeProcess && activeProcess.id === process.id, 'text-muted': !activeProcess || activeProcess.id !== process.id }"></i>
                            </div>
                            <small class="mb-1 text-muted" x-text="process.description || 'Chưa có mô tả'"></small>
                        </button>
                    </template>
                </div>
                
                <!-- Add New Process Button -->
                @can('create_inspection_process')
                <div class="p-3 border-top bg-light rounded-bottom">
                    <button class="btn btn-tech-outline w-100 border-dashed" data-bs-toggle="modal" data-bs-target="#createProcessModal">
                        <i class="fas fa-plus mr-2"></i> Thêm quy trình mới
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Right Main Area: Builder -->
    <div class="col-md-9 col-12">
        <!-- Empty State -->
        <!-- Empty State -->
        <div class="text-center py-5 tech-card d-flex flex-column align-items-center justify-content-center" 
             style="min-height: 400px;"
             x-show="!activeProcess"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             :class="{ 'd-flex': !activeProcess, 'd-none': activeProcess }">
            <div class="bg-light rounded-circle p-4 mb-3">
                <i class="fas fa-clipboard-list fa-4x text-gray-300"></i>
            </div>
            <h4 class="mt-2 text-gray-600 fw-bold">Chọn một quy trình để cấu hình</h4>
            <p class="text-muted">Hoặc tạo quy trình mới từ danh sách bên trái.</p>
        </div>

        <!-- Builder Interface -->
        <div x-show="activeProcess" style="display: none;">
            <!-- Header Card -->
            <div class="tech-card mb-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center p-4 gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-clipboard-check fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-weight-bold text-gray-800 mb-1" x-text="activeProcess?.name"></h4>
                                <p class="text-muted mb-0" x-text="activeProcess?.description || 'Chưa có mô tả'"></p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('update_inspection_process')
                        <button class="btn btn-tech-primary text-nowrap" @click="saveConfiguration" :disabled="isSaving">
                            <span x-show="isSaving" class="spinner-border spinner-border-sm mr-2"></span>
                            <i class="fas fa-save mr-2" x-show="!isSaving"></i>
                            <span x-text="isSaving ? 'Đang lưu...' : 'Lưu cấu hình'"></span>
                        </button>
                        @endcan
                        @can('delete_inspection_process')
                        <button class="btn btn-tech-danger text-nowrap" @click="deleteProcess" title="Xóa quy trình này">
                            <i class="fas fa-trash-alt me-1"></i> Xóa
                        </button>
                        @endcan
                    </div>
                    {{-- Form xóa quy trình (ẩn) --}}
                    <form id="form-delete-process" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 px-2 gap-2">
                <h5 class="text-gray-800 font-weight-bold border-start border-4 border-primary ps-3 mb-0">Các bước thực hiện (Steps)</h5>
                @can('update_inspection_process')
                <button class="btn btn-sm btn-tech-outline" @click="addStep">
                    <i class="fas fa-plus mr-1"></i> Thêm bước
                </button>
                @endcan
            </div>

            <!-- Steps List -->
            <div class="steps-container">
                <template x-for="(step, stepIndex) in steps" :key="stepIndex">
                    <div class="tech-card mb-3 step-card">
                        <div class="tech-header py-3 d-flex align-items-center justify-content-between bg-gradient-primary text-white">
                            <div class="d-flex align-items-center flex-grow-1">
                                <span class="badge bg-white text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                     style="width: 28px; height: 28px; min-width: 28px;"
                                     x-text="stepIndex + 1"></span>
                                <input type="text" class="form-control form-control-sm border-0 bg-transparent text-white fw-bold ps-0 placeholder-white-50" 
                                       style="font-size: 1.1rem; box-shadow: none;"
                                       x-model="step.title" placeholder="Nhập tên bước...">
                            </div>
                            @can('delete_inspection_process')
                            <button class="btn btn-link text-white-50 btn-sm hover-white" @click="removeStep(stepIndex)" title="Xóa bước">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            @endcan
                        </div>
                        <div class="card-body bg-light-50 rounded-3">
                            <!-- Items List -->
                            <div class="items-list p-2">
                                <template x-for="(item, itemIndex) in step.items" :key="itemIndex">
                                    <div class="mb-2 p-2 bg-white rounded-3 shadow-sm border border-light item-row flex-column">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center cursor-pointer" 
                                                 style="width: 30px;"
                                                 @click="item.is_required = !item.is_required" 
                                                 :title="item.is_required ? 'Bắt buộc' : 'Tùy chọn'">
                                                 <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                      :class="item.is_required ? 'bg-danger text-white' : 'bg-gray-200 text-gray-400'"
                                                      style="width: 20px; height: 20px;">
                                                     <i class="fas fa-exclamation" style="font-size: 0.6rem;" x-show="item.is_required"></i>
                                                     <i class="fas fa-minus" style="font-size: 0.6rem;" x-show="!item.is_required"></i>
                                                 </div>
                                            </div>

                                            {{-- Toggle Requires Approval --}}
                                            <div class="d-flex align-items-center justify-content-center cursor-pointer" 
                                                 style="width: 30px;"
                                                 @click="item.requires_approval = !item.requires_approval" 
                                                 :title="item.requires_approval ? 'Cần ký duyệt' : 'Không cần duyệt'">
                                                 <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                      :class="item.requires_approval ? 'bg-warning text-dark' : 'bg-gray-200 text-gray-400'"
                                                      style="width: 20px; height: 20px;">
                                                     <i class="fas fa-stamp" style="font-size: 0.55rem;"></i>
                                                 </div>
                                            </div>

                                            {{-- Toggle Require All Approvers (AND/OR) --}}
                                            <div x-show="item.requires_approval" class="d-flex align-items-center justify-content-center cursor-pointer ms-1 me-1" 
                                                 @click="item.require_all_approvers = !item.require_all_approvers" 
                                                 :title="item.require_all_approvers ? 'Yêu cầu TẤT CẢ phải ký duyệt (AND)' : 'BẤT KỲ ai ký duyệt cũng được (OR)'">
                                                 <span class="badge" :class="item.require_all_approvers ? 'bg-primary' : 'bg-info'" style="font-size: 0.65rem; width: 35px;" x-text="item.require_all_approvers ? 'AND' : 'OR'"></span>
                                            </div>
                                            
                                            <input type="text" class="form-control border-0 shadow-none py-1 bg-transparent flex-grow-1" 
                                                   x-model="item.content" placeholder="Nội dung kiểm tra...">

                                            <button class="btn btn-link text-danger btn-sm opacity-50 hover-opacity-100" @click="removeItem(stepIndex, itemIndex)" @cannot('update_inspection_process') disabled @endcannot>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                            <div x-show="item.requires_approval" class="mt-2">
                                                <select multiple class="form-control border-0 bg-light-50 shadow-sm" x-model="item.approvers" style="font-size: 0.85rem;" x-init="
                                                    $nextTick(() => {
                                                        new TomSelect($el, {
                                                            plugins: ['remove_button'],
                                                            options: allUsers.map(u => ({value: u.id, text: u.name})),
                                                            items: item.approvers,
                                                            placeholder: 'Chọn người duyệt...',
                                                            onChange: function(val) {
                                                                item.approvers = val ? (Array.isArray(val) ? val : [val]) : [];
                                                            }
                                                        });
                                                    });
                                                "></select>
                                            </div>
                                                                                        <!-- Formula input -->
                                            <div class="form-control border-0 bg-light-50 shadow-sm mt-2" style="font-size: 0.85rem;">
                                                <div class="input-group input-group-sm w-100">
                                                    <span class="input-group-text bg-light border-0"><i class="fas fa-calculator text-muted"></i></span>
                                                    <input type="text" class="form-control border-0 bg-light" x-model="item.formula" placeholder="Công thức tính toán (hỗ trợ IF, +, -, *, /) (Tùy chọn)">
                                                    <button class="btn btn-outline-secondary border-0 bg-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Chèn biến Tàu cá">
                                                        Biến Tàu cá
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 250px; overflow-y: auto; font-size: 0.85rem;">
                                                        <li><h6 class="dropdown-header">Thông số tàu & kích thước</h6></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'gross_tonnage')">Tổng đung tích (gross_tonnage)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'deadweight')">Trọng tải (deadweight)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'length_design')">C.dài thiết kế (length_design)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'width_design')">C.rộng thiết kế (width_design)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'length_max')">C.dài max (length_max)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'width_max')">C.rộng max (width_max)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'depth_max')">C.Sâu max (depth_max)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'draft')">Mớn nước (draft)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'crew_size')">Số thuyền viên (crew_size)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'build_year')">Năm đóng (build_year)</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><h6 class="dropdown-header">Thông số khác</h6></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'province_id')">Tỉnh/Thành phố (province_id)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'ward_id')">Quận/Huyện (ward_id)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'hull_material')">Vật liệu vỏ (hull_material)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'total_engine_hp')">Tổng HP máy chính (total_engine_hp)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'total_engine_kw')">Tổng KW máy chính (total_engine_kw)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'total_sub_engine_hp')">Tổng HP máy phụ (total_sub_engine_hp)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'total_sub_engine_kw')">Tổng KW máy phụ (total_sub_engine_kw)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'usage')">Công dụng (usage)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'main_occupation')">Nghề chính (main_occupation)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'secondary_occupation')">Nghề phụ (secondary_occupation)</a></li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="insertVariable(item, 'operation_area')">Vùng HĐ (operation_area)</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                    </div>
                                </template>
                                <div class="text-center">
                                    @can('update_inspection_process')
                                    <button class="btn btn-sm btn-light text-primary border-dashed w-100 py-2 fw-bold" 
                                            @click="addItem(stepIndex)">
                                        <i class="fas fa-plus me-1"></i> Thêm mục kiểm tra
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div x-show="steps.length === 0" class="text-center py-5 border rounded-3 border-dashed bg-white mt-3">
                <div class="text-gray-300 mb-3"><i class="fas fa-layer-group fa-3x"></i></div>
                <h6 class="text-gray-600 mb-1">Chưa có bước thực hiện nào</h6>
                <p class="text-muted small mb-3">Bắt đầu bằng cách thêm bước đầu tiên cho quy trình này.</p>
                <button class="btn btn-sm btn-tech-primary" @click="addStep">Thêm bước ngay</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Process Modal -->
<div class="modal fade" id="createProcessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.inspection-processes.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-gradient-primary text-white border-0 rounded-top-3">
                    <h5 class="modal-title fw-bold">Tạo Quy trình Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control" id="processName" required placeholder="Tên quy trình">
                        <label for="processName">Tên Quy trình</label>
                    </div>
                    <div class="form-floating">
                        <textarea name="description" class="form-control" id="processDesc" style="height: 100px" placeholder="Mô tả"></textarea>
                        <label for="processDesc">Mô tả (Tùy chọn)</label>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-tech-primary px-4">Tạo mới</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    const initialProcesses = @json($processes);
    const allUsers = @json($users ?? []);
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('inspectionBuilder', (initialProcesses) => ({
            processes: initialProcesses,
            allUsers: allUsers,
            activeProcess: initialProcesses.length > 0 ? initialProcesses[0] : null,
            steps: [],
            isSaving: false,

            init() {
                if (this.activeProcess) {
                    this.loadSteps();
                }
            },

            selectProcess(process) {
                this.activeProcess = process;
                this.loadSteps();
            },

            loadSteps() {
                // Parse steps from activeProcess relationships provided by Laravel
                // Deep copy to break reference and allow editing safely
                if (this.activeProcess && this.activeProcess.steps) {
                    this.steps = JSON.parse(JSON.stringify(this.activeProcess.steps));
                    
                    // Ensure items array exists for each step
                    this.steps.forEach(step => {
                        if (!step.items) step.items = [];
                        step.items.forEach(item => {
                            if (!item.approvers) item.approvers = [];
                            // Force boolean values so x-show works correctly
                            item.is_required = !!item.is_required;
                            item.requires_approval = !!item.requires_approval;
                            item.require_all_approvers = !!item.require_all_approvers;
                        });
                    });
                } else {
                    this.steps = [];
                }
            },

            addStep() {
                this.steps.push({
                    title: 'Bước mới',
                    items: [] // Empty items array
                });
                
                // Scroll to bottom
                this.$nextTick(() => {
                    window.scrollTo(0, document.body.scrollHeight);
                });
            },

            removeStep(index) {
                if (confirm('Bạn có chắc chắn muốn xóa bước này?')) {
                    this.steps.splice(index, 1);
                }
            },

            addItem(stepIndex, content = '') {
                this.steps[stepIndex].items.push({
                    content: content,
                    formula: '',
                    is_required: true,
                    requires_approval: false,
                    require_all_approvers: false,
                    approvers: [],
                    field_type: 'file'
                });
            },

            insertVariable(item, variable) {
                if (!item.formula) item.formula = '';
                item.formula += variable;
            },

            removeItem(stepIndex, itemIndex) {
                this.steps[stepIndex].items.splice(itemIndex, 1);
            },

            async saveConfiguration() {
                if (!this.activeProcess) return;

                this.isSaving = true;
                
                try {
                    const response = await fetch(`/admin/inspection-processes/${this.activeProcess.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            steps: this.steps
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Update the active process with new data from server
                        this.activeProcess.steps = data.process.steps;
                        this.loadSteps();
                        
                        // SweetAlert toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Đã lưu cấu hình thành công!'
                        });
                    } else {
                        throw new Error(data.message || 'Lỗi khi lưu');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Lỗi!', 'Không thể lưu cấu hình. Vui lòng thử lại.', 'error');
                } finally {
                    this.isSaving = false;
                }
            },

            deleteProcess() {
                if (!this.activeProcess) return;
                const processName = this.activeProcess.name;
                const processId  = this.activeProcess.id;

                Swal.fire({
                    title: `Xóa quy trình "${processName}"?`,
                    html: `<p class="text-muted mb-0">Toàn bộ các bước và mục kiểm tra trong quy trình này sẽ bị xóa vĩnh viễn.<br><strong class="text-danger">Hành động không thể hoàn tác!</strong></p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt me-1"></i>Xóa quy trình',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('form-delete-process');
                        form.action = `/admin/inspection-processes/${processId}`;
                        form.submit();
                    }
                });
            }
        }));
    });
</script>

<style>
    .border-dashed {
        border-style: dashed !important;
    }
    .step-card {
        transition: transform 0.2s;
    }
    .w-20 { width: 20px; text-align: center; }
    
    .bg-light-50 { background-color: #f8f9fa; }
    .hover-white:hover { color: white !important; }
    .hover-opacity-100:hover { opacity: 1 !important; transform: scale(1.1); }
    .cursor-pointer { cursor: pointer; }
    .active-process {
        background-color: #f0f7ff !important;
        border-left: 4px solid #3b82f6 !important;
    }
    .placeholder-white-50::placeholder { color: rgba(255,255,255,0.7); }
    .item-row { transition: all 0.2s; }
    .item-row:hover { transform: translateX(5px); border-color: #e3e6f0 !important; }
    .radius-12 { border-radius: 12px; }
    
    /* Custom Scrollbar for list */
    .list-group {
        max-height: calc(100vh - 280px);
        overflow-y: auto;
    }
    .list-group::-webkit-scrollbar { width: 4px; }
    .list-group::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    /* Tom-select z-index fix */
    .item-row { position: relative; z-index: 1; }
    .item-row:hover, .item-row:focus-within { z-index: 10; }
    .ts-wrapper.multi .ts-control > div {
        cursor: pointer;
        background: #e2e8f0;
        color: #1e293b;
        border-radius: 4px;
        padding: 0 4px;
    }
</style>
@endsection
