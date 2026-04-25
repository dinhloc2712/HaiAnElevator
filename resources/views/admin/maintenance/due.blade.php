@extends('layouts.admin')

@section('title', 'Thang máy đến hạn bảo trì')

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Thang máy cần lập lịch</h1>
                <p class="mb-0 text-muted small">Danh sách thang máy quá hạn hoặc sắp đến hạn trong 15 ngày tới mà chưa được
                    tạo lịch.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    @if ($dueElevators->count() > 0)
        <form action="{{ route('admin.maintenance.bulk_store') }}" method="POST" id="bulkScheduleForm">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="tech-card mb-4 shadow-sm border-0" style="border-radius: 15px; background: white;">
                        <div
                            class="card-header bg-white border-0 py-3 px-2 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary">Danh sách thiết bị ({{ $dueElevators->count() }})</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label small fw-bold" for="selectAll">Chọn tất cả</label>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4" style="width: 40px;"></th>
                                        <th class="small fw-bold text-muted">MÃ THANG</th>
                                        <th class="small fw-bold text-muted">KHÁCH HÀNG / TÒA NHÀ</th>
                                        <th class="small fw-bold text-muted text-center">HẠN BẢO TRÌ</th>
                                        <th class="small fw-bold text-muted text-center">TRẠNG THÁI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dueElevators as $elevator)
                                        <tr>
                                            <td class="ps-4">
                                                <input class="form-check-input elevator-checkbox" type="checkbox"
                                                    name="elevator_ids[]" value="{{ $elevator->id }}">
                                            </td>
                                            <td class="fw-bold text-primary">{{ $elevator->code }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $elevator->building->name ?? 'N/A' }}
                                                </div>
                                                <div class="small text-muted">{{ $elevator->building->address ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="fw-bold {{ $elevator->maintenance_deadline->isPast() ? 'text-danger' : 'text-warning' }}">
                                                    {{ $elevator->maintenance_deadline->format('d/m/Y') }}
                                                </span>
                                                <div class="small text-muted">
                                                    ({{ $elevator->maintenance_deadline->diffForHumans() }})</div>
                                            </td>
                                            <td class="text-center">
                                                @if ($elevator->maintenance_deadline->isPast())
                                                    <span class="badge bg-danger rounded-pill px-3">Quá hạn</span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill px-3">Sắp đến
                                                        hạn</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="tech-card p-4 shadow-sm border-0 sticky-top"
                        style="border-radius: 15px; background: white; top: 20px;">
                        <h6 class="fw-bold text-dark mb-4"><i class="fas fa-calendar-plus me-2 text-primary"></i> Tạo lịch
                            hàng loạt</h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ngày bảo trì dự kiến</label>
                            <input type="date" name="check_date" class="form-control rounded-3" required
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Nhân viên thực hiện</label>
                            <select name="staff_ids[]" class="form-select select2 rounded-3" multiple
                                data-placeholder="Chọn kỹ thuật viên">
                                @foreach ($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Có thể để trống và phân công sau.</div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 small">
                            <i class="fas fa-info-circle me-1"></i> Hệ thống sẽ tạo phiếu bảo trì ở trạng thái <strong>"Đang
                                chờ"</strong> cho tất cả thang máy được chọn.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3 shadow-sm"
                            id="btnSubmit">
                            <i class="fas fa-save me-1"></i> XÁC NHẬN TẠO LỊCH (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="tech-card p-5 text-center shadow-sm" style="border-radius: 20px; background: white;">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            </div>
            <h4 class="fw-bold">Tuyệt vời!</h4>
            <p class="text-muted">Tất cả thang máy đến hạn đã được lên lịch bảo trì đầy đủ.</p>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary rounded-pill px-5">Quay lại Dashboard</a>
        </div>
    @endif

@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            min-height: 45px;
            padding-top: 5px;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #4e73df;
            border: none;
            color: white;
            border-radius: 4px;
            padding: 2px 8px;
            font-weight: 500;
            margin-top: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: transparent;
            color: #f8f9fc;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                allowClear: true
            });

            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.elevator-checkbox');
            const countSpan = document.getElementById('selectedCount');
            const btnSubmit = document.getElementById('btnSubmit');

            function updateCount() {
                const count = document.querySelectorAll('.elevator-checkbox:checked').length;
                countSpan.textContent = count;
                btnSubmit.disabled = count === 0;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateCount();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateCount();
                    if (!this.checked) selectAll.checked = false;
                    if (document.querySelectorAll('.elevator-checkbox:checked').length ===
                        checkboxes.length) selectAll.checked = true;
                });
            });

            // Form validation before submit
            document.getElementById('bulkScheduleForm')?.addEventListener('submit', function(e) {
                const count = document.querySelectorAll('.elevator-checkbox:checked').length;
                if (count === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một thang máy.');
                }
            });

            updateCount();
        });
    </script>
@endsection
