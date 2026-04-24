@extends('layouts.admin')

@section('title', 'Lập phiếu bảo trì thang máy')

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
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Phiếu kiểm tra kỹ thuật</h1>
                <p class="mb-0 text-muted small">
                    @if($selectedElevator)
                        Thang máy: <span class="fw-bold text-primary">{{ $selectedElevator->code }}</span> - {{ $selectedElevator->building->name ?? 'N/A' }}
                    @else
                        Nội dung bảo trì định kỳ theo tiêu chuẩn Hải An Elevator
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>
    </div>

    <form action="{{ route('admin.maintenance.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-9">
                {{-- Header Info Section --}}
                <div class="tech-card mb-4" style="background: #f7fafc; border: none; box-shadow: none;">
                    <div class="card-body p-0">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Chọn Thang máy</label>
                                <select name="elevator_id" class="form-select modern-form-control p-3 bg-white border-0 shadow-sm" style="border-radius: 12px;" onchange="window.location.href='?elevator_id='+this.value">
                                    <option value="">-- Chọn thang máy --</option>
                                    @foreach($elevators as $elevator)
                                        <option value="{{ $elevator->id }}" {{ ($selectedElevator && $selectedElevator->id == $elevator->id) ? 'selected' : '' }}>
                                            {{ $elevator->code }} ({{ $elevator->building->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ngày thực hiện</label>
                                <input type="date" name="check_date" class="form-control bg-white border-0 p-3 shadow-sm" style="border-radius: 12px;" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Loại hình</label>
                                <select name="task_type" class="form-select bg-white border-0 p-3 shadow-sm" style="border-radius: 12px;" required>
                                    <option value="periodic">Bảo dưỡng định kỳ</option>
                                    <option value="repair">Sửa chữa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Evaluation & Signatures --}}
                <div class="tech-card mb-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Đánh giá, nhận xét</label>
                                <textarea name="evaluation" class="form-control bg-light border-0 p-3 rounded-4" rows="3" placeholder="Ghi nhận tình trạng tổng quát sau bảo trì..."></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cán bộ kỹ thuật</label>
                                <div id="staff-container-create">
                                    <div class="d-flex mb-2 staff-row">
                                        <select name="staff_ids[]" class="form-select bg-light border-0 p-3 rounded-4" onchange="updateStaffOptionsCreate()">
                                            <option value="">-- Chọn nhân viên --</option>
                                            @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary rounded-4 ms-2 px-3 fw-bold" onclick="addStaffRowCreate(this)">+</button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function updateStaffOptionsCreate() {
                                    const container = document.getElementById('staff-container-create');
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

                                function addStaffRowCreate(btn) {
                                    const container = document.getElementById('staff-container-create');
                                    const firstRow = container.querySelector('.staff-row');
                                    const newRow = firstRow.cloneNode(true);
                                    
                                    newRow.querySelector('select').value = '';
                                    
                                    const actionBtn = newRow.querySelector('button');
                                    actionBtn.className = 'btn btn-outline-danger rounded-4 ms-2 px-3 fw-bold';
                                    actionBtn.textContent = '-';
                                    actionBtn.setAttribute('onclick', 'removeStaffRowCreate(this)');
                                    
                                    container.appendChild(newRow);
                                    updateStaffOptionsCreate();
                                }
                                function removeStaffRowCreate(btn) {
                                    btn.closest('.staff-row').remove();
                                    updateStaffOptionsCreate();
                                }
                                
                                // Initialize on load
                                document.addEventListener('DOMContentLoaded', updateStaffOptionsCreate);
                            </script>

                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Số người thực hiện</label>
                                        <input type="number" name="performer_count" class="form-control bg-light border-0 p-3 rounded-4" value="1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú khác</label>
                                        <input type="text" name="notes" class="form-control bg-light border-0 p-3 rounded-4">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Bắt đầu lúc</label>
                                        <input type="time" name="start_time" class="form-control bg-light border-0 p-3 rounded-4">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Kết thúc lúc</label>
                                        <input type="time" name="end_time" class="form-control bg-light border-0 p-3 rounded-4">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 py-3">
                        <i class="fas fa-save me-2"></i> LƯU PHIẾU BẢO TRÌ
                    </button>
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
