{{-- QR Embed into PDF Modal --}}
<div class="modal fade" id="qrEmbedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 px-4 py-4"
                style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); border-radius: 16px 16px 0 0;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1"><i class="fas fa-qrcode me-2"></i>Dán mã QR vào PDF
                    </h5>
                    <p class="small text-white-50 mb-0" id="qrEmbedModalSubtitle">Kéo khung QR đến vị trí mong muốn rồi
                        nhấn Xác nhận</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center bg-light">
                <div class="d-flex justify-content-center align-items-center mb-3 gap-3">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="qrPrevPageBtn" disabled>
                        <i class="fas fa-chevron-left me-1"></i> Trang trước
                    </button>
                    <span class="fw-bold">Trang <span id="qrCurrentPage">1</span> / <span
                            id="qrTotalPages">1</span></span>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="qrNextPageBtn" disabled>
                        Trang sau <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
                <div class="bg-white border shadow-sm mx-auto overflow-auto position-relative"
                    style="max-height: 55vh; max-width: 100%; display: inline-block;">
                    <div class="position-relative d-inline-block" style="line-height: 0;">
                        <canvas id="qr-pdf-canvas"></canvas>
                        {{-- Draggable QR Stamp --}}
                        <div id="qr-stamp" class="position-absolute shadow"
                            style="border: 2px dashed #1cc88a; background: rgba(28,200,138,0.08); cursor: move; touch-action: none; left: 20px; top: 20px; width: 100px; height: 100px;">
                            <img id="qr-stamp-img" src="" alt="QR"
                                style="width:100%; height:100%; object-fit:contain; display:block; pointer-events:none;">
                        </div>
                    </div>
                </div>
                <p class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Kéo khung xanh để đặt vị trí QR
                    code trên trang PDF.</p>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4 d-flex justify-content-between align-items-center bg-white"
                style="border-radius: 0 0 16px 16px;">
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-bold small text-muted mb-0">Kích thước QR:</label>
                    <input type="range" id="qrSizeSlider" min="60" max="250" value="125"
                        class="form-range" style="width:120px;">
                    <span id="qrSizeLabel" class="small text-muted">125px</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy
                        bỏ</button>
                    <button type="button" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm"
                        id="confirmEmbedQrBtn">
                        <i class="fas fa-qrcode me-1"></i> Xác nhận & Lưu PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
