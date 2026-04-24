@extends('layouts.admin')

@section('title', 'Hoàn thành phiếu bảo trì')

@section('styles')
    <style>
        .checklist-section-title {
            padding: 10px 0;
            margin-bottom: 20px;
            margin-top: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .checklist-section-title h5 {
            margin: 0;
            font-weight: 800;
            color: #2d3748;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-pill {
            width: 6px;
            height: 32px;
            background: #3182ce;
            border-radius: 10px;
        }

        .checklist-item-card {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .checklist-item-card:hover {
            border-color: #3182ce;
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.1);
            transform: translateY(-2px);
        }

        .item-name-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .item-dot {
            width: 8px;
            height: 8px;
            background: #cbd5e0;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .item-label {
            font-weight: 700;
            color: #4a5568;
            font-size: 0.95rem;
        }

        .modern-select {
            height: 52px;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 600;
            color: #2d3748;
            background-color: #ffffff;
            transition: all 0.2s;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234a5568'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 20px;
        }

        .modern-select:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 4px rgba(49, 130, 206, 0.1);
            outline: none;
        }

        .legend-card {
            position: sticky;
            top: 20px;
        }

        .legend-item {
            font-size: 0.8rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .item-dot-legend {
            width: 8px;
            height: 8px;
            background: #3182ce;
            border-radius: 50%;
        }
    </style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Thực hiện kiểm tra & Hoàn thành phiếu</h1>
                <p class="mb-0 text-muted small">
                    Thang máy: <span class="fw-bold text-primary">{{ $selectedElevator->code }}</span> - {{ $selectedElevator->building->name ?? 'N/A' }}
                </p>
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
                <div class="tech-card mb-4" style="background: #f7fafc; border: none; box-shadow: none;">
                    <div class="card-body p-0">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Thang máy đang chọn</label>
                                <input type="hidden" name="elevator_id" value="{{ $selectedElevator->id }}">
                                <input type="text" class="form-control bg-white border-0 p-3 shadow-sm fw-bold text-primary" style="border-radius: 12px;" value="{{ $selectedElevator->code }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ngày thực hiện *</label>
                                <input type="date" name="check_date" class="form-control bg-white border-0 p-3 shadow-sm" style="border-radius: 12px;" value="{{ old('check_date', $maintenance->check_date ? $maintenance->check_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Loại hình *</label>
                                <select name="task_type" class="form-select bg-white border-0 p-3 shadow-sm" style="border-radius: 12px;" required>
                                    <option value="periodic" {{ $maintenance->task_type == 'periodic' ? 'selected' : '' }}>Bảo dưỡng định kỳ</option>
                                    <option value="repair" {{ $maintenance->task_type == 'repair' ? 'selected' : '' }}>Sửa chữa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Checklist Items Section --}}
                @if($maintenance->status == 'in_progress' || $maintenance->status == 'completed')
                <div class="mb-4">
                    @php 
                        $currentResults = is_array($maintenance->results) ? $maintenance->results : [];
                    @endphp
                    @foreach($sections as $sectionName => $items)
                        <div class="checklist-section-title">
                            <div class="section-pill"></div>
                            <h5>{{ $sectionName }}</h5>
                        </div>
                        <div class="row">
                            @foreach($items as $id => $name)
                                @php
                                    $savedValue = $currentResults[$id] ?? '';
                                @endphp
                                <div class="col-md-6">
                                    <div class="checklist-item-card">
                                        <div class="item-name-row">
                                            <div class="item-dot"></div>
                                            <div class="item-label">{{ $name }}</div>
                                        </div>
                                        <div class="status-selector mt-2">
                                            <select name="results[{{ $id }}]" class="form-select modern-select w-100">
                                                @foreach($symbols as $statusId => $statusName)
                                                    <option value="{{ $statusId }}" {{ $savedValue == $statusId ? 'selected' : '' }}>
                                                        {{ $statusName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-info rounded-4 border-0 shadow-sm p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Chưa thể nhập hạng mục bảo trì</h6>
                            <p class="small mb-0">Bạn cần nhấn "Bắt đầu thực hiện" ở danh sách để mở phần checklist kiểm tra.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Evaluation & Signatures --}}
                <div class="tech-card mb-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Phân loại lỗi / Sự cố (Nếu có)</label>
                                <div class="d-flex flex-wrap gap-4 mt-2">
                                    @php
                                        $savedFaults = is_array($maintenance->fault_category) ? $maintenance->fault_category : [];
                                    @endphp
                                    <div class="form-check form-check-inline custom-checkbox">
                                        <input class="form-check-input shadow-sm" type="checkbox" name="fault_category[]" value="Cơ khí" id="faultMec" {{ in_array('Cơ khí', $savedFaults) ? 'checked' : '' }} style="transform: scale(1.2); outline: none;">
                                        <label class="form-check-label ms-1 fw-bold text-dark" for="faultMec">Cơ khí</label>
                                    </div>
                                    <div class="form-check form-check-inline custom-checkbox">
                                        <input class="form-check-input shadow-sm" type="checkbox" name="fault_category[]" value="Hệ điều khiển" id="faultCtrl" {{ in_array('Hệ điều khiển', $savedFaults) ? 'checked' : '' }} style="transform: scale(1.2); outline: none;">
                                        <label class="form-check-label ms-1 fw-bold text-dark" for="faultCtrl">Hệ điều khiển</label>
                                    </div>
                                    <div class="form-check form-check-inline custom-checkbox">
                                        <input class="form-check-input shadow-sm" type="checkbox" name="fault_category[]" value="Điện" id="faultElec" {{ in_array('Điện', $savedFaults) ? 'checked' : '' }} style="transform: scale(1.2); outline: none;">
                                        <label class="form-check-label ms-1 fw-bold text-dark" for="faultElec">Điện</label>
                                    </div>
                                    <div class="form-check form-check-inline custom-checkbox">
                                        <input class="form-check-input shadow-sm" type="checkbox" name="fault_category[]" value="Khác" id="faultOtros" {{ in_array('Khác', $savedFaults) ? 'checked' : '' }} style="transform: scale(1.2); outline: none;">
                                        <label class="form-check-label ms-1 fw-bold text-dark" for="faultOtros">Khác</label>
                                    </div>
                                </div>
                            </div>
                            
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
                                @if($maintenance->status == 'in_progress' || $maintenance->status == 'completed')
                                    <select class="form-select border-0 p-3 rounded-4 shadow-sm fw-bold" style="background-color: #f8f9fc;" disabled>
                                        <option value="in_progress" {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                        <option value="completed" {{ $maintenance->status == 'completed' ? 'selected' : '' }}>Hoàn thành & Đi vào sử dụng</option>
                                    </select>
                                    {{-- Status will be updated by button 'action' --}}
                                @else
                                    <select name="status" class="form-select border-0 p-3 rounded-4 shadow-sm fw-bold" style="background-color: #f8f9fc;">
                                        <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                        <option value="overdue" {{ $maintenance->status == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    @if($maintenance->status == 'in_progress')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="submit" name="action" value="save" class="btn btn-info btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 py-3 text-white">
                                    <i class="fas fa-save me-2"></i> LƯU TẠM
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="action" value="complete" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 py-3">
                                    <i class="fas fa-check-circle me-2"></i> HOÀN THÀNH PHIẾU
                                </button>
                            </div>
                        </div>
                    @else
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 py-3">
                            <i class="fas fa-save me-2"></i> {{ $maintenance->status == 'completed' ? 'CẬP NHẬT PHIẾU BẢO TRÌ' : 'LƯU THAY ĐỔI' }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Legend Sidebar --}}
            <div class="col-lg-3">
                <div class="tech-card legend-card">
                    <div class="tech-header" style="background: #4e73df; color: white;">
                        <h6 class="mb-0 fw-bold small"><i class="fas fa-info-circle me-2"></i> TRẠNG THÁI KIỂM TRA</h6>
                    </div>
                    <div class="card-body p-3">
                        @foreach($symbols as $statusId => $statusName)
                            <div class="legend-item">
                                <div class="item-dot"></div>
                                <span class="small text-muted fw-bold">{{ $statusName }}</span>
                            </div>
                        @endforeach
                        
                        <hr class="my-3 opacity-5">
                        <div class="alert alert-info border-0 rounded-3 mb-0 p-2 small">
                            <i class="fas fa-lightbulb me-2"></i>
                            Mặc định tất cả hạng mục được chọn <strong>{{ reset($symbols) }}</strong>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
