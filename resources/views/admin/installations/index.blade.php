@extends('layouts.admin')

@section('title', 'Lắp đặt thang máy')

@section('styles')
<style>
    .table-responsive {
        min-height: 350px; /* Đảm bảo đủ khoảng trống cho dropdown ở dòng cuối */
    }
</style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Lắp đặt thang máy</h1>
                <p class="mb-0 text-muted small">Quản lý tiến độ lắp đặt và giao việc cho nhân viên</p>
            </div>
            @can('create_installation')
                <a href="{{ route('admin.installations.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i> Tạo đơn lắp đặt
                </a>
            @endcan
        </div>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Đang thực hiện</p>
                        <h2 class="fw-bold mb-0 text-primary">{{ $stats['in_progress'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-blue">
                        <i class="far fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Chờ xử lý</p>
                        <h2 class="fw-bold mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-orange">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 p-4 stats-card-premium">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Đã hoàn thành</p>
                        <h2 class="fw-bold mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h2>
                    </div>
                    <div class="card-summary-icon icon-bg-green">
                        <i class="far fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders List Section --}}
    <div class="tech-card">
        <div class="tech-header" style="background: white; border-bottom: 1px solid #f1f3f9;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center text-nowrap">
                    <i class="fas fa-wrench me-2 bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-none d-md-flex"
                        style="width: 36px; height: 36px; align-items: center; justify-content: center;"></i>
                    <span>Danh sách đơn lắp đặt</span>
                </h6>

                <div class="d-flex align-items-center flex-wrap gap-2 w-100 w-sm-auto justify-content-md-end">
                    {{-- Quick Search --}}
                    <div class="bg-light rounded-pill px-3 py-1 d-flex align-items-center flex-grow-1" style="min-width: 250px;">
                        <i class="fas fa-search text-muted me-2"></i>
                        <input type="text" class="form-control border-0 bg-transparent shadow-none small" 
                               placeholder="Tìm kiếm khách hàng, mã..." style="font-size: 0.85rem;">
                    </div>
                    <button class="btn btn-outline-secondary rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm d-flex align-items-center" 
                            style="font-size: 0.8rem; background: white;">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                </div>
            </div>
        </div>

        <div class="pb-5">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0 small fw-bold text-muted">MÃ ĐƠN</th>
                        <th class="border-0 small fw-bold text-muted">KHÁCH HÀNG / ĐỊA CHỈ</th>
                        <th class="border-0 small fw-bold text-muted">NHÂN VIÊN PHỤ TRÁCH</th>
                        <th class="border-0 small fw-bold text-muted">THỜI GIAN DỰ KIẾN</th>
                        <th class="border-0 small fw-bold text-muted">TRẠNG THÁI</th>
                        <th class="pe-4 border-0 text-end small fw-bold text-muted">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installations as $inst)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $inst->code }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $inst->building->name ?? 'N/A' }}</div>
                                <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ $inst->building->address ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="staff-avatar-circle">
                                        {{ strtoupper(substr($inst->staff->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="small fw-bold">{{ $inst->staff->name ?? 'Chưa giao' }}</span>
                                </div>
                            </td>
                            <td class="small">
                                <div>Bắt đầu: <span class="text-dark fw-bold">{{ $inst->start_date ? $inst->start_date->format('Y-m-d') : '---' }}</span></div>
                                <div class="text-muted">Dự kiến: {{ $inst->due_date ? $inst->due_date->format('Y-m-d') : '---' }}</div>
                            </td>
                            <td>
                                @if($inst->status == 'in_progress')
                                    <span class="badge-pill-modern badge-status-in-progress">Đang lắp</span>
                                @elseif($inst->status == 'pending')
                                    <span class="badge-pill-modern badge-status-pending">Chờ giao</span>
                                @else
                                    <span class="badge-pill-modern badge-status-completed">Đã xong</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    @if($inst->status == 'pending')
                                        @can('update_installation')
                                            <form action="{{ route('admin.installations.start', $inst->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" style="font-size: 0.75rem;">
                                                    <i class="fas fa-play me-1"></i> Bắt đầu lắp đặt
                                                </button>
                                            </form>
                                        @endcan
                                    @elseif($inst->status == 'in_progress')
                                        @can('update_installation')
                                            <button type="button" class="btn-ghost-complete open-complete-modal" 
                                                data-id="{{ $inst->id }}"
                                                data-code="{{ $inst->code }}"
                                                data-building="{{ $inst->building->name ?? '' }}"
                                                data-branch="{{ $inst->branch->name ?? 'N/A' }}">
                                                <i class="far fa-check-circle me-1"></i> Hoàn thành
                                            </button>
                                        @endcan
                                    @endif
                                    <div class="dropdown {{ $loop->last ? 'dropup' : '' }}">
                                        <button class="btn btn-link text-muted p-0 shadow-none" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-strategy="fixed">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @can('update_installation')
                                                <li><a class="dropdown-item small" href="{{ route('admin.installations.edit', $inst->id) }}"><i class="fas fa-edit me-2"></i> Chỉnh sửa</a></li>
                                            @endcan
                                            @can('delete_installation')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.installations.destroy', $inst->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item small text-danger"><i class="fas fa-trash-alt me-2"></i> Xóa</button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Chưa có đơn lắp đặt nào.</p>
                                <a href="{{ route('admin.installations.create') }}" class="btn btn-primary btn-sm mt-3 rounded-pill px-4">Tạo đơn ngay</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($installations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $installations->links() }}
            </div>
        @endif
    </div>

    {{-- Elevator Registration Modal --}}
    <div class="modal fade" id="completeInstallationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white p-4 border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-elevator me-2"></i> Đăng ký thang máy & Hoàn thành lắp đặt
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="completeForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 mb-4 small">
                            <i class="fas fa-info-circle me-2"></i> 
                            Đơn lắp đặt <strong id="modal_inst_code"></strong> cho <strong id="modal_building"></strong> sẽ được đánh dấu là 
                            <strong>Đã xong</strong> sau khi bạn đăng ký thông tin thang máy dưới đây.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mã thang máy</label>
                                <input type="text" name="elevator_code" class="form-control bg-light border-0 p-3 rounded-4 fw-bold" placeholder="Ví dụ: TH-001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Hãng sản xuất</label>
                                <input type="text" name="manufacturer" class="form-control bg-light border-0 p-3 rounded-4" placeholder="Ví dụ: Mitsubishi, Fuji...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Model</label>
                                <input type="text" name="model" class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Loại thang</label>
                                <input type="text" name="type" class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tải trọng (kg)</label>
                                <input type="text" name="capacity" class="form-control bg-light border-0 p-3 rounded-4">
                            </div>
                            
                            <hr class="my-4 opacity-5">
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tỉnh / Thành phố</label>
                                <input type="text" name="province" class="form-control bg-light border-0 p-3 rounded-4" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Quận / Huyện</label>
                                <input type="text" name="district" class="form-control bg-light border-0 p-3 rounded-4" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Chu kỳ bảo trì (Ngày)</label>
                                <input type="number" name="cycle_days" class="form-control bg-light border-0 p-3 rounded-4" value="30" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4 bg-light border-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Lưu & Hoàn thành
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('completeInstallationModal'));
            const completeButtons = document.querySelectorAll('.open-complete-modal');
            const completeForm = document.getElementById('completeForm');
            const instCodeSpan = document.getElementById('modal_inst_code');
            const buildingSpan = document.getElementById('modal_building');

            completeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const code = this.getAttribute('data-code');
                    const building = this.getAttribute('data-building');
                    
                    // Set form action
                    completeForm.action = `/admin/installations/${id}/complete`;
                    
                    // Set display info
                    instCodeSpan.textContent = code;
                    buildingSpan.textContent = building;
                    
                    // Clear previous inputs if any (except cycle_days)
                    completeForm.querySelectorAll('input:not([name="_token"]):not([name="cycle_days"])').forEach(input => {
                        input.value = '';
                    });

                    modal.show();
                });
            });
        });
    </script>
@endsection
