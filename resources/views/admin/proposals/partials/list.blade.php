    <div class="col-lg-4 col-12 mb-4 d-flex flex-column">
        <div class="tech-card d-flex flex-column h-100 mb-0">
            <div class="tech-header"
                style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 18px 20px;">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                    <i class="fas fa-list-alt me-2 bg-white bg-opacity-25 rounded-circle p-2"
                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"></i>
                    Danh sách Đề xuất
                </h6>
            </div>

            <div class="card-body p-3 border-bottom z-index-1 bg-white h-100 d-flex flex-column">
                {{-- Filters --}}
                <div class="d-flex flex-wrap gap-2 mb-3 px-1">
                    <button class="btn btn-sm rounded-pill fw-bold"
                        :class="filter === 'all' ? 'btn-primary px-3' : 'btn-light text-secondary px-3'"
                        @click="filter = 'all'">Tất cả</button>
                    <button class="btn btn-sm rounded-pill fw-bold"
                        :class="filter === 'pending' ? 'btn-warning text-white px-3' : 'btn-light text-secondary px-3'"
                        @click="filter = 'pending'">Chờ duyệt</button>
                    <button class="btn btn-sm rounded-pill fw-bold"
                        :class="filter === 'approved' ? 'btn-success px-3' : 'btn-light text-secondary px-3'"
                        @click="filter = 'approved'">Đã duyệt</button>
                    <button class="btn btn-sm rounded-pill fw-bold"
                        :class="filter === 'rejected' ? 'btn-danger px-3' : 'btn-light text-secondary px-3'"
                        @click="filter = 'rejected'">Từ chối</button>
                </div>
                {{-- Search Bar --}}
                <div class="mb-3 px-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 bg-light"
                            placeholder="Tìm kiếm đề xuất (tên, người tạo, thể loại)..." x-model="searchQuery">
                    </div>
                </div>

                {{-- List --}}
                <div class="overflow-auto flex-grow-1 px-1" style="max-height: calc(100vh - 250px);">
                    <template x-for="p in filteredProposals" :key="p.id">
                        <div class="card mb-2 border-1 cursor-pointer transition-all"
                            :class="selected && selected.id === p.id ? 'border-primary bg-primary bg-opacity-10 shadow-sm' :
                                'border-light-subtle hover-shadow'"
                            @click="selectProposal(p)" style="border-radius: 12px; cursor: pointer;">
                            <div class="card-body p-3 relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span
                                            class="badge bg-light text-secondary border px-2 py-1 rounded-pill mb-1 d-inline-block"
                                            style="font-size: 0.7rem;" x-text="p.category"></span>
                                        <span x-show="p.paid_amount"
                                            class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1 mb-1 ms-1 d-inline-block"
                                            style="font-size: 0.7rem;"><i class="fas fa-coins me-1"></i><span
                                                x-text="formatCurrency(p.paid_amount)"></span></span>
                                        <span x-show="p.amount"
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1 mb-1 ms-1 d-inline-block"
                                            style="font-size: 0.7rem;"><i class="fas fa-coins me-1"></i><span
                                                x-text="formatCurrency(p.amount)"></span></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <template
                                            x-if="p.steps && p.steps.some(s => s.attachment_urls && s.attachment_urls.length > 0)">
                                            <div>
                                                <a href="#" @click.stop="$el.nextElementSibling.querySelector('a')?.click()" 
                                                    class="text-info text-decoration-none bg-info bg-opacity-10 rounded-circle p-1" title="Nhấn để xem toàn bộ file" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-paperclip" style="font-size:0.75rem;"></i>
                                                </a>
                                                <div class="d-none">
                                                    <template x-for="step in p.steps">
                                                        <template x-if="step.attachment_urls">
                                                            <template x-for="file in step.attachment_urls">
                                                                <a :href="file.url"
                                                                    :data-fancybox="'gallery-list-' + p.id"
                                                                    :data-type="file.filename.toLowerCase().endsWith('.pdf') ? 'pdf' : (file.filename.toLowerCase().match(/\.(jpe?g|png|gif|webp)$/i) ? 'image' : 'iframe')"
                                                                    :data-caption="file.filename"></a>
                                                            </template>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <span x-show="p.status === 'pending'" class="text-warning"><i
                                                class="far fa-clock"></i></span>
                                        <span x-show="p.status === 'approved'" class="text-success"><i
                                                class="far fa-check-circle"></i></span>
                                        <span x-show="p.status === 'rejected'" class="text-danger"><i
                                                class="far fa-times-circle"></i></span>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark" x-text="p.title"></h6>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted"><i class="far fa-user me-1"></i> <span
                                            x-text="p.creator.name"></span></small>
                                    <small class="text-muted" style="font-size: 0.7rem;"
                                        x-text="formatDate(p.created_at)"></small>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="filteredProposals.length === 0" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <p class="small mb-0">Không có đề xuất nào.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
