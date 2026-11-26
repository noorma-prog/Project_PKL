@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="mb-4">
    <h3 class="auth-form-title">Masuk Akun</h3>
    <p class="auth-form-sub">Sistem Helpdesk & Pelayanan Teknologi Informasi</p>
</div>

<form action="{{ route('login.post') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="account_type" class="form-label">Pilih Jenis Akun <span class="text-danger">*</span></label>
        <select name="account_type" id="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
            <option value="" selected disabled>Pilih jenis akun</option>
            <option value="user" {{ old('account_type') === 'user' ? 'selected' : '' }}>User/Pelapor</option>
            <option value="admin" {{ old('account_type') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="padal" {{ old('account_type') === 'padal' ? 'selected' : '' }}>Padal</option>
            <option value="teknisi" {{ old('account_type') === 'teknisi' ? 'selected' : '' }}>Teknisi</option>
        </select>
        <div class="form-text">Pilihan ini untuk memilih jenis akses; hak akses tetap diverifikasi dari database.</div>
        @error('account_type')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Alamat Email Dinas <span class="text-danger">*</span></label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
               value="{{ old('email', session('verified_email')) }}" placeholder="nama@polda.go.id" autocomplete="username" required autofocus>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                   value="" placeholder="Masukkan kata sandi" autocomplete="off" required>
            <button type="button" class="btn btn-outline-secondary" id="togglePassword" data-password-target="password" tabindex="-1" aria-label="Tampilkan kata sandi">
                <i class="bi bi-eye" id="togglePassword-icon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label small text-secondary" for="remember">
                Ingat sesi saya
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        Masuk Sekarang
    </button>
</form>

<div class="d-flex align-items-center gap-3 my-3" aria-hidden="true">
    <hr class="flex-grow-1 my-0">
    <span class="text-muted small">atau</span>
    <hr class="flex-grow-1 my-0">
</div>

<a href="{{ route('auth.google.redirect') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 mb-3">
    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" role="img">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.75 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
        <path fill="#FBBC05" d="M5.84 13.04a6.6 6.6 0 0 1 0-2.08V8.12H2.18a11 11 0 0 0 0 7.76l3.66-2.84z"/>
        <path fill="#EA4335" d="M12 4.51c1.62 0 3.06.56 4.21 1.66l3.15-3.15A10.54 10.54 0 0 0 12 0 11 11 0 0 0 2.18 8.12l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    <span>Masuk dengan Google</span>
</a>

<div class="text-center pt-2">
    <span class="text-muted small">Belum memiliki akun pelapor?</span>
    <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
        Daftar Akun Baru
    </a>
</div>
@endsection
