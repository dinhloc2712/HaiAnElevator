@extends('layouts.admin')

@section('title', 'Thực hiện Đăng kiểm')

@section('content')
<div x-data="inspectionWizard()">
{{-- Header --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">
            Đăng kiểm: {{ $inspection->code }}
        </h1>
        <p class="mb-0 text-muted small">
            <i class="fas fa-ship me-1"></i> {{ $inspection->ship->name }} ({{ $inspection->ship->registration_number }}) 
            <span class="mx-2">|</span> 
            <i class="fas fa-clipboard-list me-1"></i> {{ $inspection->process->name }}
        </p>
    </div>
    <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center justify-content-end gap-2">
        @if($inspection->status !== 'completed')
        <button type="button" class="btn rounded-pill px-4 py-2 shadow-sm fw-bold text-nowrap order-2 order-sm-1" 
                :class="hasAnyFailedStep() ? 'btn-danger' : 'btn-success'" 
                data-bs-toggle="modal" data-bs-target="#completionModal">
            <template x-if="hasAnyFailedStep()">
                <span><i class="fas fa-times-circle me-1"></i> Hoàn thành (Không đạt)</span>
            </template>
            <template x-if="!hasAnyFailedStep()">
                <span><i class="fas fa-check-circle me-1"></i> Hoàn thành (Đạt)</span>
            </template>
        </button>
        @else
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 order-2 order-sm-1">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25 shadow-sm rounded-pill text-nowrap w-100">
                    <i class="fas fa-check-circle me-1"></i> Đã nộp: {{ number_format($inspection->fee_amount, 0, ',', '.') }} đ
                </span>
            </div>
        @endif
        
        <a href="{{ route('admin.inspections.index') }}" class="btn btn-light rounded-pill px-3 shadow-sm border fw-bold text-nowrap w-100 order-1 order-sm-2" style="flex: 1;">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    {{-- Left Sidebar: Progress & Info --}}
    <div class="col-lg-3 mb-4">
        {{-- Progress Card --}}
        <div class="tech-card mb-3">
            <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                    <i class="fas fa-tasks me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"></i>
                    Tiến độ
                </h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($inspection->process->steps as $index => $step)
                <div class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom-0"
                     style="transition: all 0.2s;"
                     :class="{
                        'bg-primary bg-opacity-10 border-start border-4 border-primary': currentStep === {{ $index }},
                        'text-muted opacity-75': currentStep < {{ $index }},
                        'bg-opacity-5': isStepCompleted({{ $index }})
                     }">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm"
                             style="width: 36px; height: 36px; font-size: 0.85rem; transition: all 0.3s;"
                             :class="{
                                'bg-primary text-white': currentStep === {{ $index }},
                                'bg-success text-white': isStepCompleted({{ $index }}),
                                'bg-light text-secondary': currentStep !== {{ $index }} && !isStepCompleted({{ $index }})
                             }">
                            <template x-if="isStepCompleted({{ $index }})">
                                <i class="fas fa-check"></i>
                            </template>
                            <template x-if="!isStepCompleted({{ $index }})">
                                <span>{{ $index + 1 }}</span>
                            </template>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold" style="font-size: 0.9rem;">{{ $step->title }}</span>
                            <span class="small text-muted" x-text="getStepStatusText({{ $index }})"></span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info Card --}}
        <div class="tech-card">
            <div class="tech-header" style="background: linear-gradient(135deg, #858796 0%, #60616f 100%); padding: 20px 25px;">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                    <i class="fas fa-info-circle me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"></i>
                    Thông tin
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="d-block small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Ngày kiểm tra</span>
                    <span class="fw-bold text-dark">{{ $inspection->inspection_date->format('d/m/Y') }}</span>
                </div>
                <div class="mb-3">
                    <span class="d-block small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Đăng kiểm viên</span>
                    <span class="fw-bold text-dark">{{ $inspection->inspector->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="d-block small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Trạng thái</span>
                    @if($inspection->status == 'draft')
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1">Nháp</span>
                    @elseif($inspection->status == 'in_progress')
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1">Đang thực hiện</span>
                    @elseif($inspection->status == 'completed')
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-1">Hoàn thành</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Wizard Content --}}
    <div class="col-lg-9">
        @foreach($inspection->process->steps as $index => $step)
        <div x-show="currentStep === {{ $index }}" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0">
            
            <div class="tech-card mb-4">
                <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-clipboard-check me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                            Bước {{ $index + 1 }}: {{ $step->title }}
                        </h5>
                        <span class="badge bg-white text-success rounded-pill px-3 py-2 fw-bold">
                            <i class="fas fa-list-ul me-1"></i> {{ count($step->items) }} hạng mục
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 35%">Hạng mục kiểm tra</th>
                                    <th class="text-center" style="width: 10%">Yêu cầu</th>
                                    <th class="text-center" style="width: 25%">Đánh giá</th>
                                    <th class="pe-4" style="width: 30%">Chi tiết (Ảnh/Ghi chú)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($step->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $item->content }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->is_required)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;">Bắt buộc</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1" style="font-size: 0.75rem;">Tùy chọn</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="min-width: 230px;">
                                        @if($item->requires_approval)
                                        {{-- This item requires approval before marking pass/fail --}}
                                        <div x-data="{ itemId: {{ $item->id }}, show: true }">
                                            {{-- Waiting for approval or not yet requested --}}
                                            <template x-if="getProposalStatus({{ $item->id }}) === null">
                                                <button type="button" class="btn btn-sm btn-warning fw-bold w-100 py-2 rounded-pill shadow-sm"
                                                        @click="requestApproval({{ $item->id }})">
                                                    <i class="fas fa-stamp me-1"></i> Yêu cầu Giám đốc Duyệt
                                                </button>
                                            </template>
                                            <template x-if="getProposalStatus({{ $item->id }}) === 'pending'">
                                                <span class="badge bg-warning text-dark py-2 px-3 rounded-pill fw-bold">
                                                    <i class="fas fa-clock me-1"></i> Đang chờ Giám đốc Duyệt
                                                </span>
                                            </template>
                                            <template x-if="getProposalStatus({{ $item->id }}) === 'rejected'">
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-danger py-2 px-3 rounded-pill fw-bold">
                                                        <i class="fas fa-times-circle me-1"></i> Bị Từ chối
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill"
                                                            @click="requestApproval({{ $item->id }})">
                                                        <i class="fas fa-redo me-1"></i> Gửi lại
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="getProposalStatus({{ $item->id }}) === 'approved'">
                                                <span class="badge w-100 bg-success py-2 px-3 rounded-pill fw-bold d-flex flex-column gap-1 shadow-sm">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-check-double me-1"></i> Đã Ký Duyệt (Đạt)
                                                    </div>
                                                </span>
                                            </template>
                                        </div>
                                        @else
                                        {{-- Normal item - no approval needed --}}
                                        <div class="btn-group w-100 shadow-sm" role="group" style="border-radius: 50px; overflow: hidden;">
                                            <button type="button" class="btn btn-sm fw-bold py-2" 
                                                    :class="getItemStatus({{ $item->id }}) === 'pass' ? 'btn-success' : 'btn-light text-secondary border-end'"
                                                    @click="updateStatus({{ $item->id }}, 'pass')">
                                                <i class="fas fa-check me-1"></i> Đạt
                                            </button>
                                            <button type="button" class="btn btn-sm fw-bold py-2" 
                                                    :class="getItemStatus({{ $item->id }}) === 'fail' ? 'btn-danger' : 'btn-light text-secondary border-end'"
                                                    @click="updateStatus({{ $item->id }}, 'fail')">
                                                <i class="fas fa-times me-1"></i> Hỏng
                                            </button>
                                            <button type="button" class="btn btn-sm fw-bold py-2" 
                                                    :class="getItemStatus({{ $item->id }}) === 'skipped' ? 'btn-secondary' : 'btn-light text-secondary'"
                                                    @click="updateStatus({{ $item->id }}, 'skipped')">
                                                Bỏ qua
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex flex-column gap-2">
                                            {{-- Note Input --}}
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-pen text-muted small"></i></span>
                                                <input type="text" class="form-control bg-light border-start-0" 
                                                       placeholder="Ghi chú..." 
                                                       :value="getItemNote({{ $item->id }})"
                                                       @change="updateNote({{ $item->id }}, $event.target.value)">
                                            </div>

                                            {{-- File Upload --}}
                                            <div class="d-flex flex-column gap-1">
                                                <label class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 mb-0" style="font-size: 0.75rem; cursor: pointer;">
                                                    <i class="fas fa-camera me-1"></i> Tải lên Media
                                                    <input type="file" class="d-none" multiple @change="uploadEvidence({{ $item->id }}, $event.target.files); $event.target.value = ''">
                                                </label>

                                                {{-- All Evidence files list --}}
                                                <template x-if="getItemEvidences({{ $item->id }}).length > 0">
                                                    <div class="d-flex flex-column gap-1 mt-1">
                                                        <template x-for="(ev, i) in getItemEvidences({{ $item->id }})" :key="ev.filename">
                                                            <a :href="ev.url" target="_blank" class="badge bg-info bg-opacity-10 text-info text-decoration-none rounded-pill px-2 py-1 d-flex align-items-center gap-1" style="font-size:0.7rem;">
                                                                <i class="fas fa-file me-1"></i>
                                                                <span class="text-truncate" style="max-width:120px;" x-text="ev.filename"></span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm" 
                        @click="currentStep--" 
                        x-show="currentStep > 0">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </button>
                
                <div class="ms-auto">
                    <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" 
                            @click="validateAndNext({{ $index }})" 
                            x-show="currentStep < {{ count($inspection->process->steps) - 1 }}"
                            :disabled="!canProceed({{ json_encode($step->items->filter(fn($i) => $i->is_required)->pluck('id')) }})">
                        Bước tiếp theo <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
            
            {{-- Validation Message --}}
            <div class="alert alert-warning mt-3 rounded-3 shadow-sm border-0 d-flex align-items-center" 
                 x-show="!canProceed({{ json_encode($step->items->filter(fn($i) => $i->is_required)->pluck('id')) }})"
                 x-transition>
                <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                <span class="fw-bold small">Vui lòng hoàn thành đánh giá (Đạt/Hỏng/Bỏ qua) cho tất cả các hạng mục bắt buộc trước khi tiếp tục.</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Completion Modal (ProTechUi) --}}
