@extends('layouts.admin')

@section('title', 'Chi tiết / Chỉnh sửa Đơn bảo trì')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.maintenance.orders') }}" class="btn btn-sm btn-link text-muted p-0 mb-2 text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h1 class="page-header-title mb-1 d-flex align-items-center gap-2">
            Chi tiết đơn <span class="badge bg-primary fs-6">{{ $order->code }}</span>
        </h1>
    </div>
</div>

<div class="row" x-data="editOrderForm()">
    <div class="col-12">
        <form action="{{ route('admin.maintenance.orders.update', $order->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- General Info Card --}}
            <div class="data-card mb-4">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chung</h5>
                    <div>
                        <select name="status" class="form-select form-select-sm d-inline-block w-auto fw-bold" style="border-radius: 8px;">
                            <option value="draft" {{ $order->status == 'draft' ? 'selected' : '' }}>Lưu nháp</option>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-4 row g-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Khách hàng</label>
                        <select name="building_id" class="form-select bg-light border-0 py-2 rounded-3" x-model="selectedBuildingId" @change="selectedElevatorId = ''" required>
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }} - {{ $building->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Thang máy</label>
                        <select name="elevator_id" class="form-select bg-light border-0 py-2 rounded-3" x-model="selectedElevatorId" required>
                            <option value="">-- Chọn thang máy --</option>
                            <template x-for="elevator in filteredElevators" :key="elevator.id">
                                <option :value="elevator.id" x-text="elevator.code"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Ngày lập đơn</label>
                        <div class="input-group">
                            <input type="date" name="created_date" class="form-control bg-light border-0 py-2 rounded-3" x-model="createdDate" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Services & Parts Card --}}
            <div class="data-card mb-4">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tools me-2 text-primary"></i>Chi tiết dịch vụ & Linh kiện</h5>
                    <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill fw-bold shadow-sm" @click="addItem">
                        <i class="fas fa-plus me-1"></i> Thêm dòng
                    </button>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap align-middle table-hover">
                            <thead class="bg-white">
                                <tr>
                                    <th class="border-bottom small fw-bold text-muted ps-4 py-3 text-uppercase" style="width: 40%;">Tên dịch vụ / Linh kiện</th>
                                    <th class="border-bottom small fw-bold text-muted py-3 text-uppercase text-center" style="width: 15%;">Số lượng</th>
                                    <th class="border-bottom small fw-bold text-muted py-3 text-uppercase text-end" style="width: 20%;">Đơn giá (VNĐ)</th>
                                    <th class="border-bottom small fw-bold text-muted py-3 text-end text-uppercase" style="width: 15%;">Thành tiền</th>
                                    <th class="border-bottom small fw-bold text-muted py-3 pe-4 text-center" style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="ps-4 py-3 border-bottom-0">
                                            <input type="text" x-model="item.name" :name="'items['+index+'][name]'" class="form-control bg-light border-0 rounded-3 py-2" placeholder="VD: Phí bảo dưỡng định kỳ..." required>
                                        </td>
                                        <td class="py-3 border-bottom-0">
                                            <input type="number" x-model.number="item.quantity" :name="'items['+index+'][quantity]'" class="form-control bg-light border-0 rounded-3 py-2 text-center" min="1" required>
                                        </td>
                                        <td class="py-3 border-bottom-0">
                                            <input type="number" x-model.number="item.price" :name="'items['+index+'][price]'" class="form-control bg-light border-0 rounded-3 py-2 text-end" min="0" required>
                                        </td>
                                        <td class="py-3 border-bottom-0 text-end fw-bold text-dark">
                                            <span x-text="formatCurrency(item.quantity * item.price) + ' đ'"></span>
                                        </td>
                                        <td class="pe-4 py-3 border-bottom-0 text-center">
                                            <button type="button" class="btn btn-link text-muted p-0 shadow-none hover-danger transition" @click="removeItem(index)" x-show="items.length > 1">
                                                <i class="far fa-trash-alt fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="border-top bg-light">
                                <tr>
                                    <td colspan="3" class="ps-4 py-4 text-end fw-bold text-dark fs-5 text-uppercase">Tổng cộng:</td>
                                    <td class="py-4 text-end fw-bold text-primary fs-4" x-text="formatCurrency(total) + ' đ'"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-5">
                <a href="{{ route('admin.maintenance.orders') }}" class="btn btn-outline-secondary px-4 py-2 fw-bold bg-white shadow-sm">Hủy thay đổi</a>
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                    <i class="fas fa-save me-2"></i> Lưu cập nhật
                </button>
            </div>
        </form>

        {{-- Lịch sử thanh toán Card --}}
        <div class="data-card mb-4">
            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-money-check-alt me-2 text-primary"></i>Lịch sử thanh toán</h5>
                @if($order->paid_amount < $order->total_amount)
                    <button type="button" class="btn btn-success btn-sm px-3 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-plus me-1"></i> Thêm thanh toán
                    </button>
                @endif
            </div>
            
            <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
                <div class="text-center px-4">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Tổng tiền</p>
                    <h4 class="text-dark fw-bold mb-0">{{ number_format($order->total_amount, 0, ',', '.') }} đ</h4>
                </div>
                <div class="text-center px-4 border-start border-end">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Đã thanh toán</p>
                    <h4 class="text-success fw-bold mb-0">{{ number_format($order->paid_amount, 0, ',', '.') }} đ</h4>
                </div>
                <div class="text-center px-4">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Còn nợ</p>
                    <h4 class="text-danger fw-bold mb-0">{{ number_format($order->total_amount - $order->paid_amount, 0, ',', '.') }} đ</h4>
                </div>
            </div>

            <div class="p-0">
                <div class="table-responsive">
                    <table class="table mb-0 text-nowrap align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom small fw-bold text-muted ps-4 py-3 text-uppercase">Ngày thanh toán</th>
                                <th class="border-bottom small fw-bold text-muted py-3 text-uppercase">Số tiền (VNĐ)</th>
                                <th class="border-bottom small fw-bold text-muted py-3 text-uppercase">Người thu</th>
                                <th class="border-bottom small fw-bold text-muted py-3 pe-4 text-uppercase">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $paymentHistory = $order->payment_history ?? [];
                            @endphp
                            @forelse($paymentHistory as $payment)
                                <tr>
                                    <td class="ps-4 py-3 border-bottom-0">
                                        <i class="far fa-calendar-alt text-muted me-2"></i>
                                        {{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y') }}
                                        <div class="small text-muted mt-1">Ghi nhận lúc: {{ \Carbon\Carbon::parse($payment['created_at'])->format('H:i d/m/Y') }}</div>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-success fw-bold">
                                        + {{ number_format($payment['amount'], 0, ',', '.') }} đ
                                    </td>
                                    <td class="py-3 border-bottom-0">
                                        <i class="far fa-user text-muted me-1"></i> {{ $payment['user_name'] ?? 'N/A' }}
                                    </td>
                                    <td class="pe-4 py-3 border-bottom-0 text-muted">
                                        {{ $payment['note'] ?? '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-file-invoice-dollar fs-4 mb-2 opacity-50"></i>
                                        <p class="mb-0">Chưa có lịch sử thanh toán nào.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment Modal --}}
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-light p-4 border-bottom">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-money-bill-wave text-success me-2"></i>Thêm lượt thanh toán mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.maintenance.orders.payment', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-info mb-4 rounded-3 border-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold">Số tiền cần thanh toán nốt:</span>
                                    <span class="fs-5 fw-bold text-danger">{{ number_format($order->total_amount - $order->paid_amount, 0, ',', '.') }} đ</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Số tiền khách thanh toán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control bg-light border-0 py-2" required min="1" max="{{ $order->total_amount - $order->paid_amount }}" value="{{ $order->total_amount - $order->paid_amount }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Ngày thanh toán <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control bg-light border-0 py-2" required value="{{ date('Y-m-d') }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Ghi chú / Số tham chiếu</label>
                                <textarea name="note" class="form-control bg-light border-0 py-2" rows="2" placeholder="Ví dụ: Chuyển khoản Vietcombank..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3 border-top">
                            <button type="button" class="btn btn-outline-secondary px-4 fw-bold shadow-sm border-0" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Xác nhận thanh toán</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('editOrderForm', () => ({
            selectedBuildingId: '{{ $order->building_id }}',
            selectedElevatorId: '{{ $order->elevator_id }}',
            allElevators: @json($elevators->map(function($e) { return ['id' => $e->id, 'building_id' => $e->building_id, 'code' => $e->code]; })),
            
            items: @json($order->items->map(function($item) { 
                return [
                    'name' => $item->service_name, 
                    'quantity' => $item->quantity, 
                    'price' => $item->price
                ]; 
            })),
            
            createdDate: '{{ $order->created_at->format("Y-m-d") }}',

            init() {
                if(this.items.length === 0) {
                    this.addItem();
                }
            },

            get filteredElevators() {
                if (!this.selectedBuildingId) return [];
                return this.allElevators.filter(e => e.building_id == this.selectedBuildingId);
            },

            get total() {
                return this.items.reduce((sum, item) => sum + (item.quantity * (item.price || 0)), 0);
            },

            addItem() {
                this.items.push({ name: '', quantity: 1, price: 0 });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('vi-VN').format(value || 0);
            }
        }));
    });
</script>
<style>
    .hover-danger:hover { color: #dc3545 !important; }
    .transition { transition: all 0.2s ease; }
    .data-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
    }
</style>
@endsection
