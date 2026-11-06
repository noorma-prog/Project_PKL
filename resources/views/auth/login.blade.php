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
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
               value="" placeholder="Masukkan kata sandi" autocomplete="off" required>
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

<div class="text-center pt-2">
    <span class="text-muted small">Belum memiliki akun pelapor?</span>
    <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
        Daftar Akun Baru
    </a>
</div>
@endsection
