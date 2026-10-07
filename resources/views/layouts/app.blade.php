<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js 4.4.2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- Alpine.js 3.x -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.8/dist/cdn.min.js"></script>

    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --brand-primary: {{ \App\Models\WorkshopSetting::get('primary_color', '#0d6efd') }};
        }
        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background-color: #0f172a;
            color: #94a3b8;
            min-height: 100vh;
            flex-shrink: 0;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.2rem;
            transition: all 0.15s ease;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
            font-weight: 600;
        }
        .sidebar .sidebar-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 0.75rem 1rem 0.35rem 1rem;
        }
        .top-navbar {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1020;
        }
        .main-content {
            flex-grow: 1;
            min-width: 0;
        }
        .card {
            border-radius: 0.75rem;
        }
        .badge {
            border-radius: 0.375rem;
        }
        .btn {
            border-radius: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column">

    <!-- TOP NAVBAR -->
    <header class="navbar navbar-expand top-navbar sticky-top px-3 px-lg-4 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <!-- Mobile Offcanvas Toggle Button (< lg) -->
            <button class="btn btn-light d-lg-none border p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Toggle navigation">
                <i class="bi bi-list fs-5"></i>
            </button>

            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark m-0" href="{{ route('dashboard') }}">
                <span class="bg-primary text-white rounded p-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-wrench-adjustable-circle-fill fs-5"></i>
                </span>
                <span class="fs-6 d-none d-sm-inline">{{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</span>
            </a>
        </div>

        <!-- Right User & Quick Actions -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- Work Order Quick Search -->
            <form action="{{ route('work-orders.index') }}" method="GET" class="d-none d-md-flex align-items-center">
                <div class="input-group input-group-sm" style="width: 240px;">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari WO / Plat / Nama...">
                </div>
            </form>

            <!-- Notifications Icon Badge -->
            <div class="dropdown">
                <button class="btn btn-light rounded-circle position-relative p-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    @php
                        $waitingCount = \App\Models\WorkOrder::where('status', 'WAITING_APPROVAL')->count();
                    @endphp
                    @if($waitingCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $waitingCount }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 290px;">
                    <li class="dropdown-header fw-bold">Notifikasi Operasional</li>
                    <li><hr class="dropdown-divider"></li>
                    @if($waitingCount > 0)
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-start gap-2" href="{{ route('approvals.index') }}">
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $waitingCount }} WO Menunggu Approval</div>
                                    <small class="text-muted">Perlu konfirmasi WhatsApp pelanggan</small>
                                </div>
                            </a>
                        </li>
                    @else
                        <li><span class="dropdown-item text-muted text-center py-2">Semua approval terkendali</span></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        @php
                            $lowStockCount = \App\Models\Part::whereColumn('stock', '<=', 'min_stock')->count();
                        @endphp
                        @if($lowStockCount > 0)
                            <a class="dropdown-item py-2 d-flex align-items-start gap-2" href="{{ route('parts.index') }}">
                                <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $lowStockCount }} Sparepart Stok Kritis</div>
                                    <small class="text-muted">Perlu segera restock/pembelian</small>
                                </div>
                            </a>
                        @else
                            <span class="dropdown-item text-muted text-center py-2">Stok aman</span>
                        @endif
                    </li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light border d-flex align-items-center gap-2 py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="text-start d-none d-md-block" style="line-height: 1.2;">
                        <span class="fw-bold d-block text-dark small">{{ auth()->user()->name ?? 'User' }}</span>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ strtoupper(auth()->user()->role ?? 'GUEST') }}</small>
                    </div>
                    <i class="bi bi-chevron-down text-muted small ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li class="dropdown-header small text-muted">Masuk sebagai: <strong>{{ auth()->user()->email ?? '-' }}</strong></li>
                    <li><hr class="dropdown-divider"></li>
                    @if(auth()->user()?->isOwner())
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Pengaturan Bengkel</a></li>
                    @endif
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- WRAPPER: SIDEBAR + CONTENT -->
    <div class="d-flex flex-grow-1">

        <!-- DESKTOP SIDEBAR (>= lg) -->
        <aside class="sidebar d-none d-lg-block p-3">
            @include('layouts.sidebar-nav')
        </aside>

        <!-- MOBILE / TABLET OFFCANVAS SIDEBAR (< lg) -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width: 270px; background-color: #0f172a !important;">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-primary text-white rounded p-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-wrench-adjustable-circle-fill fs-5"></i>
                    </span>
                    <h5 class="offcanvas-title fw-bold fs-6 mb-0 text-white" id="sidebarOffcanvasLabel">SIM BENGKEL</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-3">
                @include('layouts.sidebar-nav')
            </div>
        </div>

        <!-- MAIN CONTENT AREA -->
        <main class="main-content p-3 p-lg-4">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <x-alert variant="success" icon="check-circle-fill">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if(session('error'))
                <x-alert variant="danger" icon="exclamation-octagon-fill">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if(session('warning'))
                <x-alert variant="warning" icon="exclamation-triangle-fill">
                    {{ session('warning') }}
                </x-alert>
            @endif

            <!-- Page Title / Breadcrumb Slot -->
            @yield('header')

            <!-- Main Page Content -->
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
