@include('admin.proposals.partials.signature-modal')
@include('admin.proposals.partials.qr-embed-modal')
@include('admin.proposals.partials.create-proposal-modal')

@section('scripts')
    {{-- QR Embed sessionStorage logic xử lý phía JS sau khi ký số --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            // Use Blob URL to load the worker from CDN to avoid cross-origin Web Worker restrictions
            const workerUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            const blob = new Blob([`importScripts('${workerUrl}');`], {
                type: 'application/javascript'
            });
            pdfjsLib.GlobalWorkerOptions.workerPort = new Worker(URL.createObjectURL(blob));
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function proposalManager() {
            return {
                title: '',
                proposals: @json($proposals),
                filter: 'all',
                searchQuery: '',
                selected: null,
                actionStatus: '',
                rejectionReason: '',
                uploading: false,
                currentSignStepId: null,
                renderType: 3, // Mặc định là 3 (Chỉ hiển thị logo) theo yêu cầu User

                // PDF & Signature state
                pdfUrl: '',
                pdfCurrentPage: 1,
                pdfTotalPages: 0,
                canvasScale: 1.5,
                boxX: 0,
                boxY: 0,
                dragOffsetX: 0,
                dragOffsetY: 0,

                init() {
                    if (this.proposals.length > 0) {
                        const pending = this.proposals.find(p => p.status === 'pending');
                        this.selected = pending || this.proposals[0];
                    }
                },

                get filteredProposals() {
                    let filtered = this.proposals;

                    // 1. Áp dụng bộ lọc trạng thái
                    if (this.filter !== 'all') {
                        filtered = filtered.filter(p => p.status === this.filter);
                    }

                    // 2. Áp dụng bộ lọc tìm kiếm
                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(p => {
                            const matchTitle = p.title && p.title.toLowerCase().includes(q);
                            const matchCategory = p.category && p.category.toLowerCase().includes(q);
                            const matchCreator = p.creator && p.creator.name && p.creator.name.toLowerCase()
                                .includes(q);
                            return matchTitle || matchCategory || matchCreator;
                        });
                    }

                    return filtered;
                },

                selectProposal(p) {
                    this.selected = p;
                },

                formatDate(autoString) {
                    if (!autoString) return '';
                    const date = new Date(autoString);
                    return date.toISOString().split('T')[0];
                },

                formatCurrency(amount) {
                    if (!amount) return '';
                    return Number(amount).toLocaleString('vi-VN') + ' VNĐ';
                },

                confirmDeleteProposal(e) {
                    Swal.fire({
                        title: 'Xóa đề xuất này?',
                        text: 'Bạn có chắc chắn muốn xóa đề xuất này? Trạng thái hiện tại và các bình luận sẽ bị vĩnh viễn xóa.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Vâng, xóa nó!',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            e.target.submit();
                        }
                    });
                },

                submitAction(status) {
                    this.actionStatus = status;
                    setTimeout(() => {
                        this.$refs.actionForm.submit();
                    }, 50);
                },

                promptReject() {
                    Swal.fire({
                        title: 'Từ chối đề xuất',
                        input: 'textarea',
                        inputLabel: 'Nhập lý do từ chối (bắt buộc)',
                        inputPlaceholder: 'Không hợp lệ...',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận từ chối',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#dc3545',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Bạn phải nhập lý do từ chối!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.rejectionReason = result.value;
                            this.submitAction('rejected');
                        }
                    });
                },

                promptBulkApprove(event) {
                    Swal.fire({
                        title: 'Xác nhận duyệt tất cả?',
                        text: 'Hệ thống sẽ tự động duyệt tất cả các tầng tiếp theo do bạn phụ trách. Bỏ qua các bước đang vướng người khác cùng duyệt hoặc chuyển qua cấp duyệt khác. Bạn có chắc chắn tiếp tục?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-forward me-1"></i> Có, Duyệt nhanh',
                        cancelButtonText: 'Hủy bỏ'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            event.target.submit();
                        }
                    });
                },

                openRejectModal() {
                    // Reset modal content trước khi mở
                    const reasonInput = document.getElementById('rejectReasonInput');
                    const imagePicker = document.getElementById('rejectImagePicker');
                    const preview = document.getElementById('rejectImagePreview');
                    if (reasonInput) reasonInput.value = '';
                    if (imagePicker) imagePicker.value = '';
                    if (preview) preview.classList.add('d-none');

                    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                    modal.show();
                },

                async openSignatureModal(passedStepId = null, passedPdfFile = null) {
                    if (!this.selected) return;

                    let pdfFile = passedPdfFile || null;
                    let stepId = passedStepId || null;

                    // Nếu không được truyền cụ thể → fallback tìm PDF từ step đang pending
                    if (!pdfFile || !stepId) {
                        if (this.selected.steps && this.selected.steps.length > 0) {
                            const activeStep = this.selected.steps.find(s => s.status === 'pending');
                            if (activeStep) {
                                const foundPdf = activeStep.attachment_urls?.find(f => f.filename.toLowerCase()
                                    .endsWith('.pdf'));
                                if (foundPdf) {
                                    pdfFile = foundPdf;
                                    stepId = activeStep.id;
                                }
                            }
                        }
                    }

                    if (!pdfFile) {
                        Swal.fire('Lỗi', 'Không có file PDF nào để ký.', 'error');
                        return;
                    }

                    this.pdfUrl = pdfFile.url;
                    this.currentSignStepId = stepId;
                    this.pdfCurrentPage = 1;
                    this.pdfTotalPages = 0;

                    const modal = new bootstrap.Modal(document.getElementById('signaturePositionModal'));
                    modal.show();

                    if (!window._globalPdfDoc || window._globalPdfDoc.url !== this.pdfUrl) {
                        Swal.fire({
                            title: 'Đang tải PDF...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                        try {
                            // Fetch manually to ensure auth headers/cookies are sent
                            const pdfRes = await fetch(this.pdfUrl, {
                                headers: {
                                    'Accept': 'application/pdf'
                                }
                            });

                            if (!pdfRes.ok) throw new Error("Failed to fetch PDF");

                            const pdfBlob = await pdfRes.blob();
                            const pdfArrayBuffer = await pdfBlob.arrayBuffer();

                            const loadingTask = pdfjsLib.getDocument({
                                data: new Uint8Array(pdfArrayBuffer)
                            });
                            window._globalPdfDoc = await loadingTask.promise;
                            window._globalPdfDoc.url = this.pdfUrl; // Store to avoid redownloading
                            this.pdfTotalPages = window._globalPdfDoc.numPages;

                            this.$nextTick(async () => {
                                await this.drawPage(this.pdfCurrentPage);
                                Swal.close();
                            });
                        } catch (e) {
                            console.error("PDF Load Error:", e);
                            Swal.close();
                            Swal.fire('Lỗi', 'Không thể xem trước do định dạng PDF hoặc lỗi tải xuống.', 'error');
                        }
                    } else {
                        this.pdfTotalPages = window._globalPdfDoc.numPages;
                        this.$nextTick(async () => {
                            await this.drawPage(this.pdfCurrentPage);
                        });
                    }
                },

                async drawPage(pageNum) {
                    if (!window._globalPdfDoc) return;
                    const page = await window._globalPdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({
                        scale: this.canvasScale
                    });

                    // Use document.getElementById as a fallback if $refs hasn't mounted in the modal yet
                    const canvas = this.$refs.pdfCanvas || document.getElementById('pdf-render-canvas');
                    if (!canvas) {
                        console.error("Canvas element not found!");
                        return;
                    }

                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    await page.render(renderContext).promise;

                    // Center box initially or retain previous
                    if (this.boxX === 0 && this.boxY === 0) {
                        const stampW = 200 * this.canvasScale;
                        const stampH = 70 * this.canvasScale;
                        const padding = 20 * this.canvasScale; // 20px padding from the edge

                        this.boxX = Math.max(0, canvas.width - stampW - padding);
                        this.boxY = Math.max(0, canvas.height - stampH - padding);
                    }
                },

                async prevPage() {
                    if (this.pdfCurrentPage > 1) {
                        this.pdfCurrentPage--;
                        await this.drawPage(this.pdfCurrentPage);
                    }
                },

                async nextPage() {
                    if (this.pdfCurrentPage < this.pdfTotalPages) {
                        this.pdfCurrentPage++;
                        await this.drawPage(this.pdfCurrentPage);
                    }
                },

                startDrag(e) {
                    e.preventDefault();
                    const isTouch = e.type === 'touchstart';
                    const clientX = isTouch ? e.touches[0].clientX : e.clientX;
                    const clientY = isTouch ? e.touches[0].clientY : e.clientY;

                    this.dragOffsetX = clientX - this.boxX;
                    this.dragOffsetY = clientY - this.boxY;

                    // Store bounded functions in 'this' to remove them later
                    this.boundMoveHandler = (moveEvent) => this.doDrag(moveEvent);
                    this.boundUpHandler = (upEvent) => this.endDrag(upEvent, isTouch);

                    document.addEventListener(isTouch ? 'touchmove' : 'mousemove', this.boundMoveHandler, {
                        passive: false
                    });
                    document.addEventListener(isTouch ? 'touchend' : 'mouseup', this.boundUpHandler);
                },

                endDrag(e, isTouch) {
                    document.removeEventListener(isTouch ? 'touchmove' : 'mousemove', this.boundMoveHandler);
                    document.removeEventListener(isTouch ? 'touchend' : 'mouseup', this.boundUpHandler);
                },
                doDrag(e) {
                    e.preventDefault();
                    const isTouch = !!e.touches && e.touches.length > 0;
                    const clientX = isTouch ? e.touches[0].clientX : e.clientX;
                    const clientY = isTouch ? e.touches[0].clientY : e.clientY;

                    let newX = clientX - this.dragOffsetX;
                    let newY = clientY - this.dragOffsetY;

                    const canvas = document.getElementById('pdf-render-canvas');
                    if (!canvas) return;

                    const w = 200 * this.canvasScale;
                    const h = 70 * this.canvasScale;

                    this.boxX = Math.max(0, Math.min(newX, canvas.width - w));
                    this.boxY = Math.max(0, Math.min(newY, canvas.height - h));
                },

                async handleMySignWithPosition() {
                    if (!this.selected) return;

                    // Lấy vị trí stamp — đảm bảo có giá trị hợp lệ
                    let bX = this.boxX;
                    let bY = this.boxY;

                    // Nếu stamp chưa được center (drawPage chưa chạy xong), lấy từ DOM
                    if (bX === 0 && bY === 0) {
                        const canvas = document.getElementById('pdf-render-canvas');
                        const stamp = document.getElementById('signature-stamp');
                        if (canvas && stamp) {
                            const canvasRect = canvas.getBoundingClientRect();
                            const stampRect = stamp.getBoundingClientRect();
                            bX = stampRect.left - canvasRect.left;
                            bY = stampRect.top - canvasRect.top;
                        }
                    }

                    const ptX = Math.round(bX / this.canvasScale);
                    const ptY = Math.round(bY / this.canvasScale);

                    // DEBUG — xem tọa độ thực tế gửi lên
                    console.log('[MySign] boxX:', this.boxX, 'boxY:', this.boxY, '→ ptX:', ptX, 'ptY:', ptY, 'page:',
                        this.pdfCurrentPage);

                    // Đóng modal chọn vị trí
                    const posModalEl = document.getElementById('signaturePositionModal');
                    const posModal = bootstrap.Modal.getInstance(posModalEl);
                    if (posModal) posModal.hide();

                    // ── Bước 1: Upload + gửi yêu cầu ký ────────────────────────────
                    Swal.fire({
                        title: '<i class="fas fa-cloud-upload-alt me-2 text-primary"></i> Đang gửi file lên MySign...',
                        html: 'Hệ thống đang upload tài liệu và gửi yêu cầu ký số đến Viettel MySign...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });

                    let signData;
                    try {
                        const r1 = await fetch(`/admin/proposals/${this.selected.id}/mysign-sign`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                page: this.pdfCurrentPage,
                                x: ptX,
                                y: ptY,
                                pageHeight: Math.round(this.$refs.pdfCanvas.height / this.canvasScale),
                                step_id: this.currentSignStepId,
                                renderType: this.renderType
                            })
                        });
                        signData = await r1.json();
                    } catch (e) {
                        return Swal.fire('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
                    }

                    if (!signData.success) {
                        return Swal.fire('Lỗi ký số', signData.message || 'Upload/sign thất bại.', 'error');
                    }

                    // ── Bước 2: Polling chờ điện thoại xác nhận ────────────────────
                    let elapsed = 0;
                    const maxWait = 120; // 2 phút
                    const interval = 3; // poll mỗi 3 giây

                    const pollTimer = setInterval(async () => {
                        elapsed += interval;

                        // Cập nhật UI đếm giây
                        const remaining = maxWait - elapsed;
                        if (remaining >= 0) {
                            Swal.update({
                                title: '<i class="fas fa-mobile-alt me-2 text-warning"></i> Chờ xác nhận trên điện thoại',
                                html: `
                                <p class="mb-3 text-muted">Vui lòng mở <strong>ứng dụng Viettel MySign</strong> trên điện thoại và xác nhận yêu cầu ký số.</p>
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                                    <span class="fw-bold text-warning">Đang chờ xác nhận...</span>
                                </div>
                                <p class="small text-muted mb-0">Hết hạn sau <strong>${remaining}s</strong></p>`,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                            });
                        }

                        // Timeout
                        if (elapsed >= maxWait) {
                            clearInterval(pollTimer);
                            return Swal.fire('Hết thời gian',
                                'Không nhận được xác nhận từ điện thoại sau 2 phút. Vui lòng thử lại.',
                                'warning');
                        }

                        // Gọi poll API
                        let pollData;
                        try {
                            const params = new URLSearchParams({
                                transaction_id: signData.transaction_id,
                                file_id: signData.file_id,
                                pdf_filename: signData.pdf_filename,
                                pdf_index: signData.pdf_index,
                                step_id: this.currentSignStepId
                            });
                            const r2 = await fetch(
                                `/admin/proposals/${this.selected.id}/mysign-poll?${params}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF
                                    }
                                });
                            pollData = await r2.json();
                        } catch (e) {
                            return;
                        } // network error: thử lại poll sau

                        if (pollData.status === 'pending') {
                            return; // Tiếp tục chờ
                        }

                        clearInterval(pollTimer);

                        if (pollData.status === 'done') {
                            // Ký thành công (Bỏ tính năng tự động mở QR)
                            await Swal.fire({
                                icon: 'success',
                                title: 'Ký số thành công!',
                                html: `<p>Tài liệu đã được ký và lưu thành công.</p>
                                   <p class="small text-muted">Đang xử lý phê duyệt...</p>`,
                                timer: 2500,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                            this.submitAction('approved');
                        } else {
                            Swal.fire('Ký số thất bại', pollData.message ||
                                'Giao dịch bị từ chối hoặc lỗi.', 'error');
                        }
                    }, interval * 1000);

                    // Hiện ngay lần đầu trước khi interval chạy
                    Swal.fire({
                        title: '<i class="fas fa-mobile-alt me-2 text-warning"></i> Chờ xác nhận trên điện thoại',
                        html: `
                        <p class="mb-3 text-muted">Vui lòng mở <strong>ứng dụng Viettel MySign</strong> trên điện thoại và xác nhận yêu cầu ký số.</p>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                            <span class="fw-bold text-warning">Đang chờ xác nhận...</span>
                        </div>
                        <p class="small text-muted mb-0">Hết hạn sau <strong>${maxWait}s</strong></p>`,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                    });
                },

                async uploadFiles(files, stepId) {
                    if (!this.selected || !files.length || !stepId) return;

                    this.uploading = true;
                    const formData = new FormData();
                    for (const f of files) formData.append('files[]', f);
                    formData.append('_token', CSRF);

                    try {
                        const res = await fetch(`/admin/proposals/steps/${stepId}/upload-file`, {
                            method: 'POST',
                            body: formData,
                        });
                        const data = await res.json();
                        if (data.success) {
                            const stepIndex = this.selected.steps.findIndex(s => s.id === stepId);
                            if (stepIndex !== -1) {
                                this.selected.steps[stepIndex].attachment_urls = data.attachment_urls;
                            }

                            const idx = this.proposals.findIndex(p => p.id === this.selected.id);
                            if (idx !== -1) {
                                const listStepIndex = this.proposals[idx].steps.findIndex(s => s.id === stepId);
                                if (listStepIndex !== -1) {
                                    this.proposals[idx].steps[listStepIndex].attachment_urls = data.attachment_urls;
                                }
                            }
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Tải file lên thành công!',
                                showConfirmButton: false,
                                timer: 2500
                            });
                        } else {
                            throw new Error('Upload failed');
                        }
                    } catch (e) {
                        Swal.fire('Lỗi', 'Không thể tải file lên. Vui lòng thử lại.', 'error');
                    } finally {
                        this.uploading = false;
                    }
                },

                async deleteFile(filename, stepId) {
                    if (!this.selected || !stepId) return;
                    const confirmed = await Swal.fire({
                        title: 'Xóa file?',
                        text: `Bạn có chắc muốn xóa: ${filename}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Xóa',
                        cancelButtonText: 'Hủy'
                    });
                    if (!confirmed.isConfirmed) return;

                    try {
                        const res = await fetch(`/admin/proposals/steps/${stepId}/delete-file`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                filename
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            const stepIndex = this.selected.steps.findIndex(s => s.id === stepId);
                            if (stepIndex !== -1) {
                                this.selected.steps[stepIndex].attachment_urls = this.selected.steps[stepIndex]
                                    .attachment_urls.filter(f => f.filename !== filename);
                            }

                            const idx = this.proposals.findIndex(p => p.id === this.selected.id);
                            if (idx !== -1) {
                                const listStepIndex = this.proposals[idx].steps.findIndex(s => s.id === stepId);
                                if (listStepIndex !== -1) {
                                    this.proposals[idx].steps[listStepIndex].attachment_urls = this.selected.steps[
                                        stepIndex].attachment_urls;
                                }
                            }
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Đã xóa file.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    } catch (e) {
                        Swal.fire('Lỗi', 'Không thể xóa file.', 'error');
                    }
                }
            }
        }

        function proposalForm() {
            return {
                showProcessColumn: false,
                proposalType: 'default',
                title: '',
                category: 'Đăng kiểm tàu',
                amount: '',
                rawAmount: '',
                preVatAmount: '',
                preVatDisplay: '',
                vatPercentage: 8, // Set default VAT to 8%
                steps: [{
                    id: Date.now(),
                    type: 'or',
                    approvers: [],
                    name: '',
                    rawAmount: '',
                    displayAmount: '',
                    formula: ''
                }],
                allUsers: @json($users ?? []),
                processesData: @json($processes ?? []),
                shipsData: @json($ships ?? []),

                // Ship specific variables for calculations
                selectedShipId: null,
                isCreatingShip: false,
                // Ship Data Model holding all properties
                shipDataForm: {
                    registration_number: '',
                    registration_date: '',
                    status: 'active',
                    expiration_date: '',
                    name: '',
                    hull_number: '',
                    usage: '',
                    operation_area: '',
                    crew_size: '',
                    main_occupation: '',
                    secondary_occupation: '',
                    owner_name: '',
                    owner_id_card: '',
                    owner_phone: '',
                    province_id: '',
                    ward_id: '',
                    address: '',
                    gross_tonnage: '',
                    deadweight: '',
                    length_design: '',
                    width_design: '',
                    length_max: '',
                    width_max: '',
                    depth_max: '',
                    draft: '',
                    hull_material: '',
                    build_year: '',
                    build_place: '',
                    technical_safety_number: '',
                    technical_safety_date: '',
                    record_number: '',
                    record_date: ''
                },

                // ---- Engine Total Power ----
                shipTotalHp: 0,
                shipTotalKw: 0,
                shipSubTotalHp: 0,
                shipSubTotalKw: 0,

                // ---- Engine State (per-engine mark, number, hp, kw) ----
                shipEngines: [{ mark: '', number: '', hp: '', kw: '' }],
                shipSubEngines: [{ mark: '', number: '', hp: '', kw: '' }],

                // ---------------- Template Filling State ---------------- //
                templates: [],
                selectedTemplate: '',
                loadingTemplates: false,
                isGeneratingTemplate: false,

                initTemplateSelect(el) {
                    this.fetchTemplates(el);
                },

                fetchTemplates(el) {
                    this.loadingTemplates = true;
                    fetch(`{{ route('admin.proposals.templates.search') }}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.templates = data;
                            if (data.length > 0) {
                                data.forEach(t => {
                                    let option = document.createElement('option');
                                    option.value = t.file_path; // Use file_path as identifier
                                    option.text = t.title;
                                    el.add(option);
                                });
                            } else {
                                let option = document.createElement('option');
                                option.value = "";
                                option.text = "-- Không có mẫu Word nào --";
                                option.disabled = true;
                                el.options[0] = option;
                            }
                        })
                        .catch(err => {
                            console.error('Lỗi tải template:', err);
                            Swal.fire('Lỗi', 'Không thể tải danh sách mẫu tự động', 'error');
                        })
                        .finally(() => {
                            this.loadingTemplates = false;
                        });
                },

                generateTemplateFile() {
                    if (!this.selectedTemplate) {
                        Swal.fire('Lưu ý', 'Vui lòng chọn một mẫu Word.', 'warning');
                        return;
                    }

                    if (this.category !== 'Đăng kiểm tàu') {
                        Swal.fire('Lưu ý', 'Tính năng này chỉ áp dụng cho Đề xuất Đăng kiểm tàu.', 'warning');
                        return;
                    }

                    if (!this.selectedShipId && !this.isCreatingShip) {
                        Swal.fire('Lưu ý', 'Vui lòng chọn Tàu cá trước.', 'warning');
                        return;
                    }

                    this.isGeneratingTemplate = true;

                    // Chuẩn bị payload
                    let payload = {
                        template_path: this.selectedTemplate,
                        payment: this.rawAmount || 0,
                        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    };

                    if (this.selectedShipId) {
                        payload.ship_id = this.selectedShipId;
                    } else if (this.isCreatingShip) {
                        // Validate ship creating info before filling
                        if (!this.shipDataForm.registration_number || !this.shipDataForm.owner_name) {
                            Swal.fire('Lỗi', 'Vui lòng nhập Số đăng ký và Tên chủ tàu để tạo mẫu.', 'warning');
                            this.isGeneratingTemplate = false;
                            return;
                        }

                        payload.ship_data = {
                            ...this.shipDataForm
                        };
                        payload.ship_data.engine_hp = this.shipEngines.map(e => Number(e.hp) || 0);
                        payload.ship_data.engine_kw = this.shipEngines.map(e => Number(e.kw) || 0);
                        payload.ship_data.engine_mark = this.shipEngines.map(e => e.mark || '');
                        payload.ship_data.engine_number = this.shipEngines.map(e => e.number || '');
                        payload.ship_data.sub_engine_hp = this.shipSubEngines.map(e => Number(e.hp) || 0);
                        payload.ship_data.sub_engine_kw = this.shipSubEngines.map(e => Number(e.kw) || 0);
                        payload.ship_data.sub_engine_mark = this.shipSubEngines.map(e => e.mark || '');
                        payload.ship_data.sub_engine_number = this.shipSubEngines.map(e => e.number || '');
                    }

                    fetch(`{{ route('admin.proposals.templates.fill') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Đã tạo file tự động!',
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                // Tự động đính kèm vào Bước 1 bằng cách giả lập File upload
                                // Ta phải thực hiện kéo file từ server URL hoặc thay đổi input files
                                // Tuy nhiên, vì form chưa submit, file upload hiện tại thuộc quản lý của input type="file" browser.
                                // Không thể dễ dàng nhét file url vào `<input type="file">`.

                                // Cách giải quyết: Thêm 1 thẻ input hidden lưu mảng file mẫu đã tạo để backend tự gắn khi submit!
                                this.attachGeneratedFileToForm(data.filename, data.url);

                            } else {
                                throw new Error(data.message || 'Lỗi không xác định.');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Lỗi tạo file', err.message, 'error');
                        })
                        .finally(() => {
                            this.isGeneratingTemplate = false;
                        });
                },

                generatedFiles: [],

                attachGeneratedFileToForm(filename, url) {
                    // Thêm vào UI danh sách file đã tạo
                    this.generatedFiles.push({
                        filename: filename,
                        url: url
                    });
                    this.updateHiddenFileInput();
                },

                updateHiddenFileInput() {
                    // Cập nhật thẻ hidden input để báo cho Backend biết file đã tạo nằm trong storage/private
                    let hiddenInputId = 'generated-files-input';
                    let hiddenInput = document.getElementById(hiddenInputId);

                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.id = hiddenInputId;
                        hiddenInput.name = 'generated_auto_files';
                        document.getElementById('create-proposal-form').appendChild(hiddenInput);
                    }

                    let currentFiles = this.generatedFiles.map(f => f.filename);
                    hiddenInput.value = JSON.stringify(currentFiles);
                },
                // -------------------------------------------------------- //

                addShipEngine() {
                    this.shipEngines.push({ mark: '', number: '', hp: '', kw: '' });
                    this.calculateTotalPower();
                },

                removeShipEngine(index) {
                    if (this.shipEngines.length > 1) {
                        this.shipEngines.splice(index, 1);
                        this.calculateTotalPower();
                    }
                },

                calculateTotalPower() {
                    let thp = 0;
                    let tkw = 0;
                    this.shipEngines.forEach(eng => {
                        let h = parseFloat(eng.hp);
                        let k = parseFloat(eng.kw);
                        if (!isNaN(h)) thp += h;
                        if (!isNaN(k)) tkw += k;
                    });
                    this.shipTotalHp = thp;
                    this.shipTotalKw = tkw;
                },

                calculateTotalSubPower() {
                    let thp = 0;
                    let tkw = 0;
                    this.shipSubEngines.forEach(eng => {
                        let h = parseFloat(eng.hp);
                        let k = parseFloat(eng.kw);
                        if (!isNaN(h)) thp += h;
                        if (!isNaN(k)) tkw += k;
                    });
                    this.shipSubTotalHp = thp;
                    this.shipSubTotalKw = tkw;
                },

                updateShipDataDirectly() {
                    if (!this.selectedShipId) {
                        Swal.fire('Cảnh báo', 'Vui lòng chọn một tàu để cập nhật.', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Đang lưu dữ liệu tàu...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const payload = {
                        ...this.shipDataForm,
                        _method: 'PUT', // Route update thường dùng PUT/PATCH
                        engine_hp: this.shipEngines.map(e => Number(e.hp) || 0),
                        engine_kw: this.shipEngines.map(e => Number(e.kw) || 0),
                        engine_mark_inputs: this.shipEngines.map(e => e.mark || ''),
                        engine_number_inputs: this.shipEngines.map(e => e.number || ''),
                        sub_engine_hp: this.shipSubEngines.map(e => Number(e.hp) || 0),
                        sub_engine_kw: this.shipSubEngines.map(e => Number(e.kw) || 0),
                        sub_engine_mark_inputs: this.shipSubEngines.map(e => e.mark || ''),
                        sub_engine_number_inputs: this.shipSubEngines.map(e => e.number || '')
                    };

                    fetch(`/admin/ships/${this.selectedShipId}`, {
                        method: 'POST', // Dùng POST kèm _method: PUT cho Laravel
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json().catch(() => { throw new Error('API Error'); }))
                    .then(data => {
                        if (data.success || data.ship) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: 'Đã cập nhật dữ liệu gốc của tàu thành công!',
                                showConfirmButton: false, timer: 3000
                            });
                        } else {
                            throw new Error(data.message || 'Có lỗi xảy ra khi lưu.');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Cập nhật thất bại', err.message, 'error');
                    });
                },

                loadShipParameters(ship) {
                    if (ship) {
                        this.selectedShipId = ship.id;
                        for (let key in this.shipDataForm) {
                            this.shipDataForm[key] = ship[key] || '';
                        }
                        const dateFields = ['registration_date', 'expiration_date', 'technical_safety_date', 'record_date'];
                        dateFields.forEach(f => {
                            if (this.shipDataForm[f]) this.shipDataForm[f] = this.shipDataForm[f].split(' ')[0];
                        });

                        // Parse engine arrays (hp, kw, mark, number)
                        let hpArray = [], kwArray = [], markArray = [], numberArray = [];
                        try { hpArray = Array.isArray(ship.engine_hp) ? ship.engine_hp : JSON.parse(ship.engine_hp || '[]'); } catch (e) {}
                        try { kwArray = Array.isArray(ship.engine_kw) ? ship.engine_kw : JSON.parse(ship.engine_kw || '[]'); } catch (e) {}
                        try { markArray = Array.isArray(ship.engine_mark) ? ship.engine_mark : JSON.parse(ship.engine_mark || '[]'); } catch (e) {}
                        try { numberArray = Array.isArray(ship.engine_number) ? ship.engine_number : JSON.parse(ship.engine_number || '[]'); } catch (e) {}

                        let maxL = Math.max(hpArray.length, kwArray.length, markArray.length, numberArray.length, 1);
                        let engines = [];
                        for (let i = 0; i < maxL; i++) {
                            engines.push({
                                mark: markArray[i] !== undefined ? markArray[i] : '',
                                number: numberArray[i] !== undefined ? numberArray[i] : '',
                                hp: hpArray[i] !== undefined ? hpArray[i] : '',
                                kw: kwArray[i] !== undefined ? kwArray[i] : ''
                            });
                        }
                        this.shipEngines = engines;
                        // Parse sub engine arrays (hp, kw, mark, number)
                        let subHpArray = [], subKwArray = [], subMarkArray = [], subNumberArray = [];
                        try { subHpArray = Array.isArray(ship.sub_engine_hp) ? ship.sub_engine_hp : JSON.parse(ship.sub_engine_hp || '[]'); } catch (e) {}
                        try { subKwArray = Array.isArray(ship.sub_engine_kw) ? ship.sub_engine_kw : JSON.parse(ship.sub_engine_kw || '[]'); } catch (e) {}
                        try { subMarkArray = Array.isArray(ship.sub_engine_mark) ? ship.sub_engine_mark : JSON.parse(ship.sub_engine_mark || '[]'); } catch (e) {}
                        try { subNumberArray = Array.isArray(ship.sub_engine_number) ? ship.sub_engine_number : JSON.parse(ship.sub_engine_number || '[]'); } catch (e) {}

                        let maxSub = Math.max(subHpArray.length, subKwArray.length, subMarkArray.length, subNumberArray.length, 1);
                        let subEngines = [];
                        for (let i = 0; i < maxSub; i++) {
                            subEngines.push({
                                mark: subMarkArray[i] !== undefined ? subMarkArray[i] : '',
                                number: subNumberArray[i] !== undefined ? subNumberArray[i] : '',
                                hp: subHpArray[i] !== undefined ? subHpArray[i] : '',
                                kw: subKwArray[i] !== undefined ? subKwArray[i] : ''
                            });
                        }
                        this.shipSubEngines = subEngines;
                        this.calculateTotalPower();
                        this.calculateTotalSubPower();
                    } else {
                        this.selectedShipId = null;
                    }
                },

                toggleShipMode() {
                    if (this.isCreatingShip) {
                        // Chuyển sang chế độ tạo mới: xoá trắng select
                        this.selectedShipId = null;
                        if (window.tomSelectShip) {
                            window.tomSelectShip.clear();
                        }
                        for (let key in this.shipDataForm) {
                            this.shipDataForm[key] = '';
                        }
                        this.shipDataForm.status = 'active';
                        this.shipTotalHp = 0;
                        this.shipTotalKw = 0;
                        this.shipEngines = [{ mark: '', number: '', hp: '', kw: '' }];
                        this.shipSubTotalHp = 0;
                        this.shipSubTotalKw = 0;
                        this.shipSubEngines = [{ mark: '', number: '', hp: '', kw: '' }];
                    } else {
                        // Về lại chế độ chọn
                        for (let key in this.shipDataForm) {
                            this.shipDataForm[key] = '';
                        }
                        this.shipDataForm.status = 'active';
                    }
                },

                saveNewShip() {
                    if (!this.shipDataForm.registration_number || !this.shipDataForm.owner_name) {
                        Swal.fire('Lỗi', 'Vui lòng nhập Số đăng ký và Tên chủ tàu', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Đang tạo tàu mới...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let payload = { ...this.shipDataForm };
                    payload.engine_hp = this.shipEngines.map(e => Number(e.hp) || 0);
                    payload.engine_kw = this.shipEngines.map(e => Number(e.kw) || 0);
                    payload.engine_mark = this.shipEngines.map(e => e.mark || '');
                    payload.engine_number = this.shipEngines.map(e => e.number || '');
                    payload.sub_engine_hp = this.shipSubEngines.map(e => Number(e.hp) || 0);
                    payload.sub_engine_kw = this.shipSubEngines.map(e => Number(e.kw) || 0);
                    payload.sub_engine_mark = this.shipSubEngines.map(e => e.mark || '');
                    payload.sub_engine_number = this.shipSubEngines.map(e => e.number || '');

                    fetch(`{{ route('admin.ships.quick-store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) {
                                if (response.status === 422) {
                                    const errMsgs = Object.values(data.errors || {}).flat().join('\n');
                                    throw new Error(errMsgs || 'Dữ liệu không hợp lệ');
                                }
                                throw new Error(data.message || 'Có lỗi xảy ra khi tạo tàu.');
                            }
                            return data;
                        })
                        .then(data => {
                            Swal.fire('Thành công!', 'Đã tạo Tàu mới thành công.', 'success');

                            const createdShip = data.data;

                            // Thêm vào danh sách Local
                            this.shipsData.push(createdShip);

                            // Cập nhật TomSelect Option
                            if (window.tomSelectShip) {
                                window.tomSelectShip.addOption({
                                    value: createdShip.id,
                                    text: `${createdShip.registration_number} - ${createdShip.name || ''} (${createdShip.owner_name})`
                                });
                                window.tomSelectShip.setValue(createdShip.id);
                            }

                            // Đổi state
                            this.selectedShipId = createdShip.id;
                            this.isCreatingShip = false;
                            this.loadShipParameters(createdShip);
                        })
                        .catch(error => {
                            Swal.fire('Lỗi!', error.message, 'error');
                        });
                },

                formatPreVatAmount(e) {
                    // Remove non-digit characters
                    let val = e.target.value.replace(/\D/g, '');
                    this.preVatAmount = val;

                    // Add commas
                    if (val) {
                        this.preVatDisplay = Number(val).toLocaleString('vi-VN');
                    } else {
                        this.preVatDisplay = '';
                    }

                    this.calculateTotalAmount();
                },

                calculateTotalAmount() {
                    let preVat = Number(this.preVatAmount) || 0;
                    let vat = Number(this.vatPercentage) || 0;

                    if (preVat > 0) {
                        let total = preVat + (preVat * vat / 100);
                        // Round up safely 
                        total = Math.round(total);
                        this.rawAmount = total;
                        this.amount = total.toLocaleString('vi-VN');
                    } else {
                        this.rawAmount = '';
                        this.amount = '';
                    }
                },

                formatInputAmount(e) {
                    // Remove non-digit characters
                    let val = e.target.value.replace(/\D/g, '');
                    this.rawAmount = val;

                    // Add commas
                    if (val) {
                        this.amount = Number(val).toLocaleString('vi-VN');
                    } else {
                        this.amount = '';
                    }
                },

                formatStepAmount(index, value) {
                    let val = value.replace(/\D/g, '');
                    this.steps[index].rawAmount = val;
                    if (val) {
                        this.steps[index].displayAmount = Number(val).toLocaleString('vi-VN');
                    } else {
                        this.steps[index].displayAmount = '';
                    }
                },

                addStep() {
                    this.steps.push({
                        id: Date.now() + Math.random(),
                        type: 'or',
                        approvers: [],
                        name: '',
                        rawAmount: '',
                        displayAmount: ''
                    });
                },
                removeStep(idx) {
                    this.steps.splice(idx, 1);
                },

                loadProcessSteps(processId) {
                    if (!processId) {
                        this.steps = [{
                            type: 'or',
                            approvers: []
                        }]; // Reset to default
                        this.reInitTomSelects();
                        return;
                    }

                    const process = this.processesData.find(p => p.id == processId);
                    if (!process || !process.steps) return;

                    let newSteps = [];
                    // Sort steps by order_index just to be sure
                    const sortedSteps = process.steps.sort((a, b) => a.order_index - b.order_index);

                    sortedSteps.forEach(pStep => {
                        // For each step in the process, find items that require approval
                        const approvalItems = pStep.items.filter(item => item.requires_approval);

                        if (approvalItems.length > 0) {
                            approvalItems.forEach(item => {
                                let stepData = {
                                    id: Date.now() + Math.random(),
                                    type: item.require_all_approvers ? 'and' : 'or',
                                    approvers: item.approvers || [],
                                    name: `${pStep.title} - ${item.content}`,
                                    formula: item.formula || '',
                                    rawAmount: '',
                                    displayAmount: ''
                                };
                                newSteps.push(stepData);
                            });
                        }
                    });

                    if (newSteps.length > 0) {
                        this.steps = newSteps;
                    } else {
                        this.steps = [{
                            id: Date.now(),
                            type: 'or',
                            approvers: [],
                            name: '',
                            rawAmount: '',
                            displayAmount: '',
                            formula: ''
                        }]; // Fallback if no specific approval steps defined
                    }

                    this.reInitTomSelects();
                },

                calculateAmounts() {
                    if (!this.selectedShipId && !this.isCreatingShip) {
                        Swal.fire('Lỗi', 'Vui lòng chọn tàu cá trước khi tính toán', 'warning');
                        return;
                    }

                    if (!this.steps || this.steps.length === 0) {
                        Swal.fire('Lỗi', 'Chưa có bước phê duyệt nào để tính toán', 'warning');
                        return;
                    }

                    let totalAmount = 0;
                    let calculationDone = false;

                    this.steps.forEach((step, index) => {
                        if (step.formula) {
                            try {
                                // Chuẩn bị biến cho công thức
                                let formula = step.formula
                                    .toUpperCase(); // Đảm bảo hàm IF được viết hoa nếu cần thiết

                                // 1. Thay thế các hàm đặc biệt trước (ví dụ IF)
                                // regex tìm IF(condition, true_val, false_val)
                                // Lưu ý: Regex này cho IF đơn giản, không lồng nhau
                                formula = formula.replace(/IF\(([^,]+),([^,]+),([^)]+)\)/gi, function(match,
                                    condition, trueVal, falseVal) {
                                    return `((${condition}) ? (${trueVal}) : (${falseVal}))`;
                                });

                                // 2. Định nghĩa hàm fallback để eval an toàn hơn cho các biến
                                const evalContext = {
                                    GROSS_TONNAGE: Number(this.shipDataForm.gross_tonnage) || 0,
                                    TOTAL_ENGINE_HP: Number(this.shipTotalHp) || 0,
                                    TOTAL_ENGINE_KW: Number(this.shipTotalKw) || 0,
                                    TOTAL_SUB_ENGINE_HP: Number(this.shipSubTotalHp) || 0,
                                    TOTAL_SUB_ENGINE_KW: Number(this.shipSubTotalKw) || 0,
                                    LENGTH_MAX: Number(this.shipDataForm.length_max) || 0,
                                    WIDTH_MAX: Number(this.shipDataForm.width_max) || 0,
                                    DEADWEIGHT: Number(this.shipDataForm.deadweight) || 0,
                                    // Thêm các biến khác từ Tàu nếu cần
                                };

                                // Thay thế các biến trong công thức bằng giá trị
                                for (const [key, value] of Object.entries(evalContext)) {
                                    // Thay thế chính xác từ khóa, phân biệt hoa thường theo chuẩn chung
                                    const regex = new RegExp(`\\b${key}\\b`, 'gi');
                                    formula = formula.replace(regex, value);
                                }

                                // Debug
                                console.log(
                                    `Calculating Step ${index + 1}: original='${step.formula}', parsed='${formula}'`
                                );

                                // 3. Tính toán 
                                // Using new Function is slightly safer than eval when we control the string, but still requires care.
                                // In this case, we replaced variables with numbers, and replaced IF with ternary.
                                const result = new Function(`return ${formula};`)();

                                if (!isNaN(result) && result !== null) {
                                    const amount = Math.round(result);
                                    this.steps[index].rawAmount = amount;
                                    this.steps[index].displayAmount = Number(amount).toLocaleString('vi-VN');
                                    totalAmount += amount;
                                    calculationDone = true;
                                } else {
                                    console.warn(`Formula didn't return a number: ${result}`);
                                }

                            } catch (error) {
                                console.error(`Error calculating formula for step ${index + 1}: ${step.formula}`,
                                    error);
                                // Swal.fire('Lỗi', `Công thức lỗi ở bước ${index + 1}: ${error.message}`, 'error');
                            }
                        }
                    });

                    if (calculationDone) {
                        this.preVatAmount = totalAmount;
                        this.preVatDisplay = Number(totalAmount).toLocaleString('vi-VN');
                        this.calculateTotalAmount();
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Đã tính toán số tiền thành công',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire('Lưu ý', 'Không có công thức nào được tính toán do không khớp dữ liệu', 'info');
                    }
                },

                reInitTomSelects() {
                    // Alpine needs a tick to render new step markup before we init tom-selects
                    this.$nextTick(() => {
                        // Re-evaluating x-init will happen naturally, but we may need to force a reset if elements persist.
                        // Fortunately, Alpine + x-for with key repopulation usually handles DOM recreation which triggers x-init again.
                    });
                },


            };
        }
    </script>

    {{-- PDF-LIB for client-side QR embedding --}}
    <script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    <script>
        (function() {
            // ── State
            let _pdfBytes = null,
                _pdfDoc = null,
                _qrImgData = null;
            let _pdfFilename = null,
                _pdfIndex = -1,
                _proposalId = null;
            let _currentPage = 1,
                _totalPages = 1;
            const _scale = 1.5;
            let _qrSize = 125;
            let _qrRatio = 1.15; // 460/400 ratio for text added below QR
            let _stampLeft = 20,
                _stampTop = 20;
            let _isDragging = false,
                _dragStartX = 0,
                _dragStartY = 0;

            const canvas = document.getElementById('qr-pdf-canvas');
            const stamp = document.getElementById('qr-stamp');
            const stampImg = document.getElementById('qr-stamp-img');
            const slider = document.getElementById('qrSizeSlider');
            const sliderLbl = document.getElementById('qrSizeLabel');
            const confirmBtn = document.getElementById('confirmEmbedQrBtn');
            const prevBtn = document.getElementById('qrPrevPageBtn');
            const nextBtn = document.getElementById('qrNextPageBtn');

            function updateStamp() {
                stamp.style.left = _stampLeft + 'px';
                stamp.style.top = _stampTop + 'px';
                stamp.style.width = _qrSize + 'px';
                stamp.style.height = (_qrSize * _qrRatio) + 'px';
            }

            function updatePageBtns() {
                if (prevBtn) prevBtn.disabled = _currentPage <= 1;
                if (nextBtn) nextBtn.disabled = _currentPage >= _totalPages;
                document.getElementById('qrCurrentPage').textContent = _currentPage;
                document.getElementById('qrTotalPages').textContent = _totalPages;
            }
            async function renderPage(num) {
                if (!_pdfDoc) return;
                const pg = await _pdfDoc.getPage(num);
                const vp = pg.getViewport({
                    scale: _scale
                });
                canvas.width = vp.width;
                canvas.height = vp.height;
                await pg.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: vp
                }).promise;

                // Default position: bottom-left with margin
                const margin = 20; // 20px margin
                _stampLeft = margin;
                _stampTop = Math.max(0, canvas.height - (_qrSize * _qrRatio) - margin);
                updateStamp();
            }

            // ── Open modal
            window.openQrEmbedModal = async function(propId, pdfUrl, pdfFilename, pdfIdx, shipUrl, shipName, stepId =
                null) {
                _proposalId = propId;
                _pdfFilename = pdfFilename;
                _pdfIndex = pdfIdx;
                window._currentEmbedStepId = stepId; // Save step_id globally for this embed session

                // Generate QR Code containing the public link to the PDF file (no auth required)
                const _privateUrl = new URL(pdfUrl, window.location.origin);
                const _publicPath = _privateUrl.pathname.replace('/admin/media/file/', '/files/public/');
                const finalPdfUrl = window.location.origin + _publicPath;
                const qrApiUrl =
                    `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(finalPdfUrl)}&margin=4`;
                const subtitle = document.getElementById('qrEmbedModalSubtitle');
                if (subtitle && shipName) subtitle.textContent =
                    `Tàu: ${shipName} — Kéo khung QR đến vị trí muốn đặt`;

                fetch(qrApiUrl).then(r => r.blob()).then(blob => {
                    const img = new Image();
                    img.onload = () => {
                        const cvs = document.createElement('canvas');
                        cvs.width = 400;
                        cvs.height = 460;
                        const ctx = cvs.getContext('2d');
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, cvs.width, cvs.height);
                        ctx.drawImage(img, 0, 0, 400, 400);

                        ctx.fillStyle = '#000000';
                        ctx.font = '30px Arial, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('Quét mã QR để xem bản gốc', 200, 430);

                        cvs.toBlob(b => {
                            b.arrayBuffer().then(buf => {
                                _qrImgData = buf;
                            });
                            if (stampImg) stampImg.src = URL.createObjectURL(b);
                        }, 'image/png');
                    };
                    img.src = URL.createObjectURL(blob);
                }).catch(() => {});
                const modal = new bootstrap.Modal(document.getElementById('qrEmbedModal'));
                modal.show();
                Swal.fire({
                    title: 'Đang tải PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                try {
                    const res = await fetch(pdfUrl, {
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/pdf,*/*'
                        }
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    const ct = res.headers.get('content-type') || '';
                    if (!ct.includes('pdf') && !ct.includes('octet-stream')) {
                        throw new Error(
                            `Server trả về ${ct} thay vì PDF. Kiểm tra authentication hoặc file có tồn tại không.`
                        );
                    }
                    const buf = await res.arrayBuffer();
                    // Verify PDF header
                    const header = new Uint8Array(buf.slice(0, 4));
                    const isPdf = header[0] === 0x25 && header[1] === 0x50 && header[2] === 0x44 && header[
                        3] === 0x46; // %PDF
                    if (!isPdf) throw new Error('File tải về không phải định dạng PDF hợp lệ.');
                    _pdfBytes = new Uint8Array(buf.slice(
                        0)); // independent copy — pdf.js may transfer/detach original buf
                    _pdfDoc = await pdfjsLib.getDocument({
                        data: new Uint8Array(buf)
                    }).promise;
                    _totalPages = _pdfDoc.numPages;
                    _currentPage = 1;
                    updatePageBtns();
                    await renderPage(1);
                    Swal.close();
                } catch (e) {
                    Swal.close();
                    Swal.fire('Lỗi tải PDF', e.message, 'error');
                }
            };

            // ── Drag
            if (stamp) {
                stamp.addEventListener('mousedown', e => {
                    e.preventDefault();
                    _isDragging = true;
                    _dragStartX = e.clientX - _stampLeft;
                    _dragStartY = e.clientY - _stampTop;
                });
                stamp.addEventListener('touchstart', e => {
                    const t = e.touches[0];
                    _isDragging = true;
                    _dragStartX = t.clientX - _stampLeft;
                    _dragStartY = t.clientY - _stampTop;
                });
            }
            document.addEventListener('mousemove', e => {
                if (!_isDragging || !canvas) return;
                _stampLeft = Math.max(0, Math.min(e.clientX - _dragStartX, canvas.width - _qrSize));
                _stampTop = Math.max(0, Math.min(e.clientY - _dragStartY, canvas.height - (_qrSize *
                    _qrRatio)));
                updateStamp();
            });
            document.addEventListener('touchmove', e => {
                if (!_isDragging || !canvas) return;
                e.preventDefault();
                const t = e.touches[0];
                _stampLeft = Math.max(0, Math.min(t.clientX - _dragStartX, canvas.width - _qrSize));
                _stampTop = Math.max(0, Math.min(t.clientY - _dragStartY, canvas.height - (_qrSize *
                    _qrRatio)));
                updateStamp();
            }, {
                passive: false
            });
            document.addEventListener('mouseup', () => {
                _isDragging = false;
            });
            document.addEventListener('touchend', () => {
                _isDragging = false;
            });

            // ── Slider
            if (slider) slider.addEventListener('input', () => {
                _qrSize = parseInt(slider.value);
                if (sliderLbl) sliderLbl.textContent = _qrSize + 'px';
                updateStamp();
            });

            // ── Page nav
            if (prevBtn) prevBtn.addEventListener('click', async () => {
                if (_currentPage > 1) {
                    _currentPage--;
                    updatePageBtns();
                    await renderPage(_currentPage);
                }
            });
            if (nextBtn) nextBtn.addEventListener('click', async () => {
                if (_currentPage < _totalPages) {
                    _currentPage++;
                    updatePageBtns();
                    await renderPage(_currentPage);
                }
            });

            // ── Confirm embed
            if (confirmBtn) confirmBtn.addEventListener('click', async () => {
                if (!_pdfBytes || !_qrImgData) {
                    Swal.fire('Chưa sẵn sàng', 'Vui lòng đợi PDF và QR tải xong.', 'warning');
                    return;
                }
                const ptX = Math.round(_stampLeft / _scale);
                const ptSz = Math.round(_qrSize / _scale);
                const ptH = Math.round((_qrSize * _qrRatio) / _scale);
                const pg = await _pdfDoc.getPage(_currentPage);
                const vp = pg.getViewport({
                    scale: 1.0
                });
                const ptY = Math.max(0, Math.round(vp.height - (_stampTop / _scale) - ptH));
                Swal.fire({
                    title: 'Đang dán QR vào PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                try {
                    const pdfLibDoc = await PDFLib.PDFDocument.load(_pdfBytes, {
                        ignoreEncryption: true
                    });
                    const qrPng = await pdfLibDoc.embedPng(_qrImgData);
                    pdfLibDoc.getPages()[_currentPage - 1].drawImage(qrPng, {
                        x: ptX,
                        y: ptY,
                        width: ptSz,
                        height: ptH
                    });
                    const modBytes = await pdfLibDoc.save();
                    const b64 = btoa(Array.from(modBytes, b => String.fromCharCode(b)).join(''));
                    const bodyData = {
                        pdf_base64: b64,
                        original_filename: _pdfFilename,
                        pdf_index: _pdfIndex
                    };
                    if (window._currentEmbedStepId) {
                        bodyData.step_id = window._currentEmbedStepId;
                    }

                    const resp = await fetch(`/admin/proposals/${_proposalId}/embed-qr`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(bodyData)
                    });
                    const data = await resp.json();
                    const qrModal = bootstrap.Modal.getInstance(document.getElementById('qrEmbedModal'));
                    if (qrModal) qrModal.hide();
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Đã dán QR vào PDF thành công!',
                            showConfirmButton: false,
                            timer: 3500
                        });
                        if (window._alpinePM && window._alpinePM.selected) {
                            const stepIndex = window._alpinePM.selected.steps.findIndex(s => s.id === data
                                .step_id);
                            if (stepIndex > -1) {
                                window._alpinePM.selected.steps[stepIndex].attachment_urls = data
                                    .attachment_urls;
                            }
                        }
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể lưu PDF.', 'error');
                    }
                } catch (err) {
                    Swal.close();
                    Swal.fire('Lỗi', 'Có lỗi: ' + err.message, 'error');
                }
            });

            // ── Auto-open QR sau khi ký số (sessionStorage-based, không phụ thuộc tàu)
            document.addEventListener('DOMContentLoaded', () => {
                const pendingQr = sessionStorage.getItem('pendingQrEmbed');
                if (!pendingQr) return;
                let qrConfig;
                try {
                    qrConfig = JSON.parse(pendingQr);
                } catch (e) {
                    return;
                }
                sessionStorage.removeItem('pendingQrEmbed');

                setTimeout(() => {
                    const el = document.querySelector('[x-data]');
                    if (!el) return;
                    const comp = el._x_dataStack && el._x_dataStack[0];
                    if (!comp) return;
                    window._alpinePM = comp;

                    // Tìm proposal theo ID
                    const targetProposal = (comp.proposals || []).find(p => p.id == qrConfig
                        .proposalId);
                    if (!targetProposal) return;
                    comp.selected = targetProposal;

                    // Tìm step theo ID
                    const targetStep = (targetProposal.steps || []).find(s => s.id == qrConfig.stepId);
                    if (!targetStep) return;

                    // Lấy PDF mới nhất trong step đó
                    const stepFiles = targetStep.attachment_urls || [];
                    const pdfs = stepFiles.filter(f => f.filename && f.filename.toLowerCase().endsWith(
                        '.pdf'));
                    if (!pdfs.length) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ký số thành công!',
                            text: 'Không tìm thấy file PDF để dán QR.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    const lastPdf = pdfs[pdfs.length - 1];
                    const lastIdx = stepFiles.lastIndexOf(lastPdf);

                    window.openQrEmbedModal(targetProposal.id, lastPdf.url, lastPdf.filename, lastIdx,
                        null, null, targetStep.id);
                }, 700);
            });
        })();
    </script>
@endsection
