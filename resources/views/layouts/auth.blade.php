<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - LayanIT POLDA</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- LayanIT Civic Blue Theme -->
    <link rel="stylesheet" href="{{ asset('css/redesign.css') }}?v={{ file_exists(public_path('css/redesign.css')) ? filemtime(public_path('css/redesign.css')) : time() }}">
    @yield('styles')
</head>
<body data-design="A" class="auth-page">

    <div class="auth-split-container">
        <!-- Hero Pane (Civic Blue Brand & Info) -->
        <div class="auth-hero-pane">
            <div>
                <div class="auth-hero-brand">
                    <img src="{{ asset('images/logo LAYANIT.png') }}" alt="Logo LayanIT">
                    <div>
                        <div class="brand-title">LayanIT</div>
                        <div class="brand-sub">POLDA / BID TIK</div>
                    </div>
                </div>
                <h2 class="auth-hero-heading">Layanan IT.<br>Lebih terarah.</h2>
                <p class="auth-hero-desc">Laporkan kendala. Pantau penanganan. Berikan penilaian pelayanan.</p>
            </div>
            <div class="auth-hero-pill">
                <div class="pill-title">Satu layanan, satu alur yang jelas.</div>
                <p class="pill-sub">Pelapor / Admin / Padal / Teknisi</p>
            </div>
        </div>

        <!-- Form Pane -->
        <div class="auth-form-pane">
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
