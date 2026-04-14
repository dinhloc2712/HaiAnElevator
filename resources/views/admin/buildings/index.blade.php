@extends('layouts.admin')

@section('title', 'Tòa nhà & Khách hàng')


@section('content')
{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-header-title mb-1">Tòa nhà &amp; Khách hàng</h1>
        <p class="page-header-sub mb-0">Quản lý thông tin đơn vị sở hữu và địa điểm lắp đặt thang máy.</p>
    </div>
    <a href="{{ route('admin.buildings.create') }}" class="btn btn-add">
        <i class="fas fa-plus me-2"></i>Thêm tòa nhà
    </a>
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
