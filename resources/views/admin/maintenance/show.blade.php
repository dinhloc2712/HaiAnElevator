@extends('layouts.admin')

@section('title', 'Chi tiết phiếu bảo trì')

@section('styles')
    <style>
        .checklist-section-title {
            background: #f8f9fc;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 700;
            color: #4e73df;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 4px solid #4e73df;
            margin-bottom: 15px;
            margin-top: 10px;
        }

        .checklist-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .symbol-badge {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
        }

        .symbol-normal { background-color: #1cc88a; } /* Δ Bình thường */
        .symbol-checked { background-color: #36b9cc; } /* √ Đã kiểm tra */
        .symbol-replaced { background-color: #f6c23e; } /* # Đã thay thế */
        .symbol-fixed { background-color: #e74a3b; } /* X Đã sửa chữa */
        .symbol-waiting { background-color: #f6c23e; } /* A Đang chờ */
        .symbol-unused { background-color: #858796; } /* / Không sử dụng */
        .symbol-none { background-color: #5a5c69; } /* K Không có thiết bị */
        
        .symbol-default { background-color: #4e73df; }
    </style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Chi tiết phiếu bảo trì</h1>
                <p class="mb-0 text-muted small">Phiếu bảo trì thang máy {{ $maintenance->elevator->code ?? 'N/A' }} ngày {{ $maintenance->check_date ? $maintenance->check_date->format('d/m/Y') : 'N/A' }}</p>
            </div>
            <div>
                <a href="{{ route('admin.maintenance.export', $maintenance->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4 me-2">
                    <i class="fas fa-print me-2"></i> Xuất phiếu (PDF)
                </a>
                <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- Header Info Section --}}
            <div class="tech-card mb-4" id="print-area">
                <div class="tech-header" style="background: white; border-bottom: 1px solid #f1f3f9;">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 text-primary"></i> Thông tin chung
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="small text-muted mb-1 text-uppercase fw-bold">Thang máy</div>
                            <div class="h5 mb-0 fw-bold text-primary">{{ $maintenance->elevator->code ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted mb-1 text-uppercase fw-bold">Công trình</div>
                            <div class="h6 mb-0">{{ $maintenance->elevator->building->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $maintenance->elevator->building->address ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted mb-1 text-uppercase fw-bold">Thông số</div>
                            <div>Loại: {{ $maintenance->elevator->type ?? 'N/A' }}</div>
                            <div>Tải trọng: {{ $maintenance->elevator->capacity ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        @php 
                            $results = $maintenance->results ?? []; 
                            
                            function getSymbolClass($symbol) {
                                switch($symbol) {
                                    case 'Δ': return 'symbol-normal';
                                    case '√': return 'symbol-checked';
                                    case '#': return 'symbol-replaced';
                                    case 'X': return 'symbol-fixed';
                                    case 'A': return 'symbol-waiting';
                                    case '/': return 'symbol-unused';
                                    case 'K': return 'symbol-none';
                                    default: return 'symbol-default';
                                }
                            }
                        @endphp
                        @foreach($sections as $sectionName => $items)
                            <div class="col-md-6">
                                <div class="checklist-section-title">{{ $sectionName }}</div>
                                <div class="border rounded-4 overflow-hidden">
                                    @foreach($items as $id => $name)
                                        @php 
                                            $val = $results[$id] ?? ''; 
                                            $statusName = $symbols[$val] ?? 'N/A';
                                        @endphp
                                        <div class="checklist-item">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="text-muted small fw-bold" style="min-width: 20px;">{{ $loop->iteration }}.</span>
                                                <span class="small text-dark fw-bold">{{ $name }}</span>
                                            </div>
                                            <div>
                                                <span class="badge bg-light text-primary rounded-pill border px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                                                    {{ $statusName }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="fas fa-comment-dots me-2"></i> Đánh giá, nhận xét</h6>
                            <div class="p-3 bg-light rounded-4 border-0">
                                @if($maintenance->evaluation)
                                    {!! nl2br(e($maintenance->evaluation)) !!}
                                @else
                                    <span class="text-muted fst-italic">Không có đánh giá.</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="fas fa-users me-2"></i> Thông tin thực hiện</h6>
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" width="150">Cán bộ thực hiện:</td>
                                        <td class="fw-bold">{{ $maintenance->staff_names ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Số người thao tác:</td>
                                        <td class="fw-bold">{{ $maintenance->performer_count }} người</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Thời gian bắt đầu:</td>
                                        <td class="fw-bold">{{ $maintenance->start_time ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Thời gian kết thúc:</td>
                                        <td class="fw-bold">{{ $maintenance->end_time ?? 'N/A' }}</td>
                                    </tr>
                                    @if($maintenance->notes)
                                    <tr>
                                        <td class="text-muted">Ghi chú khác:</td>
                                        <td class="fw-bold">{{ $maintenance->notes }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<style type="text/css" media="print">
    body * {
        visibility: hidden;
    }
    #print-area, #print-area * {
        visibility: visible;
    }
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .tech-header-container, .btn, .sidebar, .topbar {
        display: none !important;
    }
    .tech-card {
        box-shadow: none !important;
        border: none !important;
    }
</style>
@endsection
