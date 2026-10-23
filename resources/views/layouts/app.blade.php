<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LayanIT POLDA</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --police-navy: #0f1d38;
            --police-navy-light: #182a4d;
            --police-blue: #1e3a8a;
            --police-accent: #2563eb;
            --police-gold: #d97706;
            --police-gold-hover: #b45309;
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --bg-page: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--police-navy) 0%, #0a1329 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.15);
        }

        .sidebar-brand .logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #fbbf24;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 1.4rem;
        }

        .sidebar-brand .brand-text {
            line-height: 1.2;
        }

        .sidebar-brand .brand-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .sidebar-brand .brand-sub {
            color: #94a3b8;
            font-size: 0.75rem;
            margin: 0;
        }

        .sidebar-nav {
            padding: 1.25rem 0.85rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #64748b;
            padding: 0.5rem 0.85rem;
            margin-top: 0.75rem;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.7rem 1rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar .nav-link:hover i {
            color: #fbbf24;
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            font-weight: 600;
        }

        .sidebar .nav-link.active i {
            color: #ffffff;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Topbar Header */
        .topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .content-area {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Role Badges */
        .role-badge-admin {
            background-color: #ef4444;
            color: #ffffff;
        }
        .role-badge-padal {
            background-color: #f59e0b;
            color: #000000;
        }
        .role-badge-teknisi {
            background-color: #06b6d4;
            color: #ffffff;
        }
        .role-badge-user {
            background-color: #3b82f6;
            color: #ffffff;
        }

        /* Card Custom */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        /* Stat Card */
        .stat-card {
            border-radius: 0.85rem;
            background: #ffffff;
            padding: 1.35rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Responsive Mobile */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div class="brand-text">
                <div class="brand-title">LayanIT</div>
                <div class="brand-sub">POLDA - Bid TIK</div>
            </div>
        </div>

        <div class="sidebar-nav">
            @php
                $role = Auth::user()->role;
            @endphp

            @if($role === 'user')
                <div class="nav-section-title">Menu Pelapor</div>
                <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('user.tickets.create') }}" class="nav-link {{ request()->routeIs('user.tickets.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Buat Laporan Baru</span>
                </a>
                <a href="{{ route('user.tickets.index') }}" class="nav-link {{ request()->routeIs('user.tickets.index*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i>
                    <span>Laporan Saya</span>
                </a>
            @elseif($role === 'admin')
                <div class="nav-section-title">Menu Administrator</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-detailed"></i>
                    <span>Kelola Laporan</span>
                </a>
                <a href="{{ route('admin.reports.recap') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Rekapitulasi Laporan</span>
                </a>

                <div class="nav-section-title">Master Data</div>
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Data Pengguna</span>
                </a>
                <a href="{{ route('admin.padals.index') }}" class="nav-link {{ request()->routeIs('admin.padals.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-shaded"></i>
                    <span>Unit Padal</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Kategori Gangguan</span>
                </a>
                <a href="{{ route('admin.ratings.index') }}" class="nav-link {{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i>
                    <span>Rating & Ulasan</span>
                </a>
            @elseif($role === 'padal')
                <div class="nav-section-title">Menu Padal (Perwira Pengendali)</div>
                <a href="{{ route('padal.dashboard') }}" class="nav-link {{ request()->routeIs('padal.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('padal.tickets.index') }}" class="nav-link {{ request()->routeIs('padal.tickets.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Tiket Penugasan</span>
                </a>
                <a href="{{ route('padal.members.index') }}" class="nav-link {{ request()->routeIs('padal.members.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Kelola Anggota Tim</span>
                </a>
                <a href="{{ route('padal.ratings.index') }}" class="nav-link {{ request()->routeIs('padal.ratings.*') ? 'active' : '' }}">
                    <i class="bi bi-star-half"></i>
                    <span>Penilaian Pelayanan</span>
                </a>
            @elseif($role === 'teknisi')
                <div class="nav-section-title">Menu Teknisi (Read-Only)</div>
                <a href="{{ route('teknisi.dashboard') }}" class="nav-link {{ request()->routeIs('teknisi.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('teknisi.tickets.index') }}" class="nav-link {{ request()->routeIs('teknisi.tickets.*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i>
                    <span>Daftar Penugasan Tim</span>
                </a>
            @endif

            <div class="nav-section-title">Akun & Pengaturan</div>
            <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profil Saya</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between text-white">
                <div class="text-truncate me-2">
                    <div class="small fw-bold text-truncate">{{ Auth::user()->name }}</div>
                    <div class="text-white-50" style="font-size: 0.72rem;">{{ Auth::user()->satuan_kerja ?? 'POLDA' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Keluar">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" type="button" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Dashboard')</h5>
                    <small class="text-muted">@yield('page_subtitle', 'Sistem Helpdesk IT POLDA')</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill px-3 py-2 text-uppercase fw-bold role-badge-{{ Auth::user()->role }}">
                    Role: {{ Auth::user()->role }}
                </span>

                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" 
                            style="width: 42px; height: 42px;" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill fs-5 text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
