@extends('layouts.admin')

@section('title', 'KPI Nhân Viên')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Hiệu suất & Hoa hồng CTV</h1>
            <p class="text-muted small mb-0">Theo dõi tàu phụ trách và doanh số của từng nhân viên / cộng tác viên</p>
        </div>
    </div>

    {{-- Flash Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="d-flex mb-4">
        <ul class="nav nav-pills rounded-pill bg-white shadow-sm p-2 border" id="kpiTabs" role="tablist">
            <li role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2 fw-bold d-flex align-items-center custom-pill-btn"
                    id="tab-performance" data-bs-toggle="tab" data-bs-target="#pane-performance" type="button"
                    role="tab">
                    <i class="fas fa-chart-bar me-2"></i> Hiệu suất
                </button>
            </li>
            <li role="presentation">
                <button
                    class="nav-link rounded-pill px-4 py-2 fw-bold d-flex align-items-center ms-1 custom-pill-btn text-muted"
                    id="tab-commission" data-bs-toggle="tab" data-bs-target="#pane-commission" type="button"
                    role="tab">
                    <i class="fas fa-percent me-2"></i> Cài đặt Hoa hồng
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="kpiTabContent">

        {{-- ===== TAB 1: HIỆU SUẤT ===== --}}
        <div class="tab-pane fade show active" id="pane-performance" role="tabpanel">
            <div class="row g-4">
                {{-- CỘT TRÁI: Danh sách nhân viên --}}
                <div class="col-md-5 col-lg-4">
                    <div class="tech-card h-100">
                        <div class="tech-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users me-2"></i>Danh sách Nhân viên & CTV</span>
                            @can('reset_kpi')
                                <button type="button" onclick="confirmResetKpi()"
                                    class="btn btn-sm btn-danger rounded-pill px-3 fw-bold d-flex align-items-center gap-1"
                                    title="Reset KPI tất cả nhân viên về 0">
                                    <i class="fas fa-redo"></i>
                                    <span class="d-none d-sm-inline">Reset KPI</span>
                                </button>
                            @endcan
                        </div>
                        {{-- Form Reset (ẩn) --}}
                        <form id="form-reset-kpi" action="{{ route('admin.kpi.reset') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <div class="card-body p-3" style="max-height: 80vh; overflow-y: auto;">
                            @forelse($users as $user)
                                <div class="staff-card d-flex align-items-center p-3 rounded-3 mb-2"
                                    style="cursor:pointer; transition: all 0.2s; border: 2px solid transparent;"
                                    onclick="loadUserDetail({{ $user->id }})" data-user-id="{{ $user->id }}">
                                    <div class="user-avatar me-3 flex-shrink-0"
                                        style="width: 44px; height: 44px; border-radius: 12px; font-size: 1.1rem;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-fill overflow-hidden">
                                        <div class="fw-bold text-truncate" style="font-size: 0.92rem;">{{ $user->name }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            {{ $user->role?->display_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="text-end ms-2 flex-shrink-0">
                                        <div class="fw-bold text-success" style="font-size: 0.85rem;">
                                            {{ number_format($user->commission_total, 0, ',', '.') }} đ
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Hoa hồng</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                                    Chưa có nhân viên nào
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI: Chi tiết nhân viên --}}
                <div class="col-md-7 col-lg-8">
                    {{-- PLACEHOLDER --}}
                    <div id="kpi-placeholder" class="tech-card h-100 d-flex align-items-center justify-content-center"
                        style="min-height: 400px;">
                        <div class="text-center text-muted">
                            <i class="fas fa-hand-pointer fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-0 fw-semibold">Chọn một nhân viên để xem chi tiết</p>
                        </div>
                    </div>

                    {{-- DETAIL PANEL --}}
                    <div id="kpi-detail" class="d-none">

                        {{-- Header nhân viên --}}
                        <div class="tech-card mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div id="detail-avatar" class="user-avatar flex-shrink-0"
                                        style="width: 60px; height: 60px; border-radius: 16px; font-size: 1.6rem;"></div>
                                    <div>
                                        <h5 id="detail-name" class="mb-1 fw-bold"></h5>
                                        <span id="detail-role"
                                            class="badge-tech text-primary bg-primary bg-opacity-10 px-2 py-1 rounded-2"
                                            style="font-size: 0.72rem;"></span>
                                    </div>
                                    <div class="ms-auto">
                                        @can('update_kpi')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm d-flex align-items-center gap-1"
                                                onclick="openAssignShipModal()">
                                                <i class="fas fa-ship"></i>
                                                <span class="d-none d-sm-inline">Phân công Tàu</span>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                {{-- Stats --}}
                                <div class="row g-3 mt-3">
                                    <div class="col-4">
                                        <div class="rounded-3 p-3"
                                            style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0;">
                                            <div class="text-muted small mb-1"><i
                                                    class="fas fa-dollar-sign me-1 text-success"></i> Tổng hoa hồng</div>
                                            <div id="detail-commission" class="fw-bold text-success"
                                                style="font-size: 1.4rem;"></div>
                                            <div id="detail-commission-rate" class="text-muted"
                                                style="font-size: 0.72rem;"></div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="rounded-3 p-3"
                                            style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe;">
                                            <div class="text-muted small mb-1"><i
                                                    class="fas fa-ship me-1 text-primary"></i> Tàu phụ trách</div>
                                            <div id="detail-ships-count" class="fw-bold text-primary"
                                                style="font-size: 1.4rem;"></div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Tàu đang quản lý</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="rounded-3 p-3"
                                            style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a;">
                                            <div class="text-muted small mb-1"><i
                                                    class="fas fa-percentage me-1 text-warning"></i> Tỷ lệ HH</div>
                                            <div id="detail-rate" class="fw-bold text-warning"
                                                style="font-size: 1.4rem;"></div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Mức hoa hồng</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bảng danh sách tàu --}}
                        <div class="tech-card mb-4">
                            <div class="tech-header"
                                style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                                <i class="fas fa-ship me-2"></i>Danh sách tàu đang quản lý
                            </div>
                            <div class="table-responsive">
                                <table class="table table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>Số hiệu</th>
                                            <th>Chủ tàu</th>
                                            <th>Trạng thái</th>
                                            <th>Hạn ĐK</th>
                                            <th class="text-end pe-4">Công nợ</th>
                                            <th class="text-center" style="width: 60px;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-ships-tbody">
                                    </tbody>
                                </table>
                            </div>
                            <div id="detail-ships-empty" class="d-none text-center text-muted py-4">
                                <i class="fas fa-ship fa-2x mb-2 d-block opacity-25"></i>
                                Nhân viên này chưa được phân công tàu nào
                            </div>
                        </div>

                        {{-- Lịch sử Reset KPI --}}
                        @if ($resetLogs->count() > 0)
                            <div class="tech-card">
                                <div class="tech-header"
                                    style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); font-size: 0.8rem; padding: 10px 16px;">
                                    <i class="fas fa-history me-2"></i>Lịch sử Reset KPI
                                    @if ($lastResetAt)
                                        <span class="ms-2 badge bg-white bg-opacity-20 text-white rounded-pill"
                                            style="font-size: 0.7rem;">
                                            Reset gần nhất: {{ $lastResetAt->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead style="font-size: 0.75rem;">
                                            <tr>
                                                <th class="ps-3">Thời gian</th>
                                                <th>Người reset</th>
                                                <th>Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 0.78rem;">
                                            @foreach ($resetLogs as $log)
                                                <tr>
                                                    <td class="ps-3 text-muted">{{ $log->reset_at->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="fw-bold">{{ $log->resetByUser->name ?? 'N/A' }}</td>
                                                    <td class="text-muted">{{ $log->note ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TAB 2: CÀI ĐẶT HOA HỒNG ===== --}}
        <div class="tab-pane fade" id="pane-commission" role="tabpanel">
            <div class="tech-card">
                <div class="tech-header"
                    style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-percent me-2 bg-white bg-opacity-25 rounded-circle p-2"
                                style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                            Cài đặt Tỷ lệ Hoa hồng
                        </h6>
                        <span class="badge bg-white bg-opacity-20 text-white fw-bold px-3 py-2 rounded-pill"
                            style="font-size: 0.75rem;">
                            <i class="fas fa-info-circle me-1"></i> Nhập % và bấm Lưu cho từng nhân viên
                        </span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th>Nhân viên</th>
                                <th>Chức vụ</th>
                                <th class="text-center" style="width: 120px;">Tàu quản lý</th>
                                <th class="text-end" style="width: 160px;">Tổng doanh thu</th>
                                <th class="text-center" style="width: 200px;">Tỷ lệ Hoa hồng (%)</th>
                                <th class="text-center" style="width: 100px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $i => $user)
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="user-avatar flex-shrink-0"
                                                style="width: 38px; height: 38px; border-radius: 10px; font-size: 0.9rem;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <div class="text-muted small">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary fw-bold rounded-pill px-3 py-1">
                                            {{ $user->role?->display_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">{{ $user->managed_ships_count }}</span>
                                        <span class="text-muted small ms-1">tàu</span>
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="fw-bold text-dark">{{ number_format($user->revenue_total, 0, ',', '.') }}
                                            ₫</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.kpi.commission.update', $user) }}" method="POST"
                                            class="d-flex align-items-center justify-content-center gap-2 commission-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <input type="number" name="commission_rate"
                                                    class="form-control text-center fw-bold border-success"
                                                    value="{{ number_format($user->commission_rate, 2, '.', '') }}"
                                                    min="0" max="100" step="0.5"
                                                    style="border-right: none;">
                                                <span
                                                    class="input-group-text bg-success text-white border-success fw-bold">%</span>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-success rounded-pill px-3 fw-bold btn-save-commission"
                                            data-target="{{ $user->id }}" title="Lưu tỷ lệ hoa hồng">
                                            <i class="fas fa-save me-1"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-users fa-2x mb-2 d-block opacity-25 text-muted"></i>
                                        <p class="text-muted mb-0">Chưa có nhân viên nào</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Summary footer --}}
                <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center bg-light">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1 text-warning"></i>
                        Tỷ lệ hoa hồng mặc định: <strong>5%</strong>. Thay đổi sẽ ảnh hưởng đến tính toán KPI ngay lập tức.
                    </small>
                    <small class="text-muted fw-bold">Tổng: {{ $users->count() }} nhân viên</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Phân công Tàu --}}
    <div class="modal fade" id="assignShipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form id="assignShipForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 px-4 py-4"
                        style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); border-radius: 16px 16px 0 0;">
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-1">
                                <i class="fas fa-ship me-2"></i>Phân công Tàu phụ trách
                            </h5>
                            <p class="small text-white-50 mb-0">Cập nhật danh sách tàu cho: <strong
                                    id="assign-modal-username" class="text-white"></strong></p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <input type="text" id="kpiShipSearch" class="form-control modern-form-control"
                                placeholder="Tìm kiếm theo số hiệu hoặc chủ tàu...">
                        </div>

                        <div class="row g-2" id="kpiShipList" style="max-height: 400px; overflow-y: auto;">
                            @forelse($allShips as $ship)
                                <div class="col-md-6 kpi-ship-item"
                                    data-search="{{ strtolower($ship->registration_number . ' ' . $ship->owner_name) }}">
                                    <label
                                        class="d-flex align-items-center gap-3 p-3 rounded-3 border ship-checkbox-label bg-white"
                                        style="cursor:pointer; transition: all 0.2s; border-color: #eaecf4 !important;">
                                        <input type="checkbox" name="ship_ids[]" value="{{ $ship->id }}"
                                            class="form-check-input kpi-ship-check flex-shrink-0 mt-0"
                                            id="chk-ship-{{ $ship->id }}">
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-truncate"
                                                style="font-size: 0.85rem; color: #4e73df;">
                                                {{ $ship->registration_number }}
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 0.78rem;">
                                                {{ $ship->owner_name ?? 'Chưa có chủ tàu' }}
                                            </div>
                                        </div>
                                        <span class="ms-auto flex-shrink-0">
                                            @if ($ship->status === 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill"
                                                    style="font-size: 0.65rem;">Đạt chuẩn</span>
                                            @elseif($ship->status === 'expired')
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"
                                                    style="font-size: 0.65rem;">Hết hạn</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill"
                                                    style="font-size: 0.65rem;">{{ $ship->status }}</span>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-4">
                                    <i class="fas fa-ship fa-2x mb-2 d-block opacity-25"></i>
                                    Chưa có tàu nào trong hệ thống
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4 bg-white" style="border-radius: 0 0 16px 16px;">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy
                            bỏ</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-1"></i> Lưu danh sách
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .custom-pill-btn {
            transition: all 0.2s ease-in-out;
            color: #5a5c69;
            /* text-muted equivalent */
        }

        .custom-pill-btn:not(.active):hover {
            background-color: #eff2f7;
            color: #4e73df;
        }

        .custom-pill-btn.active {
            background-color: #4e73df !important;
            color: white !important;
        }

        .staff-card:hover {
            background: #f8f9fc;
            border-color: #4e73df !important;
        }

        .staff-card.active-staff {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-color: #4e73df !important;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .commission-form input[type="number"]:focus {
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
            border-color: #198754;
        }

        .btn-save-commission {
            transition: all 0.2s;
        }

        .btn-save-commission:hover {
            transform: scale(1.05);
        }
    </style>
@endsection

@section('scripts')
    <script>
        let currentUserId = null;

        function loadUserDetail(userId) {
            if (currentUserId === userId) return;
            currentUserId = userId;

            // Highlight active
            document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('active-staff'));
            document.querySelector(`.staff-card[data-user-id="${userId}"]`)?.classList.add('active-staff');

            // Show loading
            document.getElementById('kpi-placeholder').classList.add('d-none');
            document.getElementById('kpi-detail').classList.remove('d-none');
            document.getElementById('detail-ships-tbody').innerHTML = `
            <tr><td colspan="5" class="text-center py-4 text-muted">
                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
            </td></tr>`;

            fetch(`{{ url('admin/kpi/user') }}/${userId}`)
                .then(r => r.json())
                .then(data => {
                    // Fill header
                    document.getElementById('detail-avatar').textContent = data.user.avatar_char;
                    document.getElementById('detail-name').textContent = data.user.name;
                    document.getElementById('detail-role').textContent = data.user.role;
                    document.getElementById('detail-commission').textContent =
                        new Intl.NumberFormat('vi-VN').format(data.user.commission) + ' đ';
                    document.getElementById('detail-commission-rate').textContent =
                        'Mức ' + data.user.commission_rate + '%';
                    document.getElementById('detail-rate').textContent = data.user.commission_rate + '%';
                    document.getElementById('detail-ships-count').textContent = data.ships.length;

                    // Store locally for assigning later
                    currentShipsData = data.ships;

                    // Fill ships table
                    const tbody = document.getElementById('detail-ships-tbody');
                    const empty = document.getElementById('detail-ships-empty');
                    if (data.ships.length === 0) {
                        tbody.innerHTML = '';
                        empty.classList.remove('d-none');
                    } else {
                        empty.classList.add('d-none');
                        tbody.innerHTML = data.ships.map(ship => {
                            const statusColor = ship.status === 'active' ? 'success' : (ship.status ===
                                'expired' ? 'danger' : 'warning');
                            const statusLabel = ship.status === 'active' ? 'Đạt chuẩn' : (ship.status ===
                                'expired' ? 'Hết hạn' : ship.status);
                            const debt = ship.debt > 0 ?
                                `<span class="text-danger fw-bold">${new Intl.NumberFormat('vi-VN').format(ship.debt)} đ</span>` :
                                '<span class="text-muted">-</span>';
                            return `<tr>
                            <td class="fw-bold text-primary ps-4">${ship.registration_number}</td>
                            <td class="text-muted">${ship.owner_name ?? '-'}</td>
                            <td><span class="status-dot bg-${statusColor}"></span>${statusLabel}</td>
                            <td>${ship.expiration_date ?? '-'}</td>
                                <td class="text-end pe-4">${debt}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm text-danger border-0 bg-transparent rounded-circle" 
                                        onclick="unassignShip(${ship.id}, '${ship.registration_number}')" title="Bỏ phân công">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>`;
                        }).join('');
                    }
                })
                .catch(() => {
                    document.getElementById('detail-ships-tbody').innerHTML =
                        `<tr><td colspan="6" class="text-center py-3 text-danger">Lỗi tải dữ liệu</td></tr>`;
                });
        }

        let currentShipsData = [];

        function openAssignShipModal() {
            if (!currentUserId) return;

            // Cập nhật tên user trên Modal
            const userName = document.getElementById('detail-name').textContent;
            document.getElementById('assign-modal-username').textContent = userName;

            // Reset tất cả checkbox
            document.querySelectorAll('.kpi-ship-check').forEach(cb => {
                cb.checked = false;
                updateShipHighlight(cb);
            });

            // Check những tàu đang quản lý
            currentShipsData.forEach(ship => {
                const cb = document.getElementById(`chk-ship-${ship.id}`);
                if (cb) {
                    cb.checked = true;
                    updateShipHighlight(cb);
                }
            });

            // Cập nhật action cho form
            const form = document.getElementById('assignShipForm');
            form.action = `{{ url('admin/kpi') }}/${currentUserId}/ships`;

            // Hiện Modal
            const modal = new bootstrap.Modal(document.getElementById('assignShipModal'));
            modal.show();
        }

        window.unassignShip = function(shipId, regNumber) {
            if (!currentUserId) return;

            Swal.fire({
                title: 'Bỏ phân công?',
                text: `Bạn có chắc muốn bỏ phân công tàu ${regNumber} khỏi nhân viên này?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelectorAll('.kpi-ship-check').forEach(cb => cb.checked = false);
                    currentShipsData.forEach(ship => {
                        if (ship.id !== shipId) {
                            const cb = document.getElementById(`chk-ship-${ship.id}`);
                            if (cb) cb.checked = true;
                        }
                    });
                    const form = document.getElementById('assignShipForm');
                    form.action = `{{ url('admin/kpi') }}/${currentUserId}/ships`;
                    form.submit();
                }
            });
        };

        // Highlight when checked
        function updateShipHighlight(cb) {
            const label = cb.closest('label');
            if (label) {
                label.style.borderColor = cb.checked ? '#4e73df' : '#eaecf4';
                label.style.background = cb.checked ? '#eff6ff' : '';
            }
        }

        document.querySelectorAll('.kpi-ship-check').forEach(cb => {
            cb.addEventListener('change', () => updateShipHighlight(cb));
        });

        // Tìm kiếm trên Modal
        const kpiShipSearch = document.getElementById('kpiShipSearch');
        if (kpiShipSearch) {
            kpiShipSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.kpi-ship-item').forEach(item => {
                    const match = !q || item.dataset.search.includes(q);
                    item.style.display = match ? '' : 'none';
                });
            });
        }

        // Confirm Reset KPI
        function confirmResetKpi() {
            Swal.fire({
                title: 'Reset KPI tất cả nhân viên?',
                html: `<p class="text-muted mb-2">Sau khi reset, KPI của <strong>tất cả nhân viên</strong> sẽ về 0.<br>Dữ liệu hiện tại sẽ được <strong class="text-success">lưu log</strong> trước khi reset.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-redo me-1"></i>Reset ngay',
                cancelButtonText: 'Hủy',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-reset-kpi').submit();
                }
            });
        }

        // Handle save commission buttons
        document.querySelectorAll('.btn-save-commission').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-target');
                const form = this.closest('tr').querySelector('.commission-form');
                const input = form.querySelector('input[name="commission_rate"]');
                const val = parseFloat(input.value);

                if (isNaN(val) || val < 0 || val > 100) {
                    input.classList.add('is-invalid');
                    return;
                }
                input.classList.remove('is-invalid');

                // Visual feedback
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';

                form.submit();
            });
        });

        // Auto-open commission tab if URL hash is #commission
        if (window.location.hash === '#commission') {
            document.getElementById('tab-commission')?.click();
        }
    </script>
@endsection
