@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="mb-4 text-center">
    <h4 class="fw-bold text-dark mb-1">Masuk Akun</h4>
    <p class="text-muted small">Silakan masukkan email dan kata sandi Anda untuk melanjutkan</p>
</div>

<form action="{{ route('login.post') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email Dinas</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="contoh: nama@polda.go.id" required autofocus>
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold text-secondary mb-0">Kata Sandi</label>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control border-start-0 @error('password') is-invalid @enderror" 
                   placeholder="Masukkan kata sandi" required>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label small text-secondary" for="remember">
                Ingat sesi saya
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-police w-100 mb-3 shadow-sm">
        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
    </button>
</form>

<div class="text-center mb-4">
    <span class="text-muted small">Belum memiliki akun pelapor?</span>
    <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
        Daftar Akun Baru
    </a>
</div>

<div class="p-3 bg-light rounded-3 border">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="small fw-bold text-dark"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Akun Pengujian Cepat:</span>
        <span class="badge bg-secondary">Password: password</span>
    </div>
    <div class="d-grid gap-2">
        <button type="button" class="demo-pill d-flex justify-content-between align-items-center" onclick="fillLogin('admin@polda.go.id', 'password')">
            <span><strong>Admin:</strong> admin@polda.go.id</span>
            <span class="badge bg-danger">Admin</span>
        </button>
        <button type="button" class="demo-pill d-flex justify-content-between align-items-center" onclick="fillLogin('padal@polda.go.id', 'password')">
            <span><strong>Padal:</strong> padal@polda.go.id</span>
            <span class="badge bg-warning text-dark">Padal</span>
        </button>
        <button type="button" class="demo-pill d-flex justify-content-between align-items-center" onclick="fillLogin('teknisi1@polda.go.id', 'password')">
            <span><strong>Teknisi:</strong> teknisi1@polda.go.id</span>
            <span class="badge bg-info text-dark">Teknisi</span>
        </button>
        <button type="button" class="demo-pill d-flex justify-content-between align-items-center" onclick="fillLogin('user@polda.go.id', 'password')">
            <span><strong>Pelapor (User):</strong> user@polda.go.id</span>
            <span class="badge bg-primary">User</span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
