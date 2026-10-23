<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - LayanIT POLDA</title>
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
            --police-blue: #1e3a8a;
            --police-gold: #d97706;
            --police-gold-hover: #b45309;
            --accent-glow: rgba(30, 58, 138, 0.15);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image:
                linear-gradient(rgba(11, 19, 41, 0.78), rgba(23, 37, 84, 0.72)),
                url("{{ asset('images/gambar%20polda%20kalsel.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 520px;
        }
        .auth-header {
            background: linear-gradient(135deg, var(--police-navy) 0%, var(--police-blue) 100%);
            color: white;
            padding: 2.25rem 2rem;
            text-align: center;
            position: relative;
        }
        .auth-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d97706, #fbbf24, #d97706);
        }
        .badge-logo {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(251, 191, 36, 0.6);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 0 20px rgba(217, 119, 6, 0.3);
        }
        .badge-logo i {
            font-size: 2.25rem;
            color: #fbbf24;
        }
        .auth-body {
            padding: 2.25rem 2rem;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--police-blue);
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15);
        }
        .btn-police {
            background: linear-gradient(135deg, var(--police-blue) 0%, #1d4ed8 100%);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-police:hover {
            background: linear-gradient(135deg, #172554 0%, var(--police-blue) 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }
        .demo-pill {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.375rem;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            text-align: left;
        }
        .demo-pill:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }
        @media (max-width: 576px) {
            body {
                align-items: flex-start;
                padding: 1rem 0.75rem;
                background-position: center;
            }
            .auth-card {
                border-radius: 0.75rem;
            }
            .auth-header {
                padding: 1.75rem 1rem;
            }
            .auth-body {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <div class="badge-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3 class="fw-bold mb-1 tracking-tight text-white">LayanIT POLDA</h3>
            <p class="text-white-50 mb-0 small">Sistem Helpdesk & Pelayanan Teknologi Informasi</p>
        </div>

        <div class="auth-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
