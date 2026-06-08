@extends('layouts.admin')

@section('title', 'Lịch sử hệ thống')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 6px;
            border: 1px solid #d1d3e2;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')

@php
    $modelNames = [
        'App\Models\Building' => 'Tòa nhà',
        'App\Models\Elevator' => 'Thang máy',
        'App\Models\Incident' => 'Sự cố',
        'App\Models\MaintenanceCheck' => 'Phiếu bảo trì',
        'App\Models\Order' => 'Đơn hàng',
        'App\Models\User' => 'Người dùng',
        'App\Models\Installation' => 'Lắp đặt',
    ];

    $fieldNames = [
        'name' => 'Tên',
        'code' => 'Mã',
        'status' => 'Trạng thái',
        'address' => 'Địa chỉ',
        'contact_name' => 'Người liên hệ',
        'contact_phone' => 'Số điện thoại liên hệ',
        'building_id' => 'ID Tòa nhà',
        'branch_id' => 'ID Chi nhánh',
        'customer_name' => 'Tên khách hàng',
        'customer_phone' => 'SĐT khách hàng',
        'province' => 'Tỉnh/Thành phố',
        'district' => 'Quận/Huyện',
        'manufacturer' => 'Hãng sản xuất',
        'model' => 'Model',
        'type' => 'Loại',
        'capacity' => 'Tải trọng',
        'floors' => 'Số điểm dừng',
        'cycle_days' => 'Chu kỳ bảo trì (ngày)',
        'note' => 'Ghi chú',
        'notes' => 'Ghi chú',
        'maintenance_deadline' => 'Hạn bảo trì tiếp theo',
        'maintenance_end_date' => 'Ngày kết thúc bảo trì',
        'elevator_count' => 'Số lượng thang máy',
        'is_active' => 'Trạng thái hoạt động',
        'total_amount' => 'Tổng tiền',
        'elevator_id' => 'ID Thang máy',
        'task_type' => 'Loại công việc',
        'fault_category' => 'Hạng mục lỗi',
        'check_date' => 'Ngày kiểm tra',
        'evaluation' => 'Đánh giá',
        'staff_ids' => 'ID Nhân viên',
        'staff_names' => 'Nhân viên phụ trách',
        'start_time' => 'Thời gian bắt đầu',
        'end_time' => 'Thời gian kết thúc',
        'user_id' => 'ID Người dùng',
        'start_date' => 'Ngày bắt đầu',
        'due_date' => 'Ngày dự kiến HT',
        'role_id' => 'ID Phân quyền',
        'email' => 'Email',
        'phone' => 'Số điện thoại',
        'payment_status' => 'Trạng thái thanh toán',
        'payment_history' => 'Lịch sử thanh toán',
        'paid_amount' => 'Số tiền đã thanh toán',
        'remaining_amount' => 'Số tiền còn lại',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
        'deleted_at' => 'Ngày xóa',
    ];

    $formatValue = function($val, $key) {
        if (is_array($val) || is_object($val)) {
            // Special handling for payment_history
            if ($key === 'payment_history') {
                $items = is_object($val) ? [$val] : $val;
                $output = '';
                foreach ($items as $item) {
                    $item = (array)$item;
                    $date = isset($item['date']) ? date('d/m/Y', strtotime($item['date'])) : 'N/A';
                    $amount = isset($item['amount']) ? number_format($item['amount']) . 'đ' : '0đ';
                    $user = $item['user_name'] ?? 'N/A';
                    $output .= "• {$date}: {$amount} ({$user})\n";
                }
                return trim($output) ?: '(Trống)';
            }
            return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        // Try to decode if it's a JSON string
        if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Recursive call for decoded value
                if (is_array($decoded) || is_object($decoded)) {
                     // We need to refer to $formatValue inside itself, but in PHP closures we use 'use'
                     // For Blade simplicity, we can just do a limited version here
                     if ($key === 'payment_history') {
                        $items = is_object($decoded) ? [$decoded] : $decoded;
                        $output = '';
                        foreach ($items as $item) {
                            $item = (array)$item;
                            $date = isset($item['date']) ? date('d/m/Y', strtotime($item['date'])) : 'N/A';
                            $amount = isset($item['amount']) ? number_format($item['amount']) . 'đ' : '0đ';
                            $user = $item['user_name'] ?? 'N/A';
                            $output .= "• {$date}: {$amount} ({$user})\n";
                        }
                        return trim($output) ?: '(Trống)';
                     }
                     return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
            }
        }
        
        return $val;
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Lịch sử hệ thống</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách thao tác</h6>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="mb-4 row g-3">
            <div class="col-md-3">
                <select name="subject_type" id="select-subject-type" class="form-select" style="width:100%;">
                    <option value="">-- Tất cả Đối tượng --</option>
                    @foreach($subjectTypes as $type)
                        @if($type)
                        <option value="{{ $type }}" {{ request('subject_type') == $type ? 'selected' : '' }}>
                            {{ $modelNames[$type] ?? class_basename($type) }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="event" class="form-select">
                    <option value="">-- Tất cả Hành động --</option>
                    @foreach($events as $event)
                        @if($event)
                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                            @if($event == 'created') Thêm mới
                            @elseif($event == 'updated') Cập nhật
                            @elseif($event == 'deleted') Xóa
                            @else {{ ucfirst($event) }} @endif
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="causer_id" id="select-causer-id" class="form-select" style="width:100%;">
                    <option value="">-- Tất cả Người dùng --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">Khôi phục</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Đối tượng</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if($activity->causer)
                                    <span class="badge bg-info text-dark">{{ $activity->causer->name }}</span>
                                @else
                                    <span class="text-muted">Hệ thống</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->event == 'created')
                                    <span class="badge bg-success">Thêm mới</span>
                                @elseif($activity->event == 'updated')
                                    <span class="badge bg-warning text-dark">Cập nhật</span>
                                @elseif($activity->event == 'deleted')
                                    <span class="badge bg-danger">Xóa</span>
                                @else
                                    <span class="badge bg-secondary">{{ $activity->event }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    if ($activity->subject) {
                                        $subjectIdentifier = $activity->subject->name ?: ($activity->subject->code ?: ($activity->subject->customer_name ?: ('ID: ' . $activity->subject->id)));
                                    } else {
                                        $oldData = $activity->properties['old'] ?? [];
                                        $subjectIdentifier = ($oldData['name'] ?? '') ?: (($oldData['code'] ?? '') ?: (($oldData['customer_name'] ?? '') ?: ('ID: ' . $activity->subject_id . ' - Đã bị xóa')));
                                    }

                                    // Add Elevator code for relevant models (MaintenanceCheck, Incident, etc.)
                                    $elevatorId = ($activity->subject && isset($activity->subject->elevator_id)) 
                                        ? $activity->subject->elevator_id 
                                        : ($activity->properties['attributes']['elevator_id'] ?? ($activity->properties['old']['elevator_id'] ?? null));
                                    
                                    if ($elevatorId && $activity->subject_type !== 'App\Models\Elevator') {
                                        $elevator = \App\Models\Elevator::find($elevatorId);
                                        if ($elevator) {
                                            $subjectIdentifier .= " - " . $elevator->code;
                                        }
                                    }
                                @endphp
                                <strong>{{ $modelNames[$activity->subject_type] ?? class_basename($activity->subject_type) }}</strong>
                                <div class="text-muted mt-1 small">
                                    <i class="fas fa-tag me-1"></i> {{ $subjectIdentifier }}
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logModal{{ $activity->id }}">
                                    Xem chi tiết
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="logModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Chi tiết thay đổi - {{ $modelNames[$activity->subject_type] ?? class_basename($activity->subject_type) }}
                                                    ({{ $subjectIdentifier }})
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                @php
                                                    $old = $activity->properties['old'] ?? [];
                                                    $new = $activity->properties['attributes'] ?? [];
                                                    $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                                @endphp

                                                @if(count($allKeys) > 0)
                                                    <div class="table-responsive m-0">
                                                        <table class="table table-bordered table-striped m-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="30%">Trường dữ liệu</th>
                                                                    <th width="35%">Dữ liệu cũ</th>
                                                                    <th width="35%">Dữ liệu mới</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($allKeys as $key)
                                                                    @php
                                                                        $oldVal = $old[$key] ?? '';
                                                                        $newVal = $new[$key] ?? '';
                                                                        $oldVal = $formatValue($oldVal, $key);
                                                                        $newVal = $formatValue($newVal, $key);

                                                                        // Check if changed
                                                                        $isChanged = ($activity->event == 'updated' && $oldVal != $newVal);
                                                                    @endphp
                                                                    <tr class="{{ $isChanged ? 'table-warning' : '' }}">
                                                                        <td>
                                                                            <strong>{{ $fieldNames[$key] ?? $key }}</strong>
                                                                            @if(isset($fieldNames[$key]))
                                                                                <br><small class="text-muted fst-italic">({{ $key }})</small>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-danger" style="word-break: break-all;">
                                                                             @if($activity->event == 'created')
                                                                                 <span class="text-muted fst-italic">-</span>
                                                                             @else
                                                                                 @if($oldVal === '' || $oldVal === null)
                                                                                     (Trống)
                                                                                 @else
                                                                                     {!! nl2br(e($oldVal)) !!}
                                                                                 @endif
                                                                             @endif
                                                                        </td>
                                                                        <td class="text-success" style="word-break: break-all;">
                                                                             @if($activity->event == 'deleted')
                                                                                 <span class="text-muted fst-italic">-</span>
                                                                             @else
                                                                                 @if($newVal === '' || $newVal === null)
                                                                                     (Trống)
                                                                                 @else
                                                                                     {!! nl2br(e($newVal)) !!}
                                                                                 @endif
                                                                             @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="p-4 text-center text-muted">
                                                        <i class="fas fa-info-circle mb-2 fa-2x opacity-50"></i>
                                                        <p class="mb-0">Không có chi tiết thay đổi nào được ghi nhận cho thao tác này.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không có dữ liệu lịch sử nào khớp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $activities->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#select-subject-type').select2({
                placeholder: '-- Tất cả Đối tượng --',
                allowClear: true,
                width: '100%',
                language: { noResults: function() { return 'Không tìm thấy'; } }
            });
            $('#select-causer-id').select2({
                placeholder: '-- Tất cả Người dùng --',
                allowClear: true,
                width: '100%',
                language: { noResults: function() { return 'Không tìm thấy'; } }
            });
        });
    </script>
@endsection
