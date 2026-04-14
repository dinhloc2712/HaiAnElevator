@extends('layouts.admin')

@section('title', 'Chi tiết sự cố: ' . $incident->code)

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Chi tiết sự cố: {{ $incident->code }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.incidents.index') }}" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>
            <a href="{{ route('admin.incidents.edit', $incident->id) }}" class="btn btn-primary rounded-3 px-4 shadow-sm">
                <i class="far fa-edit me-2"></i> Sửa thông tin
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tech-card p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-3 mb-4"><i class="far fa-file-alt text-primary me-2"></i> Nội dung báo cáo</h5>
            <div class="bg-light p-4 rounded-4 mb-4" style="min-height: 200px; white-space: pre-wrap;">{{ $incident->description }}</div>
            
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <label class="small text-muted text-uppercase fw-bold d-block">Mức độ ưu tiên</label>
                    <span class="fs-5 fw-bold text-{{ $incident->priority == 'emergency' ? 'danger' : ($incident->priority == 'high' ? 'orange' : 'primary') }}">
                        {{ ['emergency' => 'Khẩn cấp', 'high' => 'Cao', 'medium' => 'Trung bình', 'low' => 'Thấp'][$incident->priority] }}
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="small text-muted text-uppercase fw-bold d-block">Trạng thái hiện tại</label>
                    <span class="fs-5 fw-bold text-{{ $incident->status == 'resolved' ? 'success' : ($incident->status == 'processing' ? 'warning' : 'info') }}">
                        {{ ['new' => 'Mới báo', 'processing' => 'Đang xử lý', 'resolved' => 'Hoàn thành', 'canceled' => 'Đã hủy'][$incident->status] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="tech-card p-4 mb-4">
            <h5 class="fw-bold border-bottom pb-3 mb-4"><i class="fas fa-elevator text-primary me-2"></i> Thông tin thiết bị</h5>
            <div class="mb-3">
                <label class="small text-muted text-uppercase fw-bold d-block">Mã thang máy</label>
                <div class="fw-bold fs-5">{{ $incident->elevator->code ?? 'N/A' }}</div>
            </div>
            <div class="mb-3">
                <label class="small text-muted text-uppercase fw-bold d-block">Tòa nhà / Khách hàng</label>
                <div class="fw-bold">{{ $incident->elevator->building->name ?? 'N/A' }}</div>
            </div>
            <div class="mb-0">
                <label class="small text-muted text-uppercase fw-bold d-block">Chi nhánh</label>
                <div class="fw-bold">{{ $incident->elevator->branch->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="tech-card p-4">
            <h5 class="fw-bold border-bottom pb-3 mb-4"><i class="far fa-user text-primary me-2"></i> Người báo cáo</h5>
            <div class="mb-3">
                <label class="small text-muted text-uppercase fw-bold d-block">Họ và tên</label>
                <div class="fw-bold">{{ $incident->reporter_name ?? 'Không rõ' }}</div>
            </div>
            <div class="mb-3">
                <label class="small text-muted text-uppercase fw-bold d-block">Số điện thoại</label>
                <div class="fw-bold text-primary fs-5">{{ $incident->reporter_phone ?? 'N/A' }}</div>
            </div>
            <div class="mb-0">
                <label class="small text-muted text-uppercase fw-bold d-block">Thời gian báo</label>
                <div class="fw-bold">{{ $incident->reported_at ? $incident->reported_at->format('H:i d/m/Y') : 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
