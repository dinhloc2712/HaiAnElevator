@extends('layouts.admin')

@section('title', 'Trung tâm Chăm sóc Tàu (CRM)')

@section('content')

{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Trung tâm Chăm sóc Tàu</h1>
        <p class="mb-0 text-muted small">Quản lý danh sách tàu và thông tin liên hệ chủ tàu</p>
    </div>
</div>

<div class="tech-card h-100 mb-4 rounded-4" style="overflow: visible;">
    <div class="tech-header rounded-top-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 18px 25px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-ship me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Tra cứu Tàu thuyền (CRM)
            </h6>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Per page selector (standalone form) --}}
                <form method="GET" action="{{ route('admin.crm.index') }}" id="perPageForm">
                    @foreach(request()->except('per_page') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                        <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                        <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4" style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </form>

                {{-- Advanced Filter Toggle Button --}}
                <button class="btn bg-white rounded-pill fw-bold text-dark px-3 py-2 shadow-sm d-flex align-items-center gap-2" type="button" id="btnAdvancedFilter" onclick="toggleAdvancedFilter()">
                    <i class="fas fa-sliders-h text-primary"></i>
                    @php
                        $hasAdvancedFilter = request()->hasAny(['adv_registration','adv_owner','adv_status','adv_expiration','adv_main_occupation','adv_secondary_occupation','adv_usage','adv_power_min','adv_power_max','adv_sort_expiration', 'adv_province', 'adv_ward']);
                    @endphp
                    @if($hasAdvancedFilter)
                        <span class="text-primary fw-bold">Bộ lọc <span class="badge bg-primary text-white rounded-pill ms-1" style="font-size:0.65rem;">ON</span></span>
                    @else
                        Bộ lọc nâng cao
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Advanced Filter Panel (ProTechUI) --}}
    <div id="advancedFilterPanel" class="rounded-bottom-4" style="display: {{ $hasAdvancedFilter ? 'block' : 'none' }}; background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%); border-bottom: 1px solid #dde3f7;">
        <form method="GET" action="{{ route('admin.crm.index') }}" id="advancedFilterForm">
            <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

            <div class="px-4 pt-4 pb-3">
                {{-- Panel Header --}}
                <div class="d-flex align-items-center mb-3 gap-2">
                    <div class="rounded-2 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                        <i class="fas fa-sliders-h text-primary" style="font-size:.75rem;"></i>
                    </div>
                    <span class="fw-bold text-primary" style="font-size:.85rem; letter-spacing:.3px;">BỘ LỌC NÂNG CAO</span>
                    @if($hasAdvancedFilter)
                    <span class="badge rounded-pill ms-1" style="background: linear-gradient(135deg,#4e73df,#224abe); font-size:.65rem;">
                        <i class="fas fa-check me-1"></i>Đang lọc
                    </span>
                    @endif
                </div>

                {{-- Grid --}}
                <div class="row g-3">

                    {{-- Số đăng ký --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-hashtag me-1 text-primary opacity-75"></i>Số đăng ký
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_registration" class="filter-input" placeholder="VD: NA-90001-TS" value="{{ request('adv_registration') }}">
                        </div>
                    </div>

                    {{-- Chủ phương tiện --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-user me-1 text-indigo opacity-75"></i>Chủ phương tiện
                        </label>
                        <div class="filter-input-wrap">
                            <input type="text" name="adv_owner" class="filter-input" placeholder="Tên, SĐT hoặc Email chủ tàu..." value="{{ request('adv_owner') }}">
                        </div>
                    </div>
                    
                    {{-- Tình trạng tàu --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-traffic-light me-1 text-warning opacity-75"></i>Tình trạng tàu
                        </label>
                        <div class="filter-input-wrap">
                            <select name="adv_status" class="filter-select">
                                <option value="">-- Tất cả --</option>
                                <option value="active" {{ request('adv_status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="suspended" {{ request('adv_status') == 'suspended' ? 'selected' : '' }}>Đình chỉ</option>
                                <option value="processing" {{ request('adv_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            </select>
                        </div>
                    </div>

                    {{-- Hạn đăng kiểm --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">
                            <i class="fas fa-calendar-check me-1 text-success opacity-75"></i>Hạn đăng kiểm
                        </label>
                        <div class="filter-input-wrap">
                            <select name="adv_expiration" class="filter-select">
                                <option value="">-- Tất cả --</option>
                                <option value="valid" {{ request('adv_expiration') == 'valid' ? 'selected' : '' }}>Còn hạn</option>
                                <option value="expiring_soon" {{ request('adv_expiration') == 'expiring_soon' ? 'selected' : '' }}>Sắp hết hạn (30 ngày)</option>
                                <option value="expired" {{ request('adv_expiration') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                            </select>
                        </div>
                    </div>

                </div>{{-- /row --}}
            </div>

            {{-- Action Bar --}}
            <div class="px-4 py-3 d-flex justify-content-between align-items-center rounded-bottom-4" style="background: rgba(255,255,255,0.7); border-top: 1px solid #dde3f7;">
                <span class="text-muted small">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Điền một hoặc nhiều điều kiện rồi nhấn <strong>Tìm kiếm</strong>.
                </span>
                <div class="d-flex gap-2">
                    @if($hasAdvancedFilter)
                    <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">
                        <i class="fas fa-undo me-1"></i>Xóa lọc
                    </a>
                    @endif
                    <button type="submit" class="btn btn-sm rounded-pill px-5 fw-bold text-white d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); box-shadow: 0 4px 12px rgba(78,115,223,.35);">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row" x-data="crmApp(initialShips)">
    <!-- Left Sidebar: Ship List -->
    <div class="col-md-3 col-12 mb-4">
        <div class="tech-card h-100 rounded-4 border-0 shadow-sm" style="overflow: hidden;">
            <div class="tech-header justify-content-between" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; border-radius: 0;">
                <span class="text-white fw-bold">Danh sách Tàu</span>
                <span class="badge bg-white text-primary rounded-pill">{{ $ships->total() }}</span>
            </div>
            <div class="card-body p-0 d-flex flex-column h-100 bg-white">
                <div class="list-group list-group-flush flex-grow-1" style="overflow-y: auto; max-height: calc(100vh - 280px);">
                    <template x-for="ship in ships" :key="ship.id">
                        <button type="button" 
                                class="list-group-item list-group-item-action border-0 py-3 border-bottom"
                                :class="{ 'active-process': activeShip && activeShip.id === ship.id }"
                                @click="selectShip(ship)">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold" :class="{ 'text-primary': activeShip && activeShip.id === ship.id }" x-text="ship.registration_number"></h6>
                                <span class="badge" :class="getExpirationClass(ship)" x-text="getExpirationText(ship)"></span>
                            </div>
                            <div class="d-flex align-items-center text-muted small mt-2">
                                <i class="fas fa-user-tie me-2" :class="{ 'text-primary': activeShip && activeShip.id === ship.id }" style="width: 14px;"></i>
                                <span class="text-truncate" x-text="ship.owner_name || 'Chưa cập nhật'"></span>
                            </div>
                            <div class="d-flex w-100 justify-content-between align-items-center mt-2">
                                <span class="small text-muted" x-text="ship.usage || 'Tàu cá'"></span>
                                <i class="fas fa-chevron-right small transition-transform" :class="{ 'text-primary transform-rotate-90': activeShip && activeShip.id === ship.id, 'text-muted': !activeShip || activeShip.id !== ship.id }"></i>
                            </div>
                        </button>
                    </template>
                    
                    @if($ships->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-ship fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">Không tìm thấy tàu nào</p>
                    </div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>

    <!-- Right Main Area: Details & Assistant -->
    <div class="col-md-9 col-12">
        <!-- Empty State -->
        <div class="text-center py-5 tech-card rounded-4 border-0 shadow-sm d-flex flex-column align-items-center justify-content-center bg-white" 
             style="min-height: 400px;"
             x-show="!activeShip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             :class="{ 'd-flex': !activeShip, 'd-none': activeShip }">
            <div class="bg-light rounded-circle p-4 mb-3">
                <i class="fas fa-clipboard-list fa-4x text-gray-300"></i>
            </div>
            <h4 class="mt-2 text-gray-600 fw-bold">Chọn một phương tiện để xem chi tiết</h4>
            <p class="text-muted">Thông tin liên hệ và trạng thái đăng kiểm sẽ hiển thị tại đây.</p>
        </div>

        <!-- Detail Interface -->
        <div x-show="activeShip" style="display: none;">
            
            <!-- Owner Info Card -->
            <div class="tech-card mb-4 rounded-4 border-0 shadow-sm" style="overflow: hidden;">
                <div class="tech-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 0;">
                    <div class="text-white fw-bold">
                        <i class="fas fa-user-tie me-2"></i> Thông tin Chủ Tàu
                    </div>
                </div>
                <div class="card-body p-4 bg-white">
                    <h4 class="fw-bold text-gray-800 mb-4 text-uppercase" x-text="activeShip?.owner_name || 'CHƯA CẬP NHẬT'"></h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                    <i class="fas fa-phone-alt fa-lg text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-weight-bold mb-1">Số điện thoại</h6>
                                    <p class="text-gray-800 fw-bold mb-0" x-text="activeShip?.owner_phone || '—'"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                    <i class="fas fa-map-marker-alt fa-lg text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-weight-bold mb-1">Địa chỉ</h6>
                                    <p class="text-gray-800 fw-bold mb-0" x-text="activeShip?.address || '—'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ship Spec Card -->
            <div class="tech-card mb-4 rounded-4 border-0 shadow-sm" style="overflow: hidden;">
                <div class="tech-header justify-content-between align-items-center border-bottom text-white" style="background-color: #f8f9fc; border-radius: 0;">
                    <div class="fw-bold"><i class="fas fa-ship text-white me-2"></i> Chi tiết Phương tiện</div>
                    <span class="badge" :class="getExpirationBadgeClass(activeShip)" x-html="getExpirationBadgeHTML(activeShip)"></span>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                <i class="fas fa-ship fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="font-weight-bold text-gray-800 mb-1" x-text="activeShip?.registration_number"></h4>
                                <p class="text-muted mb-0" x-text="activeShip?.usage || 'Tàu cá'"></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="text-muted font-weight-bold mb-1">Hạn hiệu lực ĐK</h6>
                            <div class="d-flex align-items-center gap-2 justify-content-end">
                                <i class="far fa-calendar-alt text-gray-500"></i>
                                <span class="fw-bold fs-5 text-gray-800" x-text="formatDate(activeShip?.expiration_date)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Assistant Card -->
            <div class="tech-card rounded-4 border-0 shadow-sm" style="overflow: hidden;">
                <div class="tech-header d-flex justify-content-between align-items-center text-white" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 0;">
                    <div class="fw-bold">
                        <i class="fas fa-robot me-2"></i> Trợ lý ảo AI - Nhắc nhở
                    </div>
                </div>
                <div class="card-body p-4 bg-light-50">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-gray-800 font-weight-bold mb-0">Soạn thảo nội dung gửi SMS/Zalo</h5>
                        <button type="button" class="btn btn-sm text-white fw-bold px-3 shadow-sm rounded border-0" style="background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);">
                            <i class="fas fa-magic me-1"></i> Soạn nội dung tự động qua AI
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <textarea class="form-control border-0 shadow-sm p-3" rows="4" placeholder="Nội dung nhắc nhở đăng kiểm sẽ hiển thị ở đây. Bạn cũng có thể tự tuỳ chỉnh nội dung này." style="resize: none;"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-secondary rounded fw-bold border bg-white shadow-sm">
                            <i class="fas fa-sms me-1 text-primary"></i> Gửi SMS
                        </button>
                        <button class="btn btn-outline-primary rounded fw-bold border bg-white shadow-sm d-flex align-items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" style="width: 16px; margin-right: 6px;"> Gửi Zalo
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

{{-- Pagination ngoài --}}
@if($ships->hasPages())
<div class="mt-3">
    {{ $ships->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

@section('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // ---- Advanced Filter Logic ----
    function toggleAdvancedFilter() {
        const panel = document.getElementById('advancedFilterPanel');
        if (!panel) return;
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';
    }

    // Alpine JS Main CRM Logics
    const initialShips = @json($ships->items());
    const today = new Date();
    today.setHours(0,0,0,0);

    document.addEventListener('alpine:init', () => {
        Alpine.data('crmApp', (initialShips) => ({
            ships: initialShips,
            activeShip: null,

            init() {
                if (this.ships.length > 0) {
                    this.activeShip = this.ships[0];
                }
            },

            selectShip(ship) {
                this.activeShip = ship;
            },
            
            formatDate(dateStr) {
                if (!dateStr) return 'Chưa có thông tin';
                try {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('vi-VN');
                } catch(e) {
                    return dateStr;
                }
            },

            getExpirationStatus(ship) {
                if (!ship.expiration_date) return 'unknown';
                
                const expDate = new Date(ship.expiration_date);
                expDate.setHours(0,0,0,0);
                
                const thirtyDaysFromNow = new Date(today);
                thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
                
                if (expDate < today) return 'expired';
                if (expDate <= thirtyDaysFromNow) return 'expiring';
                return 'valid';
            },

            getExpirationClass(ship) {
                const status = this.getExpirationStatus(ship);
                if (status === 'expired') return 'bg-danger text-white';
                if (status === 'expiring') return 'bg-warning text-dark';
                if (status === 'valid') return 'bg-success text-white';
                return 'bg-secondary text-white';
            },

            getExpirationText(ship) {
                const status = this.getExpirationStatus(ship);
                if (status === 'expired') return 'Hết hạn';
                if (status === 'expiring') return 'Sắp hết';
                if (status === 'valid') return 'Còn hạn';
                return 'Chưa ĐK';
            },
            
            getExpirationBadgeClass(ship) {
                const status = this.getExpirationStatus(ship);
                if (status === 'valid') return 'bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-lg';
                if (status === 'expiring') return 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-lg shadow-sm';
                if (status === 'expired') return 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-lg';
                return 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-lg';
            },
            
            getExpirationBadgeHTML(ship) {
                const status = this.getExpirationStatus(ship);
                if (status === 'valid') return '<i class="fas fa-check-circle me-1"></i> Còn hạn / Đạt chuẩn';
                if (status === 'expiring') return '<i class="fas fa-exclamation-triangle me-1"></i> Cảnh báo sắp hết';
                if (status === 'expired') return '<i class="fas fa-times-circle me-1"></i> Quá hạn';
                return '<i class="fas fa-question-circle me-1"></i> Chưa xác định';
            }
        }));
    });
</script>

<style>
    /* Utility classes matching Inspection Processes */
    .bg-light-50 { background-color: #f8f9fa; }
    .active-process {
        background-color: #f0f7ff !important;
        border-left: 4px solid #3b82f6 !important;
    }
    .list-group::-webkit-scrollbar { width: 4px; }
    .list-group::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    .tech-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: 1px solid #e3e6f0;
        overflow: hidden;
    }
    .tech-header {
        padding: 1rem 1.25rem;
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 700;
        color: #4e73df;
    }
    .transition-transform {
        transition: transform 0.2s ease;
    }
    .transform-rotate-90 {
        transform: rotate(90deg);
    }

    /* Advanced filter panel additional styles */
    .filter-label {
        display: block;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .filter-input-wrap {
        position: relative;
    }
    .filter-input,
    .filter-select {
        width: 100%;
        height: 36px;
        padding: 0 12px;
        font-size: .85rem;
        border: 1.5px solid #dde3f7;
        border-radius: 10px;
        background: #fff;
        color: #495057;
        box-shadow: 0 1px 4px rgba(78,115,223,.06);
        transition: border-color .2s, box-shadow .2s;
        outline: none;
        appearance: none;
    }
    .filter-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%234e73df' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        padding-right: 32px;
    }
    .filter-input:focus,
    .filter-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 3px rgba(78,115,223,.15);
    }
    .filter-input::placeholder { color: #adb5bd; }
    #advancedFilterPanel {
        animation: slideDown .2s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
