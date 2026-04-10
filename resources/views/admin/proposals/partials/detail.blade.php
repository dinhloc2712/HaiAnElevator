    {{-- Right Column: Details --}}
    <div class="col-lg-8 col-12 mb-4 h-100">
        <template x-if="!selected">
            <div class="tech-card h-100 d-flex flex-column align-items-center justify-content-center text-muted mb-0"
                style="min-height: 600px;">
                <i class="fas fa-file-invoice fa-4x mb-3 text-gray-200"></i>
                <h5 class="fw-bold opacity-50">Chọn một đề xuất để xem chi tiết</h5>
                <p class="small">Vui lòng chọn đề xuất từ danh sách bên trái để xem nội dung tờ trình và phê duyệt.</p>
            </div>
        </template>

        <template x-if="selected">
            <div class="tech-card h-100 d-flex flex-column mb-0" style="min-height: 600px;">
                <div class="tech-header"
                    style="background: linear-gradient(135deg, #1cc88a 0%, #39914fff 100%); padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-file-signature me-2 bg-white bg-opacity-25 rounded-circle p-2"
                                style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                            Chi tiết Đề xuất
                        </h5>
                    </div>
                </div>

                <div class="d-flex flex-column h-100 bg-white">
                    <div class="p-4 flex-grow-1 overflow-auto">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
                            <div>
                                <h3 class="fw-bold text-dark mb-2" x-text="selected.title"></h3>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1"><i
                                            class="fas fa-tag me-1"></i> <span x-text="selected.category"></span></span>
                                    <span x-show="selected.pre_vat_amount"
                                        class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-2 py-1"><i
                                            class="fas fa-file-invoice-dollar me-1"></i> Trước thuế: <span
                                            x-text="formatCurrency(selected.pre_vat_amount)"></span></span>
                                    <span x-show="selected.vat"
                                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2 py-1"><i
                                            class="fas fa-percent me-1"></i> VAT: <span
                                            x-text="selected.vat + '%'"></span></span>
                                    <span x-show="selected.paid_amount"
                                        class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1"><i
                                            class="fas fa-coins me-1"></i> Đã trả: <span
                                            x-text="formatCurrency(selected.paid_amount)"></span></span>
                                    <span x-show="selected.amount"
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1"><i
                                            class="fas fa-coins me-1"></i> Còn nợ: <span
                                            x-text="formatCurrency(selected.amount)"></span></span>
                                    <a x-show="selected.ship_id" :href="'/admin/ships/' + selected.ship_id"
                                        target="_blank"
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2 py-1 text-decoration-none hover-shadow transition-all"><i
                                            class="fas fa-ship me-1"></i> Xem chi tiết tàu</a>
                                    <span>Ngày tạo: <span class="fw-bold"
                                            x-text="formatDate(selected.created_at)"></span></span>
                                    <span>Người tạo: <span class="fw-bold" x-text="selected.creator.name"></span></span>
                                </div>
                            </div>
                            <div>
                                <span class="badge rounded-pill px-3 py-2 fw-bold"
                                    :class="{
                                        'bg-warning bg-opacity-25 text-warning': selected.status === 'pending',
                                        'bg-success bg-opacity-25 text-success': selected.status === 'approved',
                                        'bg-danger bg-opacity-25 text-danger': selected.status === 'rejected'
                                    }">
                                    <span x-show="selected.status === 'pending'">Chờ duyệt</span>
                                    <span x-show="selected.status === 'approved'">Đã duyệt</span>
                                    <span x-show="selected.status === 'rejected'">Từ chối</span>
                                </span>
                            </div>
                        </div>

                        <div class="bg-light rounded-4 p-4 mb-4 border" style="min-height: 50px; white-space: pre-wrap;"
                            x-text="selected.content">
                        </div>

                        <template x-if="selected.status === 'rejected' && selected.rejection_reason">
                            <div class="alert alert-danger rounded-4 border-0 shadow-sm mt-3">
                                <strong><i class="fas fa-exclamation-circle me-1"></i> Lý do từ chối chung:</strong>
                                <p class="mb-0 mt-1" x-text="selected.rejection_reason"></p>
                            </div>
                        </template>

                        {{-- Timeline Section --}}
                        <div class="mt-4" x-show="selected.steps && selected.steps.length > 0">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-sitemap me-1 text-primary"></i> Tuyến
                                Phê Duyệt</h6>
                            <div class="d-flex flex-column gap-3 position-relative ps-4 pb-2"
                                style="border-left: 2px dashed #dee2e6; margin-left: 12px; max-height: 500px; overflow-y: auto; padding-right: 10px;"
                                x-effect="if (selected) { 
                                     $el.scrollTop = 0; 
                                     setTimeout(() => { 
                                         let pending = $el.querySelector('.step-pending'); 
                                         if(pending) { 
                                             let target = pending.offsetTop - ($el.offsetHeight / 2) + (pending.offsetHeight / 2);
                                             let start = $el.scrollTop;
                                             let change = target - start;
                                             let duration = 1200; 
                                             let startTime = performance.now();
                                             let animateScroll = function(currentTime) {
                                                 let elapsedTime = currentTime - startTime;
                                                 let progress = Math.min(elapsedTime / duration, 1);
                                                 let ease = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;
                                                 $el.scrollTop = start + change * ease;
                                                 if (progress < 1) {
                                                     requestAnimationFrame(animateScroll);
                                                 }
                                             };
                                             requestAnimationFrame(animateScroll);
                                         } 
                                     }, 400); 
                                 }">
                                <template x-for="step in selected.steps" :key="step.id">
                                    <div class="position-relative"
                                        :class="{ 'step-pending': step.status === 'pending' }">
                                        <div class="position-absolute bg-white rounded-circle border d-flex align-items-center justify-content-center"
                                            style="width: 28px; height: 28px; left: -40px; top: 0;"
                                            :class="{
                                                'border-success text-success': step.status === 'approved',
                                                'border-danger text-danger': step.status === 'rejected',
                                                'border-primary text-primary': step.status === 'pending'
                                            }">
                                            <i class="fas fa-check small" x-show="step.status === 'approved'"></i>
                                            <i class="fas fa-times small" x-show="step.status === 'rejected'"></i>
                                            <i class="fas fa-circle" style="font-size: 0.45rem;"
                                                x-show="step.status === 'pending'"></i>
                                        </div>
                                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                            <div class="card-body p-3 bg-light-50">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold text-dark text-uppercase"
                                                        style="font-size: 0.85rem;">
                                                        <span
                                                            x-text="step.name ? step.name : 'Tầng ' + step.step_level"></span>
                                                        <span
                                                            class="badge bg-white text-secondary border ms-2 fw-normal"
                                                            x-text="step.approval_type === 'or' ? 'Chỉ cần một người duyệt' : 'Tất cả phải duyệt'"></span>
                                                    </span>
                                                    <span class="badge rounded-pill"
                                                        :class="{
                                                            'bg-success': step.status === 'approved',
                                                            'bg-danger': step.status === 'rejected',
                                                            'bg-warning text-dark': step.status === 'pending'
                                                        }"
                                                        x-text="step.status === 'approved' ? 'Đã duyệt' : (step.status === 'rejected' ? 'Từ chối' : 'Đang xét duyệt')"></span>
                                                </div>
                                                <div class="d-flex flex-column gap-2 mt-3">
                                                    <template x-for="approval in step.approvals"
                                                        :key="approval.id">
                                                        <div
                                                            class="d-flex flex-column bg-white border rounded-3 px-3 py-2">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <template x-if="approval.user && approval.user.avatar">
                                                                    <img :src="'/storage/' + approval.user.avatar"
                                                                        :alt="approval.user.name"
                                                                        class="rounded-circle flex-shrink-0"
                                                                        style="width: 28px; height: 28px; object-fit: cover; border: 1.5px solid #dee2e6;">
                                                                </template>
                                                                <template
                                                                    x-if="!approval.user || !approval.user.avatar">
                                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                                        style="width: 28px; height: 28px; font-size: 0.65rem;"
                                                                        x-text="approval.user && approval.user.name ? approval.user.name.charAt(0).toUpperCase() : '?'">
                                                                    </div>
                                                                </template>
                                                                <span class="fw-bold small text-dark flex-grow-1"
                                                                    x-text="approval.user.name"></span>
                                                                <span class="badge bg-light border text-success"
                                                                    x-show="approval.status === 'approved'"><i
                                                                        class="fas fa-check-circle me-1"></i> Đã
                                                                    duyệt</span>
                                                                <span class="badge bg-light border text-danger"
                                                                    x-show="approval.status === 'rejected'"><i
                                                                        class="fas fa-times-circle me-1"></i> Từ
                                                                    chối</span>
                                                                <span class="badge bg-light border text-muted"
                                                                    x-show="approval.status === 'pending'"><i
                                                                        class="fas fa-clock me-1 text-warning"></i> Chờ
                                                                    duyệt</span>
                                                                <span class="badge bg-light border text-muted"
                                                                    x-show="approval.status === 'skipped'"><i
                                                                        class="fas fa-minus-circle me-1 text-secondary"></i>
                                                                    Bỏ qua</span>
                                                            </div>
                                                            <div class="small bg-light-50 border rounded p-2 mt-2"
                                                                x-show="approval.comment && approval.status !== 'pending'"
                                                                style="font-size: 0.8rem;">
                                                                <i
                                                                    class="fas fa-comment-dots text-primary opacity-50 me-1"></i>
                                                                <span x-text="approval.comment"
                                                                    class="fst-italic text-muted"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                {{-- Step Attachments Section --}}
                                                <div class="mt-3 border-top pt-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="fw-bold text-dark small"><i
                                                                class="fas fa-paperclip me-1 text-primary"></i> Tài
                                                            liệu đính kèm</span>
                                                        @if ($canCreate)
                                                            <label
                                                                class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 mb-0 d-flex align-items-center"
                                                                style="cursor:pointer; font-size: 0.75rem;">
                                                                <i class="fas fa-cloud-upload-alt me-1"></i> Tải lên
                                                                <input type="file" multiple class="d-none"
                                                                    @change="uploadFiles($event.target.files, step.id)">
                                                            </label>
                                                        @endif
                                                    </div>

                                                    {{-- File list --}}
                                                    <div x-show="!step.attachment_urls || step.attachment_urls.length === 0"
                                                        class="border border-dashed rounded-3 p-2 text-center text-muted small">
                                                        <span class="mb-0 opacity-50" style="font-size: 0.8rem;">Chưa
                                                            có tài liệu đính kèm cho bước này</span>
                                                    </div>

                                                    <div class="row g-2"
                                                        x-show="step.attachment_urls && step.attachment_urls.length > 0">
                                                        <template x-for="(file, idx) in step.attachment_urls"
                                                            :key="file.filename">
                                                            <div class="col-12">
                                                                <div
                                                                    class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-white shadow-sm">
                                                                    <i class="fas fa-file text-primary small"></i>
                                                                    <a :href="file.url"
                                                                        :data-fancybox="'gallery-' + selected.id"
                                                                        :data-type="file.filename.toLowerCase().endsWith('.pdf') ?
                                                                            'pdf' : (file.filename.toLowerCase().match(
                                                                                    /\.(jpe?g|png|gif|webp)$/i) ?
                                                                                'image' : 'iframe')"
                                                                        class="text-dark text-decoration-none flex-grow-1 text-truncate fw-bold"
                                                                        style="font-size: 0.8rem;"
                                                                        x-text="file.filename"></a>
                                                                    <a href="#"
                                                                        @click.prevent="$el.previousElementSibling.click()"
                                                                        class="btn btn-sm btn-outline-info rounded-pill px-2 py-0"
                                                                        title="Xem tài liệu">
                                                                        <i class="fas fa-eye"
                                                                            style="font-size: 0.7rem;"></i>
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0"
                                                                        title="Ký số PDF này"
                                                                        x-show="file.filename && file.filename.toLowerCase().endsWith('.pdf')"
                                                                        @click="openSignatureModal(step.id, file)">
                                                                        <i class="fas fa-signature"
                                                                            style="font-size: 0.7rem;"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-success rounded-pill px-2 py-0"
                                                                        title="Gán mã QR vào PDF này"
                                                                        x-show="file.filename && file.filename.toLowerCase().endsWith('.pdf')"
                                                                        @click="window.openQrEmbedModal(selected.id, file.url, file.filename, idx, null, null, step.id)">
                                                                        <i class="fas fa-qrcode"
                                                                            style="font-size: 0.7rem;"></i>
                                                                    </button>
                                                                    @if ($canCreate)
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0"
                                                                            title="Xóa file"
                                                                            @click="deleteFile(file.filename, step.id)">
                                                                            <i class="fas fa-trash-alt"
                                                                                style="font-size: 0.7rem;"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>


                    </div>

                    {{-- Actions for Admin/Director or Delete --}}
                    <div class="p-4 bg-white border-top mt-auto d-flex flex-column gap-2">
                        @if ($canApprove)
                            {{-- Row 1: Approval Actions (Bulk / Sign / Approve) --}}
                            <div class="d-flex flex-wrap gap-2 w-100"
                                x-show="selected.status === 'pending' && selected.can_approve">
                                <form method="POST"
                                    :action="`{{ url('admin/proposals') }}/${selected.id}/bulk-approve`"
                                    class="flex-grow-1" style="flex-basis: 30%;"
                                    @submit.prevent="promptBulkApprove($event)">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                        class="btn btn-outline-primary rounded-3 py-2 fw-bold shadow-sm w-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-forward me-2"></i> Duyệt tất cả
                                    </button>
                                </form>

                                <form x-ref="actionForm" method="POST"
                                    :action="`{{ url('admin/proposals') }}/${selected.id}/status`"
                                    class="d-flex flex-wrap gap-2 flex-grow-1 mt-0" style="flex-basis: 60%; margin:0;"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" x-model="actionStatus">
                                    <input type="hidden" name="rejection_reason" x-model="rejectionReason">
                                    <input type="hidden" name="reject_step" x-model="rejectStep">
                                    <select name="notify_users[]" id="hiddenNotifyUsers" multiple class="d-none"></select>
                                    <input type="file" name="rejection_images[]" id="rejectionImageInput"
                                        class="d-none" accept="image/*" multiple>

                                    <button type="button" @click="openSignatureModal()"
                                        class="btn btn-danger flex-grow-1 rounded-3 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-signature me-2"></i> Ký số
                                    </button>
                                    <button type="button" @click="submitAction('approved')"
                                        class="btn btn-tech-primary flex-grow-1 rounded-3 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-check-square me-2"></i> Duyệt thường
                                    </button>
                                </form>
                            </div>

                            {{-- Row 2: Reject / Delegate Action --}}
                            <div class="d-flex flex-wrap gap-2 w-100 mt-1">
                                <button type="button" @click="openRejectModal()"
                                    class="btn btn-outline-danger rounded-3 py-2 px-4 fw-bold bg-white flex-grow-1"
                                    x-show="selected.status === 'pending' && selected.can_approve">
                                    <i class="fas fa-times-circle me-1"></i> Từ chối
                                </button>
                                <button type="button" @click="openDelegateModal()"
                                    class="btn btn-outline-warning rounded-3 py-2 px-4 fw-bold bg-white flex-grow-1"
                                    x-show="selected.status === 'pending' && selected.can_approve">
                                    <i class="fas fa-exchange-alt me-1"></i> Uỷ quyền
                                </button>

                                @if ($canDelete)
                                    <form method="POST" :action="`{{ url('admin/proposals') }}/${selected.id}`"
                                        @submit.prevent="confirmDeleteProposal($event)" class="flex-grow-0"
                                        style="min-width: 80px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-danger rounded-3 py-2 px-3 fw-bold shadow-sm w-100"
                                            title="Xóa đề xuất">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </div>
    </template>
    </div>

    {{-- Modal Từ chối có upload ảnh --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-times-circle me-2"></i>Từ chối đề xuất
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Phạm vi từ chối <span
                                class="text-danger">*</span></label>
                        <select id="rejectStepSelect" class="form-select">
                            <option value="all">Từ chối TOÀN BỘ đề xuất (Hủy bỏ)</option>
                            <template x-if="selected && selected.steps">
                                <template x-for="(step, index) in selected.steps" :key="step.id">
                                    <option :value="step.step_level"
                                        x-text="'Lùi về Bước ' + step.step_level + ': ' + step.name"></option>
                                </template>
                            </template>
                        </select>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Từ bước bị chọn trở đi sẽ
                            được đặt lại trạng thái 'Chờ duyệt', các bước trước đó giữ nguyên kết quả.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Gửi thêm thông báo đến <span class="text-muted fw-normal">(Tuỳ chọn)</span></label>
                        <select id="rejectNotifySelect" class="form-select" multiple="multiple" data-placeholder="Chọn tài khoản nhận thông báo">
                            @foreach($users as $userOption)
                                @if ($userOption->id !== auth()->id())
                                    <option value="{{ $userOption->id }}">{{ $userOption->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Người tạo đề xuất sẽ tự động nhận được thông báo giải thích này.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Lý do từ chối <span
                                class="text-danger">*</span></label>
                        <textarea id="rejectReasonInput" class="form-control" rows="4" placeholder="Nhập lý do từ chối rõ ràng..."></textarea>
                    </div>
                    <div>
                        <label class="form-label fw-bold text-muted small text-uppercase">Ảnh đính kèm <span
                                class="text-muted fw-normal">(tuỳ chọn, cho phép chọn nhiều ảnh)</span></label>
                        <input type="file" id="rejectImagePicker" class="form-control" accept="image/*" multiple>
                        <div id="rejectImagePreview" class="mt-2 d-none">
                            <div id="rejectImagePreviewContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Ảnh sẽ được đính kèm vào
                            thông báo gửi đến người tạo đề xuất.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger rounded-pill px-5 fw-bold" id="rejectConfirmBtn"
                        onclick="submitRejectWithImage()">
                        <i class="fas fa-times-circle me-1"></i> Xác nhận từ chối
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Uỷ Quyền Người Duyệt --}}
    <div class="modal fade" id="delegateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-exchange-alt me-2"></i>Uỷ quyền người
                        duyệt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Form Uỷ quyền dùng AlpineJS x-ref để bind url -->
                <form method="POST"
                    x-bind:action="`{{ url('admin/proposals') }}/${selected.id}/steps/${selected.active_step_level ? selected.steps.find(s=>s.step_level == selected.active_step_level)?.id : ''}/delegate`">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Chọn người duyệt thay thế
                                <span class="text-danger">*</span></label>
                            <select name="delegate_user_id" id="delegateUserSelect" class="form-select" required>
                                <option value="" disabled selected>-- Chọn tài khoản --</option>
                                @foreach ($users as $userOption)
                                    @if ($userOption->id !== auth()->id())
                                        <option value="{{ $userOption->id }}">{{ $userOption->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>Người được
                                chọn sẽ thay thế bạn để duyệt đề xuất ở bước này.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Giao quyền
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('rejectNotifySelect')) {
                new TomSelect('#rejectNotifySelect', {
                    plugins: ['remove_button'],
                    placeholder: 'Chọn tài khoản nhận thông báo',
                });
            }
        });

        function openDelegateModal() {
            const modal = new bootstrap.Modal(document.getElementById('delegateModal'));
            modal.show();
        }

        function submitRejectWithImage() {
            const reason = document.getElementById('rejectReasonInput').value.trim();
            const rejectStep = document.getElementById('rejectStepSelect').value;
            
            // Xử lý giá trị mảng từ thẻ select multi (TomSelect)
            const notifySelectEl = document.getElementById('rejectNotifySelect');
            let notifyUsers = [];
            if (notifySelectEl && notifySelectEl.tomselect) {
                const val = notifySelectEl.tomselect.getValue();
                if (val) {
                    // TomSelect có thể trả về mảng hoặc chuỗi phân cách theo dòng/phẩy tuỳ cấu hình
                    notifyUsers = Array.isArray(val) ? val : String(val).split(',');
                }
            } else if (notifySelectEl) {
                // Fallback nếu TomSelect chưa khởi tạo
                notifyUsers = Array.from(notifySelectEl.selectedOptions).map(opt => opt.value);
            }

            if (!reason) {
                alert('Vui lòng nhập lý do từ chối!');
                return;
            }

            // Tìm Alpine component và gán rejection_reason, reject_step
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && alpineEl._x_dataStack) {
                const data = Alpine.mergeProxies(alpineEl._x_dataStack);
                data.rejectionReason = reason;
                data.rejectStep = rejectStep;
                data.actionStatus = 'rejected';
            }

            // Gán dữ liệu array người dùng nhận thông báo vào select ẩn
            const hiddenNotifyUsers = document.getElementById('hiddenNotifyUsers');
            if (hiddenNotifyUsers) {
                hiddenNotifyUsers.innerHTML = '';
                notifyUsers.forEach(id => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.selected = true;
                    hiddenNotifyUsers.appendChild(opt);
                });
            }

            // Copy tất cả các file ảnh từ picker sang input ẩn
            const pickerInput = document.getElementById('rejectImagePicker');
            const hiddenInput = document.getElementById('rejectionImageInput');
            if (pickerInput.files.length > 0 && hiddenInput) {
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < pickerInput.files.length; i++) {
                    dataTransfer.items.add(pickerInput.files[i]);
                }
                hiddenInput.files = dataTransfer.files;
            }

            // Đóng modal rồi submit form sau 1 tick
            const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
            if (modal) modal.hide();

            setTimeout(() => {
                const form = document.querySelector('[x-ref="actionForm"]');
                if (form) form.submit();
            }, 200);
        }

        // Preview ảnh khi chọn
        document.addEventListener('DOMContentLoaded', () => {
            const picker = document.getElementById('rejectImagePicker');
            if (picker) {
                picker.addEventListener('change', function() {
                    const preview = document.getElementById('rejectImagePreview');
                    const container = document.getElementById('rejectImagePreviewContainer');
                    if (this.files && this.files.length > 0) {
                        container.innerHTML = '';
                        Array.from(this.files).forEach(file => {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.className = 'rounded border';
                            img.style = 'height: 100px; width: auto; object-fit: cover;';
                            container.appendChild(img);
                        });
                        preview.classList.remove('d-none');
                    } else {
                        preview.classList.add('d-none');
                    }
                });
            }
        });
    </script>
