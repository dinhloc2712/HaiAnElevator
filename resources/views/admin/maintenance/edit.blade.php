@extends('layouts.admin')

@section('title', 'Hoàn thành phiếu bảo trì')

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
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .checklist-item:hover {
            background-color: #fcfdfe;
        }

        .symbol-selector {
            display: flex;
            gap: 4px;
        }

        .symbol-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            color: #858796;
        }

        .symbol-btn:hover {
            border-color: #4e73df;
            color: #4e73df;
            background: #f8f9fc;
        }

        input[type="radio"]:checked+.symbol-btn {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
        }

        .legend-card {
            position: sticky;
            top: 20px;
        }

        .legend-item {
            font-size: 0.8rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-symbol {
            width: 24px;
            height: 24px;
            min-width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4e73df;
            color: white;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Thực hiện kiểm tra & Hoàn thành lịch</h1>
                <p class="mb-0 text-muted small">Cập nhật kết quả chi tiết để hoàn tất công việc được giao</p>
            </div>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Trở về lịch
            </a>
        </div>
    </div>

    <form action="{{ route('admin.maintenance.update', $maintenance->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-9">
                {{-- Header Info Section --}}
                <div class="tech-card mb-4">
                    <div class="tech-header" style="background: white; border-bottom: 1px solid #f1f3f9;">
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-info-circle me-2 text-primary"></i> Thông tin chung
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Thang máy đang chọn</label>
                                <input type="hidden" name="elevator_id" value="{{ $selectedElevator->id }}">
                                <input type="text" class="form-control bg-light border-0 p-3 rounded-4 fw-bold text-primary" value="{{ $selectedElevator->code }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ngày thực hiện *</label>
                                <input type="date" name="check_date" class="form-control bg-light border-0 p-3 rounded-4" value="{{ old('check_date', $maintenance->check_date ? $maintenance->check_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Loại hình *</label>
                                <select name="task_type" class="form-select bg-light border-0 p-3 rounded-4" required>
                                    <option value="periodic" {{ $maintenance->task_type == 'periodic' ? 'selected' : '' }}>Bảo dưỡng định kỳ</option>
                                    <option value="repair" {{ $maintenance->task_type == 'repair' ? 'selected' : '' }}>Sửa chữa</option>
                                </select>
                            </div>

                            @if($selectedElevator)
                                <div class="col-md-12">
                                    <div class="p-3 rounded-4 border bg-light bg-opacity-50">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="small text-muted">Công trình:</div>
                                                <div class="fw-bold">{{ $selectedElevator->building->name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="small text-muted">Địa chỉ:</div>
                                                <div class="fw-bold small">{{ $selectedElevator->building->address ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted">Thông số:</div>
                                                <div class="fw-bold">{{ $selectedElevator->type ?? 'Thang máy' }} - {{ $selectedElevator->capacity ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Checklist Items Section --}}
                <div class="tech-card mb-4">
                    <div class="tech-header" style="background: white; border-bottom: 1px solid #f1f3f9;">
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-list-check me-2 text-primary"></i> Danh mục các hạng mục kiểm tra
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @php 
                                $count = 0; 
                                $currentResults = is_array($maintenance->results) ? $maintenance->results : [];
                            @endphp
                            @foreach($sections as $sectionName => $items)
                                <div class="col-md-6">
                                    <div class="checklist-section-title">{{ $sectionName }}</div>
                                    <div class="border rounded-4 overflow-hidden">
                                        @foreach($items as $id => $name)
                                            @php
                                                $savedValue = $currentResults[$id] ?? 'Δ';
                                            @endphp
                                            <div class="checklist-item">
                                                <div class="d-flex align-items-start gap-3">
                                                    <span class="text-muted small fw-bold" style="min-width: 20px;">{{ $id }}.</span>
                                                    <span class="small text-dark fw-bold">{{ $name }}</span>
                                                </div>
                                                <div class="symbol-selector">
                                                    @foreach($symbols as $symbol => $desc)
                                                        <label class="mb-0">
                                                            <input type="radio" name="results[{{ $id }}]" value="{{ $symbol }}" 
                                                                   class="d-none" {{ $savedValue == $symbol ? 'checked' : '' }}>
                                                            <div class="symbol-btn" title="{{ $desc }}">{{ $symbol }}</div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $count++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Evaluation & Signatures --}}
                <div class="tech-card mb-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Đánh giá, nhận xét</label>
                                <textarea name="evaluation" class="form-control bg-light border-0 p-3 rounded-4" rows="3" placeholder="Ghi nhận tình trạng tổng quát sau bảo trì...">{{ old('evaluation', $maintenance->evaluation) }}</textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cán bộ kỹ thuật</label>
                                <div id="staff-container">
                                    @php
                                        $selectedStaffIds = is_array($maintenance->staff_ids) ? $maintenance->staff_ids : [];
                                        if (empty($selectedStaffIds)) $selectedStaffIds = ['']; // At least one empty row
                                    @endphp
                                    @foreach($selectedStaffIds as $index => $selectedId)
                                        <div class="d-flex mb-2 staff-row">
                                            <select name="staff_ids[]" class="form-select bg-light border-0 p-3 rounded-4" onchange="updateStaffOptionsEdit()">
                                                <option value="">-- Chọn nhân viên --</option>
                                                @foreach($staffs as $staff)
                                                    <option value="{{ $staff->id }}" {{ $staff->id == $selectedId ? 'selected' : '' }}>
                                                        {{ $staff->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($index == 0)
                                                <button type="button" class="btn btn-primary rounded-4 ms-2 px-3 fw-bold" onclick="addStaffRow(this)">+</button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger rounded-4 ms-2 px-3 fw-bold" onclick="removeStaffRow(this)">-</button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <script>
                                function updateStaffOptionsEdit() {
                                    const container = document.getElementById('staff-container');
                                    const selects = Array.from(container.querySelectorAll('select'));
                                    const selectedValues = selects.map(s => s.value).filter(v => v !== "");

                                    selects.forEach(select => {
                                        const options = select.querySelectorAll('option');
                                        options.forEach(option => {
                                            if (option.value === "") return;
                                            if (selectedValues.includes(option.value) && option.value !== select.value) {
                                                option.style.display = 'none';
                                                option.disabled = true;
                                            } else {
                                                option.style.display = 'block';
                                                option.disabled = false;
                                            }
                                        });
                                    });
                                }

                                function addStaffRow(btn) {
                                    const container = document.getElementById('staff-container');
                                    const firstRow = container.querySelector('.staff-row');
                                    const newRow = firstRow.cloneNode(true);
                                    
                                    // Reset value
                                    newRow.querySelector('select').value = '';
                                    
                                    // Change button to minus
                                    const actionBtn = newRow.querySelector('button');
                                    actionBtn.className = 'btn btn-outline-danger rounded-4 ms-2 px-3 fw-bold';
                                    actionBtn.textContent = '-';
                                    actionBtn.setAttribute('onclick', 'removeStaffRow(this)');
                                    
                                    container.appendChild(newRow);
                                    updateStaffOptionsEdit();
                                }
                                function removeStaffRow(btn) {
                                    btn.closest('.staff-row').remove();
                                    updateStaffOptionsEdit();
                                }
                                
                                // Initialize on load
                                document.addEventListener('DOMContentLoaded', updateStaffOptionsEdit);
                            </script>

                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Số người thực hiện</label>
                                        <input type="number" name="performer_count" class="form-control bg-light border-0 p-3 rounded-4" value="{{ old('performer_count', $maintenance->performer_count ?? 1) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú khác</label>
                                        <input type="text" name="notes" class="form-control bg-light border-0 p-3 rounded-4" value="{{ old('notes', $maintenance->notes) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Bắt đầu lúc</label>
                                        <input type="time" name="start_time" class="form-control bg-light border-0 p-3 rounded-4" value="{{ old('start_time', $maintenance->start_time) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Kết thúc lúc</label>
                                        <input type="time" name="end_time" class="form-control bg-light border-0 p-3 rounded-4" value="{{ old('end_time', $maintenance->end_time) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4 opacity-50 w-100">

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark text-uppercase"><i class="fas fa-tag me-1 text-primary"></i> Trạng thái bảo trì</label>
                                <select name="status" class="form-select border-0 p-3 rounded-4 shadow-sm fw-bold" style="background-color: #f8f9fc;">
                                    <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="overdue" {{ $maintenance->status == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                                    <option value="in_progress" {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                    <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>Hoàn thành & Đi vào sử dụng</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 py-3">
                        <i class="fas fa-save me-2"></i> LƯU THAY ĐỔI LỊCH / PHIẾU BẢO TRÌ
                    </button>
                </div>
            </div>

            {{-- Legend Sidebar --}}
            <div class="col-lg-3">
                <div class="tech-card legend-card">
                    <div class="tech-header" style="background: #4e73df; color: white;">
                        <h6 class="mb-0 fw-bold small"><i class="fas fa-key me-2"></i> KÝ HIỆU GHI CHÚ</h6>
                    </div>
                    <div class="card-body p-3">
                        @foreach($symbols as $symbol => $desc)
                            <div class="legend-item">
                                <span class="legend-symbol">{{ $symbol }}</span>
                                <span class="small text-muted fw-bold">{{ $desc }}</span>
                            </div>
                        @endforeach
                        
                        <hr class="my-3 opacity-5">
                        <div class="alert alert-warning border-0 rounded-3 mb-0 p-2 small">
                            <i class="fas fa-lightbulb me-2"></i>
                            Mặc định tất cả hạng mục được chọn <strong>Bình thường (Δ)</strong>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
