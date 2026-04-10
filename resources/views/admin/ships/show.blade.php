@extends('layouts.admin')

@section('title', 'Thông tin Tàu thuyền')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Thông tin Tàu thuyền</h1>
            <p class="text-muted small mb-0">Chi tiết hồ sơ tàu: <strong>{{ $ship->registration_number }}</strong></p>
        </div>
        <div>
            <a href="{{ route('admin.ships.edit', $ship) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.ships.index') }}" class="btn btn-tech-outline">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>


    {{-- Tab Navigation --}}
    <div class="d-flex mb-4">
        <ul class="nav nav-pills rounded-pill bg-white shadow-sm p-2 border" id="shipTabs" role="tablist">
            <li role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2 fw-bold d-flex align-items-center custom-pill-btn"
                    id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab"
                    aria-controls="info" aria-selected="true">
                    <i class="fas fa-info-circle me-2"></i> Thông tin chung
                </button>
            </li>
            <li role="presentation">
                <button
                    class="nav-link rounded-pill px-4 py-2 fw-bold d-flex align-items-center ms-1 custom-pill-btn text-muted"
                    id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab"
                    aria-controls="history" aria-selected="false">
                    <i class="fas fa-history me-2"></i> Lịch sử xét duyệt
                    @if ($proposals->count() > 0)
                        <span class="badge rounded-pill ms-2 badge-count"
                            style="background-color: #e3e6f0; color: #5a5c69;">{{ $proposals->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <style>
        .custom-pill-btn {
            transition: all 0.2s ease-in-out;
            color: #5a5c69;
            /* text-muted equivalent */
        }

        .custom-pill-btn:not(.active):hover {
            background-color: #eff2f7;
            color: #4e73df;
        }

        .custom-pill-btn.active {
            background-color: #4e73df !important;
            color: white !important;
        }

        .custom-pill-btn.active .badge-count {
            background-color: white !important;
            color: #4e73df !important;
        }
    </style>

    {{-- Tab Content --}}
    <div class="tab-content" id="shipTabsContent">
        {{-- General Info Tab --}}
        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
            <div class="row">
                {{-- Main Info --}}
                <div class="col-md-8">
                    {{-- General Info (Registration + Technical) --}}
                    <div class="tech-card mb-4">
                        <div class="tech-header">
                            <h5 class="m-0 fw-bold"><i class="fas fa-info-circle me-2"></i> Thông tin chung</h5>
                        </div>
                        <div class="card-body p-4">
                            {{-- Registration --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase fw-bold">Số đăng ký</label>
                                    <div class="fs-5 fw-bold text-primary">{{ $ship->registration_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase fw-bold">Trạng thái</label>
                                    <div>
                                        @if ($ship->status == 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Hoạt
                                                động</span>
                                        @elseif($ship->status == 'suspended')
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Đình
                                                chỉ</span>
                                        @else
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">{{ ucfirst($ship->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Technical Info --}}
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted text-uppercase fw-bold">Tên tàu</label>
                                    <div class="fw-bold">{{ $ship->name ?? 'Chưa cập nhật' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted text-uppercase fw-bold">Số hiệu</label>
                                    <div class="fw-bold">{{ $ship->hull_number ?? 'Chưa cập nhật' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted text-uppercase fw-bold">Công dụng</label>
                                    <div>{{ $ship->main_occupation ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted text-uppercase fw-bold">Hạn đăng kiểm</label>
                                    <div>{{ $ship->expiration_date->format('d/m/Y') ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Công dụng</label>
                                    <div>{{ $ship->secondary_occupation ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Vùng hoạt động</label>
                                    <div>{{ $ship->operation_area ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Số thuyền viên</label>
                                    <div>{{ $ship->crew_size ?? 0 }} người</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Vật liệu vỏ</label>
                                    <div>{{ $ship->hull_material ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Năm đóng</label>
                                    <div>{{ $ship->build_year ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Nơi đóng</label>
                                    <div>{{ $ship->build_place ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tech-card mb-4">
                        <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                            <h5 class="m-0 fw-bold"><i class="fas fa-ruler-combined me-2"></i> Thông số Kích thước & Trọng
                                tải</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Tổng dung tích</label>
                                    <div class="fw-bold">{{ $ship->gross_tonnage ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Trọng tải</label>
                                    <div class="fw-bold">{{ $ship->deadweight ? $ship->deadweight . ' tấn' : '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Ltk (m)</label>
                                    <div class="fw-bold">{{ $ship->length_design ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Btk (m)</label>
                                    <div class="fw-bold">{{ $ship->width_design ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Lmax (m)</label>
                                    <div class="fw-bold">{{ $ship->length_max ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Bmax (m)</label>
                                    <div class="fw-bold">{{ $ship->width_max ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Dmax (m)</label>
                                    <div class="fw-bold">{{ $ship->depth_max ?? '-' }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted">Mớn nước (d)</label>
                                    <div class="fw-bold">{{ $ship->draft ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ATKT Info --}}
                    <div class="tech-card mb-4">
                        <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                            <h5 class="m-0 fw-bold"><i class="fas fa-shield-alt me-2"></i> Hồ Sơ An Toàn Kỹ Thuật (ATKT)
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">Số ATKT</label>
                                    <div class="fw-bold text-primary">{{ $ship->technical_safety_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">Ngày cấp ATKT</label>
                                    <div class="fw-bold">
                                        {{ $ship->technical_safety_date ? $ship->technical_safety_date->format('d/m/Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">Số Biên bản</label>
                                    <div class="fw-bold text-primary">{{ $ship->record_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted">Ngày cấp BB</label>
                                    <div class="fw-bold">
                                        {{ $ship->record_date ? $ship->record_date->format('d/m/Y') : '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Owner Info --}}
                    <div class="tech-card mb-4">
                        <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                            <h5 class="m-0 fw-bold"><i class="fas fa-user-tie me-2"></i> Thông tin Chủ phương tiện</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Họ và tên</label>
                                    <div class="fw-bold">{{ $ship->owner_name }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">CMND/CCCD</label>
                                    <div>{{ $ship->owner_id_card ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted">Điện thoại</label>
                                    <div>{{ $ship->owner_phone ?? '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="small text-muted">Địa chỉ</label>
                                    <div>
                                        {{ $ship->address ? $ship->address . ', ' : '' }}
                                        {{ $ship->ward_id ? $ship->ward_id . ', ' : '' }}
                                        {{ $ship->province_id ? $ship->province_id . ', ' : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Sidebar Info --}}
                <div class="col-md-4">
                    {{-- Engine Info --}}
                    <div class="tech-card mb-4">
                        <div class="tech-header"
                            style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: #fff;">
                            <h5 class="m-0 fw-bold"><i class="fas fa-cogs me-2"></i> Hệ Thống Máy</h5>
                        </div>
                        <div class="card-body p-4">
                            {{-- Máy chính --}}
                            <label class="small text-muted mb-2 fw-bold text-uppercase">Máy Chính</label>
                            @php
                                $hp_array = is_array($ship->engine_hp) ? $ship->engine_hp : [];
                                $kw_array = is_array($ship->engine_kw) ? $ship->engine_kw : [];
                                $mark_array = is_array($ship->engine_mark) ? $ship->engine_mark : [];
                                $number_array = is_array($ship->engine_number) ? $ship->engine_number : [];
                                $count = max(
                                    count($hp_array),
                                    count($kw_array),
                                    count($mark_array),
                                    count($number_array),
                                );
                            @endphp
                            @if ($count > 0)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width:40px;">#</th>
                                                <th>Ký hiệu</th>
                                                <th>Số máy</th>
                                                <th>HP</th>
                                                <th>KW</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < $count; $i++)
                                                <tr>
                                                    <td class="text-center">{{ $i + 1 }}</td>
                                                    <td class="fw-bold">{{ $mark_array[$i] ?? '-' }}</td>
                                                    <td>{{ $number_array[$i] ?? '-' }}</td>
                                                    <td><span
                                                            class="fw-bold text-primary">{{ $hp_array[$i] ?? '-' }}</span>
                                                    </td>
                                                    <td><span
                                                            class="fw-bold text-primary">{{ $kw_array[$i] ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted fst-italic mb-3">Chưa có thông tin máy chính</div>
                            @endif

                            {{-- Máy phụ --}}
                            <label class="small text-muted mb-2 fw-bold text-uppercase">Máy Phụ</label>
                            @php
                                $sub_hp_array = is_array($ship->sub_engine_hp) ? $ship->sub_engine_hp : [];
                                $sub_kw_array = is_array($ship->sub_engine_kw) ? $ship->sub_engine_kw : [];
                                $sub_mark_array = is_array($ship->sub_engine_mark) ? $ship->sub_engine_mark : [];
                                $sub_number_array = is_array($ship->sub_engine_number) ? $ship->sub_engine_number : [];
                                $sub_count = max(
                                    count($sub_hp_array),
                                    count($sub_kw_array),
                                    count($sub_mark_array),
                                    count($sub_number_array),
                                );
                            @endphp
                            @if ($sub_count > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width:40px;">#</th>
                                                <th>Ký hiệu</th>
                                                <th>Số máy</th>
                                                <th>HP</th>
                                                <th>KW</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < $sub_count; $i++)
                                                <tr>
                                                    <td class="text-center">{{ $i + 1 }}</td>
                                                    <td class="fw-bold">{{ $sub_mark_array[$i] ?? '-' }}</td>
                                                    <td>{{ $sub_number_array[$i] ?? '-' }}</td>
                                                    <td><span
                                                            class="fw-bold text-info">{{ $sub_hp_array[$i] ?? '-' }}</span>
                                                    </td>
                                                    <td><span
                                                            class="fw-bold text-info">{{ $sub_kw_array[$i] ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted fst-italic">Chưa có thông tin máy phụ</div>
                            @endif
                        </div>
                    </div>
                    <div class="tech-card mb-4">
                        <div class="tech-header" style="background: linear-gradient(135deg, #858796 0%, #60616f 100%);">
                            <h5 class="m-0 fw-bold"><i class="fas fa-history me-2"></i> Lịch sử dữ liệu</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="small text-muted">Ngày tạo</label>
                                <div class="fw-bold">{{ $ship->created_at->format('H:i d/m/Y') }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted">Cập nhật lần cuối</label>
                                <div class="fw-bold">{{ $ship->updated_at->format('H:i d/m/Y') }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted">Ngày đăng ký</label>
                                <div class="fw-bold">
                                    {{ $ship->registration_date ? $ship->registration_date->format('d/m/Y') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Proposal History Tab --}}
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
            <div class="tech-card">
                <div class="tech-header">
                    <h5 class="m-0 fw-bold"><i class="fas fa-file-signature me-2"></i> Lịch sử xét duyệt hồ sơ</h5>
                </div>
                <div class="card-body p-0">
                    @if ($proposals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Tên hồ sơ</th>
                                        <th>Danh mục</th>
                                        <th>Người tạo</th>
                                        <th>Ngày tạo</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($proposals as $proposal)
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">{{ $proposal->title }}</td>
                                            <td>{{ $proposal->category ?? 'N/A' }}</td>
                                            <td>{{ $proposal->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $proposal->created_at->format('H:i d/m/Y') }}</td>
                                            <td class="text-center">
                                                @if ($proposal->status == 'pending')
                                                    <span
                                                        class="badge bg-warning bg-opacity-10 text-warning rounded-pill fw-bold px-3 py-1">Đang
                                                        chờ duyệt</span>
                                                @elseif($proposal->status == 'approved')
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold px-3 py-1">Đã
                                                        duyệt</span>
                                                @elseif($proposal->status == 'rejected')
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-bold px-3 py-1">Từ
                                                        chối</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('admin.proposals.index', ['id' => $proposal->id]) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="fas fa-folder-open fa-3x text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Chưa có hồ sơ xét duyệt</h6>
                                <p class="text-muted small mb-0">Tàu này chưa cấu hình hay tạo hồ sơ xét duyệt nào.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const placeholder = document.querySelector('.address-placeholder');
            if (placeholder) {
                const pId = placeholder.getAttribute('data-p');
                const wId = placeholder.getAttribute('data-w');

                if (pId) {
                    // Fetch Province Name
                    fetch('https://esgoo.net/api-tinhthanh-new/1/0.htm')
                        .then(response => response.json())
                        .then(data => {
                            if (data.error === 0) {
                                const province = data.data.find(item => item.id === pId);
                                let addressText = province ? province.full_name : '';

                                if (wId && province) {
                                    fetch(`https://esgoo.net/api-tinhthanh-new/2/${pId}.htm`)
                                        .then(response => response.json())
                                        .then(dData => {
                                            if (dData.error === 0) {
                                                const district = dData.data.find(item => item.id === wId);
                                                if (district) {
                                                    addressText = district.full_name + ', ' + addressText;
                                                }
                                                placeholder.textContent = addressText;
                                            }
                                        });
                                } else {
                                    placeholder.textContent = addressText;
                                }
                            }
                        });
                } else {
                    placeholder.textContent = '';
                }
            }
        });
    </script>
@endsection
