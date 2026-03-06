<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CBT Ujian') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
        }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .sidebar .nav-link i { width: 24px; text-align: center; margin-right: 10px; }
        .sidebar-brand {
            padding: 20px;
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 80px 20px 20px;
            min-height: 100vh;
        }
        .topbar {
            background: white;
            padding: 12px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            z-index: 998;
        }
        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; }
        .user-name-display {
            display: inline-block;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .user-name-display { max-width: 8ch; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding-top: 80px; }
            .topbar { left: 0; }
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 999;
        }
        .sidebar-backdrop.show { display: block; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Backdrop (mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i> CBT UJIAN
        </div>
        <nav class="nav flex-column mt-3">
            @role('admin')
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}" href="{{ route('admin.jurusan.index') }}">
                    <i class="bi bi-building"></i> Jurusan
                </a>
                <a class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}" href="{{ route('admin.kelas.index') }}">
                    <i class="bi bi-door-open"></i> Kelas
                </a>
                <a class="nav-link {{ request()->routeIs('admin.mapel.*') ? 'active' : '' }}" href="{{ route('admin.mapel.index') }}">
                    <i class="bi bi-book"></i> Mata Pelajaran
                </a>
                <a class="nav-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}" href="{{ route('admin.guru.index') }}">
                    <i class="bi bi-person-badge"></i> Guru
                </a>
                <a class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}" href="{{ route('admin.siswa.index') }}">
                    <i class="bi bi-people"></i> Siswa
                </a>
                <a class="nav-link {{ request()->routeIs('admin.ruang.*') ? 'active' : '' }}" href="{{ route('admin.ruang.index') }}">
                    <i class="bi bi-geo-alt"></i> Ruang Ujian
                </a>
                <a class="nav-link {{ request()->routeIs('admin.proktor.*') ? 'active' : '' }}" href="{{ route('admin.proktor.index') }}">
                    <i class="bi bi-person-workspace"></i> Proktor
                </a>
                <a class="nav-link {{ request()->routeIs('admin.ujian.*') ? 'active' : '' }}" href="{{ route('admin.ujian.index') }}">
                    <i class="bi bi-clipboard-check"></i> Ujian
                </a>
                <a class="nav-link {{ request()->routeIs('admin.hasil.*') ? 'active' : '' }}" href="{{ route('admin.hasil.index') }}">
                    <i class="bi bi-bar-chart-line"></i> Hasil & Nilai
                </a>
                <a class="nav-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}" href="{{ route('admin.import.index') }}">
                    <i class="bi bi-cloud-upload"></i> Import Data
                </a>
                <a class="nav-link {{ request()->routeIs('admin.monitor.*') ? 'active' : '' }}" href="{{ route('admin.monitor.index') }}">
                    <i class="bi bi-display"></i> Monitor Ujian
                </a>
            @endrole

            @role('guru')
                <a class="nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}" href="{{ route('guru.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('guru.soal.*') ? 'active' : '' }}" href="{{ route('guru.soal.index') }}">
                    <i class="bi bi-question-circle"></i> Bank Soal
                </a>
                <a class="nav-link {{ request()->routeIs('guru.ujian.*') ? 'active' : '' }}" href="{{ route('guru.ujian.index') }}">
                    <i class="bi bi-clipboard-check"></i> Ujian
                </a>
                <a class="nav-link {{ request()->routeIs('guru.hasil.*') ? 'active' : '' }}" href="{{ route('guru.hasil.index') }}">
                    <i class="bi bi-bar-chart"></i> Hasil & Nilai
                </a>
                <a class="nav-link {{ request()->routeIs('guru.analisis.*') ? 'active' : '' }}" href="{{ route('guru.analisis.index') }}">
                    <i class="bi bi-graph-up"></i> Analisis Soal
                </a>
            @endrole

            @role('proktor')
                <a class="nav-link {{ request()->routeIs('proktor.dashboard') ? 'active' : '' }}" href="{{ route('proktor.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('proktor.monitor.*') ? 'active' : '' }}" href="{{ route('proktor.monitor.index') }}">
                    <i class="bi bi-display"></i> Monitor Ujian
                </a>
            @endrole

            @role('siswa')
                <a class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" href="{{ route('siswa.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('siswa.ujian.*') ? 'active' : '' }}" href="{{ route('siswa.ujian.index') }}">
                    <i class="bi bi-clipboard-check"></i> Ujian
                </a>
                <a class="nav-link {{ request()->routeIs('siswa.riwayat') ? 'active' : '' }}" href="{{ route('siswa.riwayat') }}">
                    <i class="bi bi-clock-history"></i> Riwayat Ujian
                </a>
            @endrole
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <span class="fw-semibold">@yield('title', 'Dashboard')</span>
            </div>
            <div class="dropdown">
                <a class="btn btn-sm btn-light dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="user-name-display">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarBackdrop').classList.remove('show');
        }
    </script>

    <!-- MathJax 3 for equation rendering -->
    <script>
        window.MathJax = {
            tex: { inlineMath: [['\\(', '\\)']], displayMath: [['\\[', '\\]']] },
            options: { skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre'] },
            startup: { pageReady: () => MathJax.startup.defaultPageReady() }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>

    @stack('scripts')
</body>
</html>
