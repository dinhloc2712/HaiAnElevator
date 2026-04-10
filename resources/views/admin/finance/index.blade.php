@extends('layouts.admin')

@section('title', 'Quản lý Tài chính')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Quản lý Tài chính</h1>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Tổng thu thực tế --}}
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white shadow-sm border-0 h-100 rounded-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-white-50 fw-semibold">Tổng thu thực tế</p>
                    <div class="bg-white bg-opacity-25 rounded px-2 py-1 text-white fw-bold">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h3>
            </div>
        </div>
    </div>

    {{-- Chưa thu --}}
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white shadow-sm border-0 h-100 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-muted fw-semibold">Chưa thu</p>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-2 text-dark">{{ number_format($uncollectedRevenue, 0, ',', '.') }} đ</h3>
                <p class="mb-0 small text-muted">Từ các Đề xuất Tàu cá</p>
            </div>
        </div>
    </div>

    {{-- Đã thu --}}
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white shadow-sm border-0 h-100 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-muted fw-semibold">Đã thu</p>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-2 text-dark">{{ number_format($collectedRevenue, 0, ',', '.') }} đ</h3>
                <p class="mb-0 small text-muted">Các khoản đã thu nạp</p>
            </div>
        </div>
    </div>

    {{-- Tổng chi --}}
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white shadow-sm border-0 h-100 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-muted fw-semibold">Tổng chi</p>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-2 text-dark">{{ number_format($totalExpense, 0, ',', '.') }} đ</h3>
                <p class="mb-0 small text-muted">Các khoản đã chi ra</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Biểu đồ --}}
    <div class="col-xl-8">
        <div class="card bg-white shadow-sm border-0 rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-4">Biểu đồ Doanh thu (Triệu VNĐ)</h6>
                <div class="position-relative" style="height: 250px;">
                    <!-- Simple CSS Bar Chart Implementation matching Mockup -->
                    <div class="d-flex h-100 w-100 align-items-end justify-content-between pb-4 position-absolute" style="padding-left: 40px;">
                        @php
                            $maxData = max(count($chartData) > 0 ? max($chartData) : 1, 240000000); // Scale up to 240M max default
                        @endphp
                        
                        @foreach($chartData as $index => $val)
                            @php
                                $heightPercent = ($val / $maxData) * 100;
                                // Adding a fake top red stub to match user design style "stacked"
                                $redHeight = $heightPercent > 0 ? max(3, $heightPercent * 0.1) : 0; 
                                $blueHeight = $heightPercent - $redHeight;
                            @endphp
                            <div class="d-flex flex-column align-items-center" style="width: 12%; height: 100%;">
                                <div class="w-100 d-flex flex-column justify-content-end" style="height: 100%;">
                                    @if($heightPercent > 0)
                                        <div class="w-100" style="background-color: #ef4444; height: {{ $redHeight }}%;"></div>
                                        <div class="w-100" style="background-color: #3b82f6; height: {{ $blueHeight }}%;"></div>
                                    @endif
                                </div>
                                <div class="mt-2 text-muted fw-semibold small">{{ $chartLabels[$index] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Y-Axis Labels -->
                    <div class="h-100 d-flex flex-column justify-content-between pb-4 position-absolute" style="left: 0; width: 40px; border-right: 1px solid #e2e8f0;">
                        <div class="text-muted small text-end pe-2">240</div>
                        <div class="text-muted small text-end pe-2 position-relative"><div class="position-absolute border-top border-dashed" style="width: calc(100vw - 400px); border-color: #e2e8f0; left: 40px; top: 10px; z-index: 0"></div>180</div>
                        <div class="text-muted small text-end pe-2 position-relative"><div class="position-absolute border-top border-dashed" style="width: calc(100vw - 400px); border-color: #e2e8f0; left: 40px; top: 10px; z-index: 0"></div>120</div>
                        <div class="text-muted small text-end pe-2 position-relative"><div class="position-absolute border-top border-dashed" style="width: calc(100vw - 400px); border-color: #e2e8f0; left: 40px; top: 10px; z-index: 0"></div>60</div>
                        <div class="text-muted small text-end pe-2">0</div>
                    </div>
                    <div class="w-100 position-absolute bottom-0 border-top" style="left: 40px; width: calc(100% - 40px); bottom: 1.8rem !important; border-color: #cbd5e1 !important;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Giao dịch gần đây --}}
    <div class="col-xl-4">
        <div class="card bg-white shadow-sm border-0 rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-4">Giao dịch gần đây</h6>
                
                <div class="d-flex flex-column gap-3">
                    @forelse($recentTransactions as $transaction)
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 fw-bold text-dark">{{ $transaction->reference_id ?: 'Khách Hàng' }}</p>
                                <p class="mb-0 small text-muted">{{ $transaction->description ?: ($transaction->type == 'income' ? 'Phí thu' : 'Chi phí') }}</p>
                            </div>
                            <div class="text-end">
                                @if($transaction->type == 'income')
                                    <p class="mb-0 fw-bold text-success">+{{ number_format($transaction->amount, 0, ',', '.') }} đ</p>
                                @else
                                    <p class="mb-0 fw-bold text-danger">-{{ number_format($transaction->amount, 0, ',', '.') }} đ</p>
                                @endif
                                <p class="mb-0 small text-muted text-opacity-75">{{ $transaction->transaction_date->format('Y-m-d') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                            <p class="small mb-0">Chưa có giao dịch nào</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bảng Danh sách Phiếu --}}
<div class="tech-card mt-4 mb-4">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                <i class="fas fa-file-invoice me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                Danh sách Phiếu
            </h6>

            <form method="GET" action="{{ route('admin.finance.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                {{-- Per Page --}}
                <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                    <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển thị</small>
                    <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4" style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="bg-white rounded-pill shadow-sm" style="flex: 1; min-width: 200px; max-width: 300px;">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3" style="z-index: 5;"></i>
                        <input type="text" name="search" class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2" placeholder="Tìm mã, người nộp/nhận..." value="{{ $search }}">
                        @if($search)
                            <a href="{{ route('admin.finance.index') }}" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted text-decoration-none" style="z-index: 5;"><i class="fas fa-times"></i></a>
                        @endif
                    </div>
                </div>

                {{-- Filter Dropdown --}}
                <div class="dropdown">
                    <button class="btn bg-white rounded-pill fw-bold text-dark px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1 text-secondary"></i> Lọc
                    </button>
                    <div class="dropdown-menu shadow-lg border-0 mt-2 p-3" style="width: 280px;">
                        <h6 class="dropdown-header px-0 text-uppercase fw-bold mb-2 small text-muted">Bộ lọc tìm kiếm</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Loại phiếu</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">-- Tất cả --</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Phiếu Thu</option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Phiếu Chi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Trạng thái</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- Tất cả --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Áp dụng</button>
                        @if(request('type') || request('status'))
                            <a href="{{ route('admin.finance.index') }}" class="btn btn-link btn-sm w-100 mt-1 text-decoration-none text-muted">Xóa bộ lọc</a>
                        @endif
                    </div>
                </div>

                {{-- Add Button --}}
                <a href="{{ route('admin.finance.create') }}" class="text-white fw-bold px-2 text-decoration-none d-flex align-items-center">
                    <i class="fas fa-plus me-1"></i> Thêm mới
                </a>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">MÃ PHIẾU</th>
                        <th>LOẠI PHIẾU</th>
                        <th>NGÀY GHI NHẬN</th>
                        <th>NGƯỜI NỘP/NHẬN</th>
                        <th>SỐ TIỀN</th>
                        <th>HÌNH THỨC</th>
                        <th>TRẠNG THÁI</th>
                        <th>NGƯỜI TẠO</th>
                        <th class="text-end pe-4">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $tx->code ?: '---' }}</td>
                            <td>
                                @if($tx->type == 'income')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1">
                                        <i class="fas fa-arrow-down small"></i> Phiếu Thu
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1">
                                        <i class="fas fa-arrow-up small"></i> Phiếu Chi
                                    </span>
                                @endif
                            </td>
                            <td>{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="fw-bold text-dark">{{ $tx->customer_name ?: '---' }}</td>
                            <td>
                                <span class="fw-bold {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($tx->amount, 0, ',', '.') }} đ
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-tech text-primary bg-primary bg-opacity-10">
                                    <i class="fas {{ $tx->payment_method == 'cash' ? 'fa-money-bill-wave' : 'fa-university' }} me-1"></i>
                                    {{ $tx->payment_method == 'cash' ? 'Tiền mặt' : 'Chuyển khoản' }}
                                </span>
                            </td>
                            <td>
                                @if($tx->status == 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill" style="font-weight: 600; font-size: 0.75rem;">Đã duyệt</span>
                                @elseif($tx->status == 'rejected')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill" style="font-weight: 600; font-size: 0.75rem;">Từ chối</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill" style="font-weight: 600; font-size: 0.75rem;">Chờ duyệt</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning text-white fw-bold shadow-sm" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($tx->user->name ?? 'A', 0, 1)) }}
                                    </span>
                                    <span class="text-dark">{{ $tx->user->name ?? 'Admin' }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.finance.edit', $tx) }}" class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.finance.destroy', $tx) }}" method="POST" class="d-inline-block" id="delete-finance-{{ $tx->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xóa" onclick="confirmDeleteFinance({{ $tx->id }}, '{{ $tx->code }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle p-4 mb-3">
                                        <i class="fas fa-file-invoice fa-3x text-secondary"></i>
                                    </div>
                                    <h6 class="text-muted fw-bold">Không tìm thấy phiếu nào</h6>
                                    <p class="text-muted small mb-0">Thử thay đổi bộ lọc hoặc thêm phiếu mới.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDeleteFinance(id, code) {
        Swal.fire({
            title: 'Xóa phiếu ' + code + '?',
            text: 'Hành động này không thể hoàn tác!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xóa!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-finance-' + id).submit();
            }
        });
    }
</script>
@endsection