<div class="modal fade" id="completionModal" tabindex="-1" aria-labelledby="completionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tech-card overflow-hidden">
            <div class="modal-header" 
                 :style="hasAnyFailedStep() ? 'background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); padding: 20px 25px;' : 'background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); padding: 20px 25px;'">
                <h5 class="modal-title fw-bold m-0 text-white" id="completionModalLabel" style="display: flex; align-items: center;">
                    <template x-if="hasAnyFailedStep()">
                        <span><i class="fas fa-exclamation-triangle me-2"></i> Xác nhận hoàn thành đăng kiểm (Không Đạt)</span>
                    </template>
                    <template x-if="!hasAnyFailedStep()">
                        <span><i class="fas fa-check-circle me-2"></i> Xác nhận hoàn thành đăng kiểm (Đạt)</span>
                    </template>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.inspections.update', $inspection) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="result" :value="hasAnyFailedStep() ? 'fail' : 'pass'">
                
                <div class="modal-body p-4 bg-white">
                    <div class="alert bg-primary bg-opacity-10 border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
                        <div>
                            <p class="mb-0 fw-bold text-dark">Thông tin Tàu: <span class="text-primary">{{ $inspection->ship->registration_number }}</span></p>
                            <p class="mb-0 fs-7 text-muted">Các thông tin Chứng nhận & ATKT dưới đây được lấy từ dữ liệu gốc của Tàu. Mọi thay đổi trên biểu mẫu này sẽ tự động cập nhật lại cho Tàu.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 position-relative">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-money-check-alt me-2 text-primary"></i> Lệ phí & Gia hạn</h6>
                            <div class="form-floating mb-3">
                                <input type="number" name="fee_amount" id="fee_amount" class="form-control fw-bold text-success border-2" value="{{ old('fee_amount', $inspection->fee_amount > 0 ? (int)$inspection->fee_amount : '') }}" min="0" placeholder="0">
                                <label for="fee_amount" class="text-muted"><i class="fas fa-file-invoice-dollar me-1"></i> Số tiền phí (VNĐ)</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="date" name="new_expiration_date" id="new_expiration_date" class="form-control fw-bold border-2" value="{{ old('new_expiration_date', $inspection->ship->expiration_date ? $inspection->ship->expiration_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 year'))) }}">
                                <label for="new_expiration_date" class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Hạn đăng kiểm mới</label>
                            </div>
                        </div>

                        <div class="col-md-6 border-start ps-md-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt me-2 text-success"></i> An Toàn Kỹ Thuật (ATKT)</h6>
                            <div class="form-floating mb-3">
                                <input type="text" name="technical_safety_number" id="technical_safety_number" class="form-control border-2" value="{{ old('technical_safety_number', $inspection->ship->technical_safety_number) }}" placeholder="VD: ATKT-001">
                                <label for="technical_safety_number" class="text-muted"><i class="fas fa-certificate me-1"></i> Số ATKT</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="date" name="technical_safety_date" id="technical_safety_date" class="form-control border-2" value="{{ old('technical_safety_date', $inspection->ship->technical_safety_date ? $inspection->ship->technical_safety_date->format('Y-m-d') : '') }}">
                                <label for="technical_safety_date" class="text-muted"><i class="fas fa-calendar-day me-1"></i> Ngày cấp ATKT</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="record_number" id="record_number" class="form-control border-2" value="{{ old('record_number', $inspection->ship->record_number) }}" placeholder="VD: BB-102">
                                <label for="record_number" class="text-muted"><i class="fas fa-file-signature me-1"></i> Số Biên bản (BB)</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="date" name="record_date" id="record_date" class="form-control border-2" value="{{ old('record_date', $inspection->ship->record_date ? $inspection->ship->record_date->format('Y-m-d') : '') }}">
                                <label for="record_date" class="text-muted"><i class="fas fa-calendar-day me-1"></i> Ngày cấp Biên bản</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3 px-4 d-flex justify-content-between" style="border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-tech-outline" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-tech-primary border-0" :class="hasAnyFailedStep() ? 'bg-danger' : 'bg-success'">
                        Xác nhận Hoàn thành <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div> {{-- /End of x-data=inspectionWizard() --}}

