{{-- Create Modal --}}
<div class="modal fade" id="createProposalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1500px;">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 px-4 py-4"
                style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); border-radius: 16px 16px 0 0;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1"><i class="fas fa-plus-circle text-white me-2"></i>Tạo
                        Đề xuất mới</h5>
                    <p class="small text-white-50 mb-0">Điền thông tin và đính kèm file cần thiết</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.proposals.store') }}" method="POST" enctype="multipart/form-data"
                    id="create-proposal-form" x-data="proposalForm()">
                    @csrf
                    <div class="row">
                        {{-- Cột 1: Thông tin chung --}}
                        <div :class="showProcessColumn ? 'col-lg-5' : 'col-lg-8'" style="transition: all 0.3s ease;">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <h6 class="fw-bold text-primary mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin chung</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold" 
                                    @click="showProcessColumn = !showProcessColumn"
                                    title="Quản lý các bước phê duyệt của Đề xuất này">
                                    <i class="fas" :class="showProcessColumn ? 'fa-eye-slash' : 'fa-sitemap'"></i>
                                    <span x-text="showProcessColumn ? ' Ẩn Quy trình duyệt' : ' Cấu hình Quy trình duyệt'"></span>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Tên đề xuất <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="title" x-model="title" class="form-control" required
                                        placeholder="Vd: Xin cấp mới thiết bị siêu âm...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Phân loại <span
                                            class="text-danger">*</span></label>
                                    <select name="category" class="form-select" x-model="category" required>
                                        <option value="Đăng kiểm tàu">Đăng kiểm tàu</option>
                                        <option value="Mua sắm thiết bị">Mua sắm thiết bị</option>
                                        <option value="Vấn đề kỹ thuật">Vấn đề kỹ thuật</option>
                                        <option value="Hành chính nhân sự">Hành chính nhân sự</option>
                                        <option value="Đề xuất khác">Đề xuất khác</option>
                                    </select>

                                    <!-- Hidden Inputs -->
                                    <input type="hidden" name="type" x-model="proposalType">
                                    <input type="hidden" name="ship_id" x-model="selectedShipId">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Số tiền trước thuế
                                        <span class="text-muted fw-normal">(Tuỳ chọn)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control fw-bold text-primary"
                                            x-model="preVatDisplay" @input="formatPreVatAmount"
                                            placeholder="Vd: 50,000,000">
                                        <span class="input-group-text fw-bold text-muted">VNĐ</span>
                                    </div>
                                    <input type="hidden" name="pre_vat_amount" :value="preVatAmount">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">VAT <span
                                            class="text-muted fw-normal">(%)</span></label>
                                    <div class="input-group">
                                        <input type="number" name="vat" class="form-control text-center fw-bold"
                                            x-model="vatPercentage" min="0" max="100"
                                            @input="calculateTotalAmount" step="any">
                                        <span class="input-group-text fw-bold text-muted">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Thành tiền (Tổng
                                        cộng)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light fw-bold text-success"
                                            x-model="amount" readonly placeholder="0">
                                        <span class="input-group-text bg-light fw-bold text-muted">VNĐ</span>
                                    </div>
                                    <input type="hidden" name="amount" :value="rawAmount">
                                </div>
                            </div>

                            {{-- Ship Inspection Specific Fields --}}
                            <div class="row bg-light rounded-3 p-3 mb-3 border mx-0"
                                x-show="category === 'Đăng kiểm tàu'" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-primary mb-0"><i class="fas fa-ship me-2"></i>Thông tin đăng
                                        kiểm</h6>
                                    <button type="button"
                                        class="btn btn-md btn-outline-success fw-bold rounded-pill shadow-sm"
                                        @click="calculateAmounts()"
                                        x-show="(selectedShipId || isCreatingShip) && steps.length > 0">
                                        <i class="fas fa-calculator me-1"></i> Tính toán số tiền
                                    </button>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Quy trình áp
                                            dụng <span class="text-muted fw-normal">(Tuỳ chọn)</span></label>
                                        <select class="form-select" id="inspection_process_id"
                                            @change="loadProcessSteps($event.target.value)">
                                            <option value="">Không dùng quy trình mẫu</option>
                                            @foreach ($processes as $process)
                                                <option value="{{ $process->id }}">{{ $process->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6" x-show="category === 'Đăng kiểm tàu'" style="display: none;">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Hạn đăng kiểm
                                            <span class="text-danger">*</span></label>
                                        <input type="date" name="expiration_date" class="form-control"
                                            :required="category === 'Đăng kiểm tàu'"
                                            value="{{ now()->addYear()->format('Y-m-d') }}"
                                            title="Hạn đăng kiểm sẽ được áp dụng cho tàu khi đề xuất này được duyệt toàn bộ">
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-0">Chọn Tàu
                                            cá <span class="text-danger">*</span></label>
                                        @can('create_ship')
                                            <div class="form-check form-switch mb-0">
                                                <label class="form-label fw-bold text-muted small text-uppercase mb-0"
                                                    for="toggleCreateShip">Tàu mới</label>
                                                <input class="form-check-input" type="checkbox" id="toggleCreateShip"
                                                    x-model="isCreatingShip" @change="toggleShipMode()">
                                            </div>
                                        @endcan
                                    </div>

                                    {{-- Chọn Tàu cũ --}}
                                    <div x-show="!isCreatingShip">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="flex-grow-1 me-2">
                                                <select name="ship_id" id="select-ship" class="form-control"
                                                    :required="category === 'Đăng kiểm tàu' && !isCreatingShip"
                                                    x-init="$nextTick(() => {
                                                        window.tomSelectShip = new TomSelect($el, {
                                                            create: false,
                                                            sortField: { field: 'text', direction: 'asc' },
                                                            placeholder: 'Tìm kiếm theo số đăng ký hoặc tên tàu...',
                                                            onChange: function(value) {
                                                                if (value) {
                                                                    const text = this.options[value].text.replace(/\s+/g, ' ').trim();
                                                                    const regNumber = text.split(' - ')[0];
                                                                    $data.title = 'Đăng kiểm cho tàu ' + regNumber;
                                                    
                                                                    // Tìm tàu trong mảng shipsData để set các biến phục vụ tính toán
                                                                    const ship = $data.shipsData.find(s => s.id == value);
                                                                    if (ship) {
                                                                        $data.loadShipParameters(ship);
                                                                    }
                                                                } else {
                                                                    $data.selectedShipId = null;
                                                                }
                                                            }
                                                        });
                                                    });">
                                                    <option value="">-- Chọn tàu cá --</option>
                                                    @foreach ($ships as $ship)
                                                        <option value="{{ $ship->id }}">{{ $ship->registration_number }} - {{ $ship->name }} ({{ $ship->owner_name }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-outline-info text-nowrap" style="height: 38px;"
                                                x-show="selectedShipId"
                                                @click="updateShipDataDirectly()"
                                                title="Lưu lại các thông số vừa sửa ở dưới vào dữ liệu gốc của tàu này">
                                                <i class="fas fa-save me-1"></i> Lưu vào DB Tàu
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Khai báo Tàu mới --}}
                                    <div x-show="isCreatingShip" style="display: none;">
                                        <div class="card border border-primary border-opacity-25 bg-white shadow-sm">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fas fa-passport me-2"></i>Thông tin Đăng ký & Chủ tàu</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Số đăng ký <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.registration_number" placeholder="VD: NA-12345-TS">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Ngày đăng ký</label>
                                                        <input type="date" class="form-control form-control-sm" x-model="shipDataForm.registration_date">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Hạn đăng kiểm</label>
                                                        <input type="date" class="form-control form-control-sm" x-model="shipDataForm.expiration_date">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Tình trạng</label>
                                                        <select class="form-select form-select-sm" x-model="shipDataForm.status">
                                                            <option value="active">Hoạt động</option>
                                                            <option value="suspended">Đình chỉ</option>
                                                            <option value="expired">Hết hạn</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Tên chủ tàu <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.owner_name" placeholder="Nguyễn Văn A">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Số CMND/CCCD</label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.owner_id_card" placeholder="0123456789">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted mb-1 fw-bold">SĐT liên hệ</label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.owner_phone" placeholder="090...">
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Huyện/Thị xã</label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.ward_id" placeholder="VD: Huyện Đầm Hà">
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Tỉnh/Thành phố</label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.province_id" placeholder="VD: Quảng Ninh">
                                                    </div>
                                                    <div class="col-12 mt-2">
                                                        <label class="form-label small text-muted mb-1 fw-bold">Địa chỉ</label>
                                                        <input type="text" class="form-control form-control-sm" x-model="shipDataForm.address" placeholder="Xã Y, Huyện X, Tỉnh Z">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Form nhập thông số tàu (Hiển thị khi đã chọn tàu HOẶC đang tạo mới) --}}
                                <div class="col-12 mt-2" x-show="selectedShipId || isCreatingShip"
                                    style="display: none;">
                                    <div class="card border border-info border-opacity-50 shadow-sm bg-white">
                                        <div
                                            class="card-header bg-info bg-opacity-10 py-2 border-bottom-0 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-info fw-bold small"><i
                                                    class="fas fa-sliders-h me-1"></i> Thông số kích thước và Tải trọng</h6>
                                        </div>
                                        <div class="card-body p-3 accordion" id="shipDetailsAccordion">
                                            {{-- Section 1: Kích thước & Trọng tải --}}
                                            <div class="accordion-item mb-2 border rounded">
                                                <h2 class="accordion-header" id="headingSize">
                                                    <button class="accordion-button py-2 bg-light fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSize" aria-expanded="true" aria-controls="collapseSize">
                                                        <i class="fas fa-ruler-combined me-2"></i> Thông số Kích thước, Trọng tải & Kỹ thuật
                                                    </button>
                                                </h2>
                                                <div id="collapseSize" class="accordion-collapse collapse show" aria-labelledby="headingSize" data-bs-parent="#shipDetailsAccordion">
                                                    <div class="accordion-body p-3">
                                                        <div class="row g-2">
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tên tàu</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.name">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Số hiệu (Hull)</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.hull_number">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Công dụng</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.usage" placeholder="Nghề cá/...">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Vùng hoạt động</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.operation_area">
                                                            </div>
                                                            
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Nghề chính</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.main_occupation">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Nghề phụ</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.secondary_occupation">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Số thuyền viên</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.crew_size">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Vật liệu vỏ</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.hull_material">
                                                            </div>

                                                            <div class="col-12 mt-3 mb-1"><hr class="m-0"></div>

                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tổng GT</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.gross_tonnage" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Trọng tải (DWT)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.deadweight" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Ltk (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.length_design" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Btk (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.width_design" step="any">
                                                            </div>

                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Lmax (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.length_max" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Bmax (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.width_max" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Dmax (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.depth_max" step="any">
                                                            </div>
                                                            <div class="col-md-3 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Mớn nước (m)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.draft" step="any">
                                                            </div>

                                                            <div class="col-12 mt-3 mb-1"><hr class="m-0"></div>
                                                            
                                                            <div class="col-md-6 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Năm đóng</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="shipDataForm.build_year">
                                                            </div>
                                                            <div class="col-md-6 col-sm-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Nơi đóng</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.build_place" placeholder="Tên Tỉnh/Thành">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Section 2: Thông tin hệ thống Máy --}}
                                            <div class="accordion-item mb-2 border rounded">
                                                <h2 class="accordion-header" id="headingEngine">
                                                    <button class="accordion-button collapsed py-2 bg-light fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEngine" aria-expanded="false" aria-controls="collapseEngine">
                                                        <i class="fas fa-cogs me-2"></i> Hệ thống Máy (Hồng cầu)
                                                    </button>
                                                </h2>
                                                <div id="collapseEngine" class="accordion-collapse collapse" aria-labelledby="headingEngine" data-bs-parent="#shipDetailsAccordion">
                                                    <div class="accordion-body p-3">
                                                        <div class="row g-2 mb-3 pb-3 border-bottom">
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Ký hiệu máy</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.engine_mark">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Số máy</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="shipDataForm.engine_number">
                                                            </div>
                                                            <div class="col-md-3 mt-3">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tổng HP Máy chính</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" x-model="shipTotalHp" readonly>
                                                            </div>
                                                            <div class="col-md-3 mt-3">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tổng KW Máy chính</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" x-model="shipTotalKw" readonly>
                                                            </div>
                                                            <div class="col-md-3 mt-3">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tổng HP Máy phụ</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" x-model="shipSubTotalHp" readonly>
                                                            </div>
                                                            <div class="col-md-3 mt-3">
                                                                <label class="form-label small text-muted mb-1 fw-bold">Tổng KW Máy phụ</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" x-model="shipSubTotalKw" readonly>
                                                            </div>
                                                        </div>

                                                {{-- Danh sách Máy chính --}}
                                                <div class="col-12 d-flex justify-content-between align-items-center mt-2 mb-1 border-top pt-2">
                                                    <label class="form-label fw-bold text-muted small text-uppercase mb-0 text-primary">Danh sách Máy Chính</label>
                                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold rounded-pill shadow-sm" style="font-size: 0.70rem; padding: 0.2rem 0.5rem;" @click="addShipEngine()">
                                                        <i class="fas fa-plus me-1"></i> Thêm máy chính
                                                    </button>
                                                </div>

                                                <template x-for="(engine, index) in shipEngines" :key="'main_'+index">
                                                    <div class="col-12">
                                                        <div class="d-flex flex-wrap align-items-center gap-2 bg-light p-2 rounded-3 border mb-1">
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="min-width: 60px;">Ký hiệu</span>
                                                                    <input type="text" class="form-control" x-model="engine.mark" placeholder="VD: YANMAR">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="min-width: 60px;">Số máy</span>
                                                                    <input type="text" class="form-control" x-model="engine.number" placeholder="VD: 12345">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-primary fw-bold" style="width: 45px;">HP</span>
                                                                    <input type="number" class="form-control text-primary fw-bold" x-model="engine.hp" @input="calculateTotalPower()" step="any" placeholder="0">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="width: 45px;">KW</span>
                                                                    <input type="number" class="form-control text-primary fw-bold" x-model="engine.kw" @input="calculateTotalPower()" step="any" placeholder="0">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm rounded-circle px-2" @click="removeShipEngine(index)" x-show="shipEngines.length > 1" title="Xóa máy này">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Danh sách Máy phụ --}}
                                                <div class="col-12 d-flex justify-content-between align-items-center mt-2 mb-1 border-top pt-2">
                                                    <label class="form-label fw-bold text-muted small text-uppercase mb-0 text-info">Danh sách Máy Phụ</label>
                                                    <button type="button" class="btn btn-sm btn-outline-info fw-bold rounded-pill shadow-sm" style="font-size: 0.70rem; padding: 0.2rem 0.5rem;" @click="shipSubEngines.push({hp: '', kw: ''}); calculateTotalSubPower()">
                                                        <i class="fas fa-plus me-1"></i> Thêm máy phụ
                                                    </button>
                                                </div>

                                                <template x-for="(subEngine, index) in shipSubEngines" :key="'sub_'+index">
                                                    <div class="col-12">
                                                        <div class="d-flex flex-wrap align-items-center gap-2 bg-light p-2 rounded-3 border mb-1">
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="min-width: 60px;">Ký hiệu</span>
                                                                    <input type="text" class="form-control" x-model="subEngine.mark" placeholder="VD: HONDA">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="min-width: 60px;">Số máy</span>
                                                                    <input type="text" class="form-control" x-model="subEngine.number" placeholder="VD: 67890">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-info fw-bold" style="width: 45px;">HP</span>
                                                                    <input type="number" class="form-control text-info fw-bold" x-model="subEngine.hp" @input="calculateTotalSubPower()" step="any" placeholder="0">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="input-group input-group-sm shadow-sm">
                                                                    <span class="input-group-text bg-white text-muted fw-bold" style="width: 45px;">KW</span>
                                                                    <input type="number" class="form-control text-info fw-bold" x-model="subEngine.kw" @input="calculateTotalSubPower()" step="any" placeholder="0">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm rounded-circle px-2" @click="if(shipSubEngines.length > 1) { shipSubEngines.splice(index, 1); calculateTotalSubPower(); }" x-show="shipSubEngines.length > 1" title="Xóa máy phụ này">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <em class="d-block mt-3 small text-muted text-center"><i
                                                    class="fas fa-info-circle me-1"></i> Các thông số này được dùng để tính toán và lưu gốc.</em>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Nội dung chi tiết
                                    <span class="text-muted fw-normal">(Tuỳ chọn)</span></label>
                                <textarea name="content" class="form-control" rows="5" placeholder="Kính gửi Giám đốc,..."></textarea>
                            </div>
                        </div>

                        {{-- Cột 2: Quy trình duyệt --}}
                        <div class="col-lg-4 ps-lg-4 border-start" x-show="showProcessColumn" x-transition.opacity.duration.300ms>
                            <label class="form-label fw-bold text-primary small text-uppercase mb-3"><i
                                    class="fas fa-sitemap me-2"></i>Quy trình duyệt <span
                                    class="text-danger">*</span></label>

                            <div class="pe-2" style="max-height: 80vh; overflow-y: auto;">
                                <template x-for="(step, index) in steps" :key="step.id">
                                    <div
                                        class="card bg-light border border-secondary border-opacity-25 shadow-sm mb-3 position-relative">
                                        <div class="card-body p-3">
                                            <div
                                                class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                <div class="d-flex align-items-center flex-grow-1 me-3">
                                                    <span class="badge bg-primary rounded-circle p-2 me-2"
                                                        style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"
                                                        x-text="index + 1"></span>
                                                    <input type="text"
                                                        class="form-control form-control-sm border-0 bg-white fw-bold shadow-sm"
                                                        placeholder="Tên bước (tuỳ chọn)" x-model="step.name"
                                                        :name="`steps[${index}][name]`">
                                                </div>
                                                <button type="button"
                                                    class="btn btn-sm btn-light text-danger p-1 border rounded shadow-sm"
                                                    @click="removeStep(index)" x-show="steps.length > 1"
                                                    title="Xóa bước này">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1 fw-bold">Hình thức
                                                    duyệt</label>
                                                <select class="form-select form-select-sm border-0 shadow-sm"
                                                    x-model="step.type" :name="`steps[${index}][type]`">
                                                    <option value="or">1 Người duyệt (Chỉ cần 1 người xác nhận)
                                                    </option>
                                                    <option value="and">Tất cả duyệt (Cần toàn bộ xác nhận)</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1 fw-bold">Người duyệt
                                                    <span class="text-danger">*</span></label>
                                                <select multiple class="form-control border-0 shadow-sm"
                                                    :name="`steps[${index}][approvers][]`" required
                                                    x-init="$nextTick(() => {
                                                        if ($el.tomselect) {
                                                            $el.tomselect.destroy();
                                                        }
                                                        new TomSelect($el, {
                                                            plugins: ['remove_button'],
                                                            options: allUsers.map(u => ({ value: u.id, text: u.name })),
                                                            items: step.approvers,
                                                            placeholder: 'Chọn người duyệt...',
                                                            onChange: function(val) {
                                                                step.approvers = val ? (Array.isArray(val) ? val : [val]) : [];
                                                            }
                                                        });
                                                    });"></select>
                                            </div>

                                            <div class="row align-items-start mt-3 bg-white p-2 border rounded mx-0">
                                                <div class="col-12 mb-2">
                                                    <label class="form-label small text-muted mb-1"><i
                                                            class="fas fa-coins me-1 text-warning"></i>Kinh phí đề xuất
                                                        (Tuỳ chọn)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text"
                                                            class="form-control border-secondary border-opacity-25"
                                                            x-model="step.displayAmount"
                                                            @input="formatStepAmount(index, $event.target.value)"
                                                            placeholder="Vd: 50,000,000">
                                                        <span
                                                            class="input-group-text bg-light fw-bold text-muted border-secondary border-opacity-25">VNĐ</span>
                                                    </div>
                                                    <input type="hidden" :name="`steps[${index}][amount]`"
                                                        :value="step.rawAmount">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small text-muted mb-1"><i
                                                            class="fas fa-paperclip me-1 text-info"></i>Tài liệu đính
                                                        kèm (Tuỳ chọn)</label>
                                                    <input type="file" :name="`steps[${index}][files][]`"
                                                        class="form-control form-control-sm border-secondary border-opacity-25"
                                                        multiple accept="*/*">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button"
                                class="btn btn-outline-primary border-dashed w-100 mt-2 py-2 fw-bold"
                                @click="addStep()">
                                <i class="fas fa-plus-circle me-1"></i> Thêm Bước phê duyệt
                            </button>
                        </div>

                        {{-- Cột 3: Tạo Tài Liệu Tự Động --}}
                        <div :class="showProcessColumn ? 'col-lg-3' : 'col-lg-4'" class="ps-lg-4 border-start" style="transition: all 0.3s ease;">
                            <label class="form-label fw-bold text-muted small text-uppercase mb-3"><i
                                    class="fas fa-magic text-warning me-2"></i>Điền file tự động</label>

                            <div
                                class="card bg-light border border-warning border-opacity-25 shadow-sm mb-3 position-relative">
                                <div class="card-body p-3">
                                    <p class="small text-muted mb-3">Tính năng này giúp bạn chọn một biểu mẫu Word có
                                        sẵn trong hệ thống và tự động điền các thông tin của Tàu cá đang chọn.</p>

                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1 fw-bold">Chọn Biểu mẫu <i
                                                class="fas fa-spinner fa-spin ms-1"
                                                x-show="loadingTemplates"></i></label>
                                        <select id="template-select" class="form-control border-0 shadow-sm"
                                            x-model="selectedTemplate" x-init="initTemplateSelect($el);">
                                            <option value="">-- Chọn Mẫu Word --</option>
                                        </select>
                                    </div>

                                    <div class="alert alert-warning p-2 small border-warning border-opacity-25 mb-3"
                                        style="font-size: 0.75rem;"
                                        x-show="category !== 'Đăng kiểm tàu' || (!selectedShipId && !isCreatingShip)">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Bạn cần chọn Phân loại "Đăng
                                        kiểm tàu" và chọn một Tàu cá trước khi điền dữ liệu.
                                    </div>

                                    <button type="button" class="btn btn-warning w-100 text-white fw-bold shadow-sm"
                                        style="font-size: 0.85rem;" @click="generateTemplateFile()"
                                        :disabled="!selectedTemplate || category !== 'Đăng kiểm tàu' || (!selectedShipId && !
                                            isCreatingShip) || isGeneratingTemplate">
                                        <span x-show="!isGeneratingTemplate"><i class="fas fa-bolt me-1"></i> Điền Dữ
                                            Liệu & Đính Kèm</span>
                                        <span x-show="isGeneratingTemplate"><i
                                                class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...</span>
                                    </button>

                                    <div class="mt-3 pt-3 border-top border-warning border-opacity-25"
                                        x-show="generatedFiles.length > 0" style="display: none;">
                                        <label class="form-label small text-muted mb-2 fw-bold"><i
                                                class="fas fa-file-word text-primary me-1"></i> File đã tạo (Sẽ tự đính
                                            kèm vào Bước 1)</label>
                                        <ul class="list-group list-group-flush small" style="font-size: 0.8rem;">
                                            <template x-for="(file, index) in generatedFiles" :key="index">
                                                <li
                                                    class="list-group-item px-0 py-1 bg-transparent d-flex justify-content-between align-items-center border-0">
                                                    <a :href="file.url" target="_blank" class="text-truncate"
                                                        style="max-width: 80%;" title="Nhấn để xem trước file"><i
                                                            class="fas fa-file-word ms-1 me-1 text-primary"></i> <span
                                                            x-text="file.filename"></span></a>
                                                    <button type="button" class="btn btn-sm text-danger p-0"
                                                        title="Xóa file này"
                                                        @click="generatedFiles.splice(index, 1); updateHiddenFileInput()"><i
                                                            class="fas fa-times"></i></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="small text-muted p-2 bg-light rounded border border-dashed">
                                <i class="fas fa-info-circle me-1 text-primary"></i> <b>Lưu ý:</b>
                                <ul class="mb-0 ps-3 mt-1" style="font-size: 0.75rem;">
                                    <li>Sau khi tạo, file tự động được đính kèm vào <b>Bước 1</b>.</li>
                                    <li>Biểu mẫu cần chứa các biến dạng <code>${registration_number}</code>,
                                        <code>${owner_name}</code>,...</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm"
                            data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold"><i
                                class="fas fa-paper-plane me-2"></i> Gửi đề xuất</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
