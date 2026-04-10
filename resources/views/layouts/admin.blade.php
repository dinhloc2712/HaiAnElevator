<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị hệ thống') - Hải An Elevator</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Fancybox 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

    <!-- Pro Tech UI CSS -->
    <link href="{{ asset('css/admin-pro-tech.css') }}" rel="stylesheet">
    <!-- Admin Shared Components CSS -->
    <link href="{{ asset('css/admin-shared.css') }}" rel="stylesheet">

    <style>
        /* Only Critical Initial Load CSS if needed, otherwise empty as we moved to admin-pro-tech.css */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <img src="{{ asset('logo.png') }}" alt="Logo"
                    style="width: 48px; height: 48px; object-fit: contain;">
                <span>
                    Hải An
                    <small> Elevator </small>
                </span>
            </a>
        </div>

        <!-- Scrollable Middle Area -->
        <div class="sidebar-scroll-area">
            <!-- Search Bar -->
            <div class="sidebar-search px-3 pt-3 pb-2">
                <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 p-3 rounded-4 fw-bold"
                        id="sidebarSearch" placeholder="Tìm kiếm chức năng..." autocomplete="off"
                        style="border-radius: 20px; font-size: 0.85rem;">
                </div>
            </div>

            <!-- Menu -->
            <ul class="nav flex-column sidebar-menu" id="sidebarAccordion">
                @can('view_dashboard')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-th-large"></i> <span>Tổng quan</span>
                        </a>
                    </li>
                @endcan

                <!-- Tòa nhà & Khách hàng -->
                @can('view_building')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.buildings.*') ? 'active' : '' }}"
                            href="{{ route('admin.buildings.index') }}">
                            <i class="fas fa-building"></i> <span>Tòa nhà &amp; Khách hàng</span>
                        </a>
                    </li>
                @endcan

                <!-- Nav Item - Tin tuc (News) -->
                @can('view_news')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}"
                            href="{{ route('admin.news.index') }}">
                            <i class="fas fa-fw fa-bullhorn"></i> <span>Tin tức & Thông báo</span>
                        </a>
                    </li>
                @endcan

                <!-- System Admin -->
                @if (auth()->user()->can('view_user') || auth()->user()->can('view_role') || auth()->user()->can('view_branch'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.branches.*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#adminSubmenu" role="button"
                            aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.branches.*') ? 'true' : 'false' }}">
                            <i class="fas fa-user-shield"></i>
                            <span>Quản trị hệ thống</span>
                            <i class="fas fa-chevron-down ms-auto arrow" style="font-size: 0.8rem; width: auto;"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.branches.*') ? 'show' : '' }}"
                            id="adminSubmenu" data-bs-parent="#sidebarAccordion">
                            <ul class="nav flex-column collapse-menu">
                                @can('view_user')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                            href="{{ route('admin.users.index') }}">
                                            <i class="fas fa-users"></i> <span>Tài khoản</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_role')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                                            href="{{ route('admin.roles.index') }}">
                                            <i class="fas fa-key"></i> <span>Phân quyền</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_branch')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"
                                            href="{{ route('admin.branches.index') }}">
                                            <i class="fas fa-code-branch"></i> <span>Chi nhánh</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>{{-- end sidebar-scroll-area --}}

        <!-- Footer -->
        <div class="sidebar-footer">
            <a href="{{ route('admin.profile.show') }}" class="user-profile">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ auth()->user()->role->display_name ?? 'N/A' }}</span>
                </div>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="nav-link w-100 bg-transparent border-0 text-start">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm" style="height: 60px;">
            <div class="container-fluid">
                <button class="btn btn-link text-primary" onclick="toggleSidebar()">
                    <i class="fas fa-bars fa-lg"></i>
                </button>

                {{-- Mobile Brand (visible only on mobile) --}}
                <div class="d-md-none ms-3 fw-bold text-primary">TÀU CÁ NGHỆ AN</div>



                {{-- Topbar Navbar --}}
                <ul class="navbar-nav ms-auto align-items-center">
                    {{-- Notifications Dropdown --}}
                    @auth
                        @php
                            $unreadNewsCount = auth()->user()->unreadNewsCount();
                            $unreadNewsList = auth()->user()->unreadNews(5);
                        @endphp
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="alertsDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fa-lg text-primary"></i>
                                @if ($unreadNewsCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        style="font-size: 0.6rem; margin-top: 10px; margin-left: -10px;">
                                        {{ $unreadNewsCount > 9 ? '9+' : $unreadNewsCount }}
                                        <span class="visually-hidden">unread messages</span>
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in p-0 border-0"
                                aria-labelledby="alertsDropdown" style="width: 300px; max-height: 400px;">
                                <h6 class="dropdown-header text-white p-3 fw-bold rounded-top align-items-center d-flex justify-content-between"
                                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                    <div><i class="fas fa-bullhorn me-1"></i> Tin tức mới</div>
                                    @if ($unreadNewsCount > 0)
                                        <span
                                            class="badge bg-white text-primary rounded-pill">{{ $unreadNewsCount }}</span>
                                    @endif
                                </h6>

                                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                    @forelse($unreadNewsList as $news)
                                        <a class="list-group-item list-group-item-action border-bottom p-3"
                                            href="{{ route('admin.news.show', $news->id) }}">
                                            <div class="d-flex align-items-start">
                                                <div class="mr-3 me-3">
                                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;">
                                                        <i class="fas fa-file-alt"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="small text-muted mb-1">
                                                        {{ $news->created_at->diffForHumans() }}</div>
                                                    <span class="fw-bold text-dark d-block text-truncate"
                                                        style="max-width: 200px; font-size: 0.9rem;">{{ $news->title }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-4 text-center text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                                            <p class="mb-0 small">Bạn đã xem hết thông báo.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <a class="dropdown-item text-center small text-primary fw-bold p-3 bg-light border-top"
                                    style="border-radius: 0 0 0.5rem 0.5rem;" href="{{ route('admin.news.index') }}">
                                    Xem tất cả thông báo
                                </a>
                            </div>
                        </li>
                    @endauth
                </ul>
            </div>
        </nav>

        <div class="container-fluid px-4">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>

    <script>
        // Sidebar Search Logic
        document.getElementById('sidebarSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const sidebarItems = document.querySelectorAll('.sidebar-menu .nav-item');
            const submenus = document.querySelectorAll('.collapse');

            // Reset if empty
            if (searchTerm === '') {
                sidebarItems.forEach(item => item.style.display = '');
                submenus.forEach(submenu => {
                    // Only collapse if it wasn't active
                    if (!submenu.classList.contains('show') && !submenu.parentElement.querySelector(
                            '.active')) {
                        new bootstrap.Collapse(submenu, {
                            toggle: false
                        }).hide();
                    }
                });
                return;
            }

            sidebarItems.forEach(item => {
                const link = item.querySelector('.nav-link');
                if (!link) return;

                const text = link.textContent.toLowerCase();
                const isMatch = text.includes(searchTerm);

                if (isMatch) {
                    item.style.display = '';

                    // If matched item is inside a submenu, expand the submenu
                    const parentCollapse = item.closest('.collapse');
                    if (parentCollapse) {
                        // Ensure parent li is visible
                        const parentLi = parentCollapse.closest('.nav-item');
                        if (parentLi) parentLi.style.display = '';

                        // Expand
                        if (!parentCollapse.classList.contains('show')) {
                            new bootstrap.Collapse(parentCollapse, {
                                toggle: false
                            }).show();
                        }
                    }
                } else {
                    // Start by hiding, but check if children match
                    item.style.display = 'none';

                    // If current item creates a submenu, checks if any CHILDREN match
                    const subMenu = item.querySelector('.collapse');
                    if (subMenu) {
                        const hasVisibleChild = Array.from(subMenu.querySelectorAll('.nav-item')).some(
                            child => {
                                return child.textContent.toLowerCase().includes(searchTerm);
                            });

                        if (hasVisibleChild) {
                            item.style.display = ''; // Show parent
                            if (!subMenu.classList.contains('show')) {
                                new bootstrap.Collapse(subMenu, {
                                    toggle: false
                                }).show();
                            }
                        }
                    }
                }
            });
        });

        // Sidebar Toggle Logic
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                sidebar.classList.toggle('mobile-show');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                // Save state only for desktop
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        }

        // Initialize state
        document.addEventListener('DOMContentLoaded', () => {
            const isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                const sidebar = document.getElementById('sidebar');
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                }
            }

            // Close sidebar on route change if mobile (optional but good for UX)
            // ...

            // Keyboard shortcut for toggle (optional)
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'b') {
                    toggleSidebar();
                }
            });

            // Fancybox Init
            if (typeof Fancybox !== "undefined") {
                Fancybox.bind('[data-fancybox]', {
                    // Để mặc định để bật Toolbar có nút Download, Plugins...
                });
            }
        });

        // SweetAlert2 Toast Mixin
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Handle Session Messages
        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        @if ($errors->any())
            Toast.fire({
                icon: 'error',
                title: "Vui lòng kiểm tra lại dữ liệu nhập vào."
            });
        @endif
    </script>
    @yield('scripts')
</body>

</html>
