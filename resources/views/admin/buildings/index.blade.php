@extends('layouts.admin')

@section('title', 'Tòa nhà & Khách hàng')


@section('content')
{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-header-title mb-1">Tòa nhà &amp; Khách hàng</h1>
        <p class="page-header-sub mb-0">Quản lý thông tin đơn vị sở hữu và địa điểm lắp đặt thang máy.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import me-2"></i>Import Excel
        </button>
        <a href="{{ route('admin.buildings.create') }}" class="btn btn-add">
            <i class="fas fa-plus me-2"></i>Thêm tòa nhà
        </a>
    </div>
</div>

{{-- Table Card --}}
<div class="buildings-card">
    <div class="p-3 border-bottom">
        <form method="GET" action="{{ route('admin.buildings.index') }}">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Tìm kiếm tòa nhà, khách hàng..."
                       value="{{ request('search') }}" autocomplete="off">
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-horizontal-mobile">
            <thead>
                <tr>
                    <th>Tòa nhà</th>
                    <th>Khách hàng</th>
                    <th>Địa chỉ</th>
                    <th>Liên hệ</th>
                    <th class="text-end">Số lượng thang</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buildings as $building)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="building-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <span class="building-name">{{ $building->name }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="customer-name">{{ $building->customer_name ?? '—' }}</span>
                    </td>
                    <td>
                        @if($building->address)
                            <span class="address-text">
                                <i class="fas fa-map-marker-alt me-1 text-muted" style="font-size:0.75rem;"></i>
                                {{ Str::limit($building->address, 35) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($building->contact_name || $building->contact_phone)
                            <div class="contact-name">{{ $building->contact_name }}</div>
                            <div class="contact-phone">
                                <i class="fas fa-phone-alt me-1"></i>{{ $building->contact_phone }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="elevator-count">{{ $building->elevator_count }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('admin.buildings.edit', $building) }}" class="action-btn edit" title="Chỉnh sửa">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.buildings.destroy', $building) }}" method="POST"
                                   onsubmit="return confirm('Bạn chắc chắn muốn xóa tòa nhà này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn del" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-building"></i>
                            <p class="mb-0 fw-semibold">Chưa có tòa nhà nào.</p>
                            <p class="small mt-1">Nhấn <strong>Thêm tòa nhà</strong> để bắt đầu.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($buildings->hasPages())
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $buildings->links() }}
    </div>
    @endif
</div>
@endsection

@push('modals')
{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.buildings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="importModalLabel">Import Tòa Nhà</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-file-excel text-success" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted small">Chọn file Excel để nhập dữ liệu tòa nhà hàng loạt vào hệ thống.</p>
                    </div>

                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">Chọn file dữ liệu</label>
                        <input type="file" name="file" class="form-control shadow-none" id="file" required accept=".xlsx, .xls, .csv">
                        <div class="form-text mt-2">Định dạng hỗ trợ: .xlsx, .xls, .csv (Tối đa 10MB)</div>
                    </div>

                    <div class="bg-light p-3 rounded-3">
                        <p class="mb-2 fw-semibold small text-primary"><i class="fas fa-info-circle me-1"></i> Thứ tự các cột trong file:</p>
                        <div class="row g-2">
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 1</span> Tên tòa nhà <span class="text-danger">*</span></div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 2</span> Tên khách hàng</div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 3</span> Địa chỉ</div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 4</span> Người liên hệ</div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 5</span> Số điện thoại</div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 6</span> Số lượng thang</div>
                            <div class="col-12"><span class="badge bg-white text-dark border fw-normal">Cột 7</span> Ghi chú</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4">Bắt đầu Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

