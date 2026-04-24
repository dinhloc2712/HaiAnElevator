@extends('layouts.admin')

@section('title', 'Đơn bảo trì & Báo giá')

@section('content')
{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-header-title mb-1">Đơn bảo trì & Báo giá</h1>
        <p class="page-header-sub mb-0">Lên đơn, nhập giá dịch vụ/linh kiện và theo dõi thanh toán.</p>
    </div>
    @can('create_maintenance_order')
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            <i class="fas fa-plus me-md-2"></i><span class="d-none d-md-inline"> Tạo đơn mới</span>
        </button>
    @endcan
</div>

{{-- Statistics Section --}}
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
            <div class="card-body">
                <h6 class="text-muted fw-bold small mb-3 d-flex align-items-center text-uppercase">
                    <i class="fas fa-chart-line text-primary me-2"></i> Doanh thu bảo trì (6 tháng)
                </h6>
                <div style="height: 180px; width: 100%;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: #f0f7ff;">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <h6 class="text-primary fw-bold small mb-3 d-flex align-items-center text-uppercase">
                    <i class="far fa-credit-card me-2"></i> Tổng doanh thu tháng
                </h6>
                <h2 class="fw-bold text-primary mb-2" style="font-size: 2rem;">
                    {{ number_format($stats['current_month_revenue'], 0, ',', '.') }} <span class="text-decoration-underline" style="font-size: 1.5rem;">đ</span>
                </h2>
                
                @if($stats['revenue_increase_percent'] >= 0)
                    <p class="mb-0 small text-muted">
                        Tăng <span class="text-success fw-bold">{{ $stats['revenue_increase_percent'] }}%</span> so với tháng trước
                    </p>
                @else
                    <p class="mb-0 small text-muted">
                        Giảm <span class="text-danger fw-bold">{{ abs($stats['revenue_increase_percent']) }}%</span> so với tháng trước
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Orders Card --}}
<div class="data-card">
    <div class="p-3 border-bottom">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" placeholder="Tìm kiếm mã đơn, khách hàng..." autocomplete="off">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern table-horizontal-mobile">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Thang máy</th>
                    <th>Ngày tạo</th>
                    <th class="text-end">Tổng tiền</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="far fa-file-alt text-muted"></i>
                            <span class="fw-bold">{{ $order->code }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="customer-name">{{ $order->building->name ?? '' }}</span>
                    </td>
                    <td>
                        <span class="text-muted">{{ $order->elevator->code ?? '' }}</span>
                    </td>
                    <td>
                        {{ $order->created_at->format('Y-m-d') }}
                    </td>
                    <td class="text-end fw-bold">
                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                    </td>
                    <td class="text-center">
                        @if($order->status == 'paid')
                            <span class="badge-active">Đã thanh toán</span>
                        @elseif($order->status == 'pending')
                            <span class="badge-inactive" style="background: #fff4e5; color: #ff9800;">Chờ thanh toán</span>
                        @else
                            <span class="badge-inactive">{{ $order->status }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @can('update_maintenance_order')
                            <a href="{{ route('admin.maintenance.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill fw-bold" style="font-size: 0.75rem;">Chi tiết</a>
                        @elsecan('view_maintenance_order')
                            <a href="{{ route('admin.maintenance.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill fw-bold" style="font-size: 0.75rem;">Chi tiết</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <p class="mb-0 fw-semibold">Chưa có đơn bảo trì nào.</p>
                            <p class="small mt-1">Nhấn <strong>Tạo đơn mới</strong> để bắt đầu.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $orders->links() }}
    </div>
    @endif
</div>

{{-- Create Order Modal --}}
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-hidden="true" x-data="orderForm()">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #f8f9fa;">
            <div class="modal-header bg-white p-4 border-bottom">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center">
                    <i class="fas fa-dollar-sign me-2 fs-4"></i> Tạo Đơn bảo trì / Báo giá
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.maintenance.orders.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- General Info Card --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Khách hàng</label>
                                <select name="building_id" class="form-select bg-light border-0 py-2 rounded-3" x-model="selectedBuildingId" @change="selectedElevatorId = ''" required>
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}">{{ $building->name }} - {{ $building->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Thang máy</label>
                                <select name="elevator_id" class="form-select bg-light border-0 py-2 rounded-3" x-model="selectedElevatorId" required>
                                    <option value="">-- Chọn thang máy --</option>
                                    <template x-for="elevator in filteredElevators" :key="elevator.id">
                                        <option :value="elevator.id" x-text="elevator.code"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Ngày lập đơn</label>
                                <div class="input-group">
                                    <input type="date" name="created_date" class="form-control bg-light border-0 py-2 rounded-3" x-model="createdDate" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Services & Parts Card --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark fs-5">Chi tiết dịch vụ & Linh kiện</h6>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-bold bg-light border-0 shadow-sm" @click="addItem">
                                <i class="fas fa-plus me-1"></i> Thêm dòng
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0 text-nowrap align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 small fw-bold text-muted ps-4 py-3" style="width: 40%;">Tên dịch vụ / Linh kiện</th>
                                            <th class="border-0 small fw-bold text-muted py-3" style="width: 15%;">Số lượng</th>
                                            <th class="border-0 small fw-bold text-muted py-3" style="width: 20%;">Đơn giá (VNĐ)</th>
                                            <th class="border-0 small fw-bold text-muted py-3 text-end" style="width: 15%;">Thành tiền</th>
                                            <th class="border-0 small fw-bold text-muted py-3 pe-4 text-center" style="width: 10%;"></th>
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
                                    <tfoot class="border-top">
                                        <tr>
                                            <td colspan="3" class="ps-4 py-3 text-end fw-bold text-dark fs-5">Tổng cộng:</td>
                                            <td class="py-3 text-end fw-bold text-primary fs-5" x-text="formatCurrency(total) + ' đ'"></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white p-4 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-bold bg-light border-0 shadow-sm w-sm-100" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" name="save_draft" value="1" class="btn btn-outline-secondary px-4 py-2 fw-bold shadow-sm w-sm-100 mx-2" style="background-color: #f1f5f9; border-color: #f1f5f9;">Lưu nháp</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm w-sm-100">Tạo đơn & Gửi khách hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderForm', () => ({
            selectedBuildingId: '',
            selectedElevatorId: '',
            allElevators: @json($elevators->map(function($e) { return ['id' => $e->id, 'building_id' => $e->building_id, 'code' => $e->code]; })),
            
            items: [
                { name: '', quantity: 1, price: 0 }
            ],
            
            createdDate: new Date().toISOString().split('T')[0],

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
    @media (max-width: 576px) {
        .w-sm-100 { width: 100%; margin-bottom: 0.5rem; }
        .mx-2.w-sm-100 { margin-left: 0 !important; margin-right: 0 !important; }
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = {!! json_encode($stats['chart_labels']) !!};
        const dataStats = {!! json_encode($stats['chart_data']) !!};

        // Custom grid colors and font to match layout
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#9ca3af';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: dataStats,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 10,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 14 },
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: true,
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
