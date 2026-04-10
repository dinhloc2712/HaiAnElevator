@extends('layouts.admin')

@section('title', 'Tạo Tài Liệu')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Tạo Tài Liệu</h1>
            <p class="mb-0 text-muted small">Điền thông tin vào mẫu để xuất file Word</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.media.index') }}"
                class="btn btn-light rounded-pill px-3 shadow-sm border fw-bold text-nowrap">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div x-data="documentGenerator()" class="row">
        {{-- Form Section --}}
        <div class="col-lg-8 mb-4">
            <div class="tech-card mb-4"
                style="border: 0; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05); border-radius: 12px; overflow: hidden;">
                <div class="tech-header"
                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 15px 25px;">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center justify-content-between w-100">
                        <div>
                            <i class="fas fa-pen me-2 bg-white bg-opacity-25 rounded-circle p-2"
                                style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"></i>
                            Nhập Liệu
                        </div>
                        <button type="submit" form="generate-form"
                            class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center text-primary shadow-sm"
                            style="width: 36px; height: 36px;" title="Tải xuống">
                            <i class="fas fa-download"></i>
                        </button>
                    </h6>
                </div>

                <div class="card-body p-4 bg-white"
                    style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <form id="generate-form" action="{{ route('admin.media.generate', $filename) }}" method="POST">
                        @csrf

                        {{-- Table Mode Toggle --}}
                        <div
                            class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4 border shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-table fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Chế độ Bảng (Table Mode)</h6>
                                    <p class="mb-0 text-muted small">Bật tính năng này nếu bạn muốn nhập danh sách nhiều
                                        dòng.</p>
                                </div>
                            </div>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="tableModeToggle"
                                    x-model="tableMode" name="table_mode" value="1" style="cursor:pointer;">
                            </div>
                        </div>

                        {{-- Table Columns Selection --}}
                        <div x-show="tableMode" x-collapse x-cloak>
                            <div class="p-4 border rounded-3 bg-white mb-4 shadow-sm">
                                <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size: 0.8rem;">
                                    <i class="fas fa-list-check me-1"></i> Chọn cột cho bảng
                                </h6>
                                <div class="row g-3 variables-grid">
                                    @foreach ($variables as $var)
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-check custom-checkbox px-3 py-2 border rounded-3 bg-light"
                                                style="cursor: pointer;" @click="$refs.check_{{ md5($var) }}.click()">
                                                <input class="form-check-input me-2 mt-1" type="checkbox"
                                                    name="table_cols[]" value="{{ $var }}" x-model="selectedCols"
                                                    id="col_{{ md5($var) }}" x-ref="check_{{ md5($var) }}"
                                                    @click.stop>
                                                <label class="form-check-label fw-bold small text-dark w-100"
                                                    for="col_{{ md5($var) }}"
                                                    style="cursor: pointer; word-break: break-all;">
                                                    {{ $var }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 pt-3 border-top">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 fw-bold text-warning">
                                            <i class="fas fa-anchor me-1"></i> Anchor tự động:
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0 fw-bold text-dark"
                                            name="table_anchor" x-model="tableAnchor"
                                            placeholder="(Dòng chứa biến này sẽ được nhân bản)" style="font-size: 0.85rem;"
                                            :required="tableMode && selectedCols.length > 0">
                                    </div>
                                </div>
                            </div>

                            {{-- Dynamic Table Rows Input --}}
                            <div class="mb-4" x-show="selectedCols.length > 0" x-collapse x-cloak>
                                <div
                                    class="d-flex align-items-center justify-content-between mb-3 pl-2 border-start border-4 border-info">
                                    <h6 class="fw-bold text-dark mb-0 form-section-title ps-2 text-uppercase"
                                        style="font-size: 0.85rem; letter-spacing: 0.5px;">Dữ Liệu Bảng</h6>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm fw-bold"
                                        @click="addRow()">
                                        <i class="fas fa-plus me-1"></i> Thêm dòng
                                    </button>
                                </div>

                                <div class="table-responsive bg-white rounded-3 border shadow-sm"
                                    style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-bordered table-hover mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <template x-for="col in selectedCols" :key="col">
                                                    <th class="fw-bold text-muted" x-text="col"></th>
                                                </template>
                                                <th class="text-center" style="width: 60px;"><i class="fas fa-cog"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, index) in tableRows" :key="index">
                                                <tr>
                                                    <td class="text-center text-muted fw-bold align-middle"
                                                        x-text="index + 1"></td>
                                                    <template x-for="col in selectedCols" :key="col">
                                                        <td>
                                                            <input type="text" :name="col + '[]'"
                                                                class="form-control form-control-sm border-0 bg-light rounded-pill px-3"
                                                                :placeholder="'Nhập ' + col">
                                                        </td>
                                                    </template>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                                            @click="removeRow(index)" x-show="tableRows.length > 1"
                                                            title="Xóa dòng">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- General Information --}}
                        <div class="pl-2 border-start border-4 border-primary mb-3 mt-2"
                            x-show="getGeneralVars().length > 0">
                            <h6 class="fw-bold text-dark mb-0 form-section-title ps-2 text-uppercase"
                                style="font-size: 0.85rem; letter-spacing: 0.5px;">Thông Tin Chung</h6>
                        </div>

                        <div class="row g-3">
                            <template x-for="varName in getGeneralVars()" :key="varName">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-floating shadow-sm">
                                        <input type="text" :name="varName" :id="'var_' + varName"
                                            class="form-control rounded-3 border-light bg-light fw-bold text-dark"
                                            style="font-size: 0.9rem;" placeholder="Nhập giá trị">
                                        <label :for="'var_' + varName" class="text-muted small"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"
                                            x-text="varName"></label>
                                    </div>
                                </div>
                            </template>
                            <div class="col-12" x-show="getGeneralVars().length === 0">
                                <div
                                    class="alert alert-light text-center text-muted border-dashed rounded-3 shadow-sm py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i><br>
                                    Tất cả các biến đã được chuyển vào Bảng.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4 mb-4">
            {{-- File Info Card --}}
            <div class="tech-card mb-4"
                style="border: 0; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05); border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4 bg-white">
                    <h6 class="fw-bold text-muted mb-4 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                        Thông Tin Mẫu</h6>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 40px; height: 40px;">
                            <i class="fas fa-file-word fa-lg"></i>
                        </div>
                        <div style="min-width: 0;">
                            <p class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Tên File
                            </p>
                            <p class="fw-bold text-dark mb-0 text-truncate" title="{{ $filename }}">
                                {{ $filename }}</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 40px; height: 40px;">
                            <i class="fas fa-tag fa-lg"></i>
                        </div>
                        <div>
                            <p class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Mô tả</p>
                            <p class="text-dark mb-0 small">Bản mẫu Word từ hệ thống</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 40px; height: 40px;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div>
                            <p class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Nội dung
                            </p>
                            <p class="text-dark mb-0 small">Tự động nhận diện <span
                                    class="badge bg-primary rounded-pill px-2">{{ count($variables) }}</span> biến
                                (variable) cần điền.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tips Card --}}
            <div class="tech-card"
                style="border: 0; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05); border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4 bg-primary text-white"
                    style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-lightbulb text-warning me-2 fa-lg"></i> Mẹo nhỏ
                    </h6>
                    <p class="small mb-0 text-white-50" style="line-height: 1.6;">
                        Sử dụng chế độ <strong>Table Mode</strong> để tạo danh sách tự động. Hệ thống sẽ tự tìm dòng trong
                        file Word chứa <strong>"Anchor tự động"</strong> bạn chọn và nhân bản nó.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-checkbox:hover {
            background-color: #e9ecef !important;
            border-color: #dee2e6 !important;
            transition: all 0.2s;
        }

        .form-floating>.form-control:focus~label {
            color: #0d6efd;
            opacity: 1;
        }

        .form-floating>.form-control {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-floating {
            border-radius: 8px;
            overflow: hidden;
        }

        .form-floating>label {
            padding: 1rem 1rem;
            z-index: 2;
            pointer-events: none;
        }
    </style>

@section('scripts')
    <script>
        function documentGenerator() {
            let savedConfig = @json($savedConfig) || {
                tableMode: false,
                selectedCols: [],
                tableAnchor: ''
            };

            return {
                variables: @json($variables),
                tableMode: savedConfig.tableMode || false,
                selectedCols: savedConfig.selectedCols || [],
                tableRows: [{}],
                tableAnchor: savedConfig.tableAnchor || '',

                init() {
                    // Ensure saved columns still exist in the current variables (in case template changed)
                    this.selectedCols = this.selectedCols.filter(c => this.variables.includes(c));

                    // Auto-select anchor when variables are selected
                    this.$watch('selectedCols', (value) => {
                        if (value.length > 0 && !this.tableAnchor) {
                            this.tableAnchor = value[0]; // default to first selected col
                        }
                        if (value.length === 0) {
                            this.tableAnchor = '';
                        }
                        this.saveConfig();
                    });

                    this.$watch('tableMode', () => this.saveConfig());
                    this.$watch('tableAnchor', () => this.saveConfig());
                },

                saveConfig() {
                    const configData = {
                        tableMode: this.tableMode,
                        selectedCols: this.selectedCols,
                        tableAnchor: this.tableAnchor
                    };

                    fetch("{{ route('admin.media.save-config', $filename) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            config: configData
                        })
                    }).catch(e => console.error('Lỗi khi lưu cấu hình', e));
                },

                getGeneralVars() {
                    if (!this.tableMode) return this.variables;
                    return this.variables.filter(v => !this.selectedCols.includes(v));
                },

                addRow() {
                    this.tableRows.push({});
                },

                removeRow(index) {
                    if (this.tableRows.length > 1) {
                        this.tableRows.splice(index, 1);
                    }
                }
            };
        }
    </script>
@endsection
@endsection