@section('scripts')
<script>
    // Define global function for Alpine x-data="inspectionWizard()"
    function inspectionWizard() {
        return {
            currentStep: 0,
            details: @json($detailsMap), // Keyed by item_id
            
            init() {
                console.log('Inspection Wizard Initialized', this.details);
            },

            // Helpers
            hasAnyFailedStep() {
                return Object.values(this.details).some(detail => detail.status === 'fail');
            },
            getItemStatus(itemId) {
                return this.details[itemId] ? this.details[itemId].status : null;
            },
            getItemNote(itemId) {
                return this.details[itemId] ? this.details[itemId].note : '';
            },
            getItemEvidence(itemId) {
                // Returns first url for backwards compat
                const evs = this.getItemEvidences(itemId);
                return evs.length > 0 ? evs[0].url : null;
            },
            getItemEvidences(itemId) {
                // Returns array of {filename, url}
                if (!this.details[itemId]) return [];
                const urls = this.details[itemId].evidence_urls;
                if (Array.isArray(urls) && urls.length > 0) return urls;
                // Fallback: if loaded from server with single evidence_url
                const single = this.details[itemId].evidence_url;
                const files = this.details[itemId].evidence_files;
                if (Array.isArray(files) && files.length > 0) {
                    return files.map(f => ({ filename: f, url: single || '#' }));
                }
                if (single) return [{ filename: 'Tệp đính kèm', url: single }];
                return [];
            },
            getProposalStatus(itemId) {
                return this.details[itemId] ? this.details[itemId].proposal_status : null;
            },

            async requestApproval(itemId) {
                try {
                    const response = await fetch(`{{ route('admin.inspections.request-approval', $inspection) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ item_id: itemId })
                    });
                    const data = await response.json();

                    if (!this.details[itemId]) this.details[itemId] = {};
                    this.details[itemId].proposal_status = data.proposal_status;
                    this.details[itemId].proposal_id_val = data.proposal_id;

                    const icon = data.success ? 'success' : 'info';
                    Swal.fire({ toast: true, position: 'top-end', icon, title: data.message, showConfirmButton: false, timer: 3500 });
                } catch (e) {
                    Swal.fire('Lỗi', 'Không thể gửi yêu cầu duyệt.', 'error');
                }
            },
            
            // Logic
            isStepCompleted(stepIndex) {
                return this.currentStep > stepIndex;
            },
            
            getStepStatusText(stepIndex) {
                if (this.currentStep === stepIndex) return 'Đang thực hiện';
                if (this.currentStep > stepIndex) return 'Hoàn thành';
                return 'Chưa bắt đầu';
            },

            // Validation: Check if all required items have a status
            canProceed(requiredItemIds) {
                if (!requiredItemIds || requiredItemIds.length === 0) return true;
                return requiredItemIds.every(id => {
                    const detail = this.details[id];
                    const status = detail ? String(detail.status) : '';
                    const proposalStatus = detail ? detail.proposal_status : null;
                    return ['pass', 'fail', 'skipped'].includes(status) || proposalStatus === 'approved';
                });
            },

            validateAndNext(currentIndex) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            // Actions
            updateStatus(itemId, status) {
                // 1. Optimistic Update
                if (!this.details[itemId]) {
                    this.details[itemId] = { status: status, note: '', evidence_url: null };
                } else {
                    this.details[itemId].status = status;
                }
                
                // Force reactivity
                this.details = JSON.parse(JSON.stringify(this.details));

                // 2. Server Update
                this.saveDetail(itemId, { status: status });
            },

            updateNote(itemId, note) {
                if (!this.details[itemId]) this.details[itemId] = {};
                this.details[itemId].note = note;
                this.saveDetail(itemId, { note: note });
            },

            uploadEvidence(itemId, files) {
                if (!files || files.length === 0) return;
                
                let formData = new FormData();
                formData.append('item_id', itemId);
                for (let i = 0; i < files.length; i++) {
                    formData.append('evidence_files[]', files[i]);
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) { alert('Lỗi: Không tìm thấy CSRF Token!'); return; }

                // Show loading
                Swal.fire({ title: 'Đang tải lên...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`{{ route('admin.inspections.update-status', $inspection->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken.getAttribute('content') },
                    body: formData
                })
                .then(r => {
                    if (!r.ok) throw new Error('Network response was not ok');
                    return r.json();
                })
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        if (!this.details[itemId]) this.details[itemId] = {};
                        this.details[itemId].evidence_urls = data.evidence_urls;
                        this.details[itemId].evidence_url = data.evidence_url;
                        this.details[itemId].evidence_files = data.detail.evidence_files;
                        
                        // Force reactivity
                        this.details = JSON.parse(JSON.stringify(this.details));
                        
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Đã tải lên ${data.evidence_urls.length} file thành công!`, showConfirmButton: false, timer: 2500 });
                    } else { 
                        Swal.fire('Lỗi', data.message || 'Unknown error', 'error');
                    }
                })
                .catch(e => {
                    Swal.close();
                    console.error('Upload Error:', e);
                    Swal.fire('Lỗi', 'Có lỗi xảy ra khi tải lên.', 'error');
                });
            },

            saveDetail(itemId, data) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                fetch(`{{ route('admin.inspections.update-status', $inspection->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: JSON.stringify({ item_id: itemId, ...data })
                })
                .then(r => r.json())
                .then(d => console.log('Save response:', d))
                .catch(e => console.error('Save Error:', e));
            }
        };
    }
</script>
@endsection
@endsection
