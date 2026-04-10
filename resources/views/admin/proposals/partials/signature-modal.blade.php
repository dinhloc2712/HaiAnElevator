<!-- Signature Positioning Modal -->
<div class="modal fade" id="signaturePositionModal" tabindex="-1" aria-hidden="true" x-ref="signatureModal">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 px-4 py-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 16px 16px 0 0;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1"><i class="fas fa-signature me-2"></i>Chọn vị trí chữ ký</h5>
                    <p class="small text-white-50 mb-0">Kéo khung chữ ký đến vị trí mong muốn rồi nhấn Xác nhận</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center bg-light">
                <div class="d-flex justify-content-center align-items-center mb-3 gap-3">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" @click.prevent="prevPage()" :disabled="pdfCurrentPage <= 1">
                        <i class="fas fa-chevron-left me-1"></i> Trang trước
                    </button>
                    <span class="fw-bold">Trang <span x-text="pdfCurrentPage"></span> / <span x-text="pdfTotalPages"></span></span>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" @click.prevent="nextPage()" :disabled="pdfCurrentPage >= pdfTotalPages">
                        Trang sau <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
                
                <div class="bg-white border shadow-sm mx-auto overflow-auto position-relative" style="max-height: 55vh; max-width: 100%; display: inline-block;">
                    <div class="position-relative d-inline-block" style="line-height: 0;">
                        <canvas id="pdf-render-canvas" x-ref="pdfCanvas"></canvas>
                        
                        <!-- Draggable Stamp -->
                        <div id="signature-stamp" class="position-absolute shadow"
                            :style="`border: 2px dashed #4e73df; background: rgba(78, 115, 223, 0.08); cursor: move; touch-action: none; left: ${boxX}px; top: ${boxY}px; width: ${200 * canvasScale}px; height: ${70 * canvasScale}px;`"
                            @mousedown="startDrag"
                            @touchstart="startDrag">
                            <span class="d-flex w-100 h-100 align-items-center justify-content-center fw-bold text-primary opacity-75" style="user-select: none; pointer-events: none; font-size: 1.1rem; letter-spacing: 0.5px;">
                                <i class="fas fa-signature me-2"></i> Kéo chữ ký
                            </span>
                        </div>
                    </div>
                </div>
                <p class="small text-muted mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Kéo khung nét đứt màu xanh để đặt vị trí chữ ký trên trang PDF.</p>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4 d-flex justify-content-between align-items-center bg-white" style="border-radius: 0 0 16px 16px;">
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-bold small text-muted mb-0 text-nowrap">Kiểu hiển thị:</label>
                    <select class="form-select form-select-sm rounded-pill" x-model="renderType" style="width: auto;">
                        <option value="1">Chỉ hiển thị text</option>
                        <option value="2">Hiển thị text và logo bên trái</option>
                        <option value="3">Chỉ hiển thị logo</option>
                        <option value="4">Hiển thị text và logo phía trên</option>
                        <option value="5">Hiển thị text và background</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" @click.prevent="handleMySignWithPosition()">
                        <i class="fas fa-paper-plane me-1"></i> Xác nhận & Gửi ký số
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div> {{-- Close the x-data row div --}}
