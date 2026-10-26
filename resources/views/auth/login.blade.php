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
        <label for="account_type" class="form-label small fw-semibold text-secondary">Pilih Jenis Akun</label>
        <select name="account_type" id="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
            <option value="" selected disabled>Pilih jenis akun</option>
            <option value="admin" {{ old('account_type') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="padal" {{ old('account_type') === 'padal' ? 'selected' : '' }}>Padal</option>
            <option value="teknisi" {{ old('account_type') === 'teknisi' ? 'selected' : '' }}>Teknisi</option>
            <option value="user" {{ old('account_type') === 'user' ? 'selected' : '' }}>User/Pelapor</option>
        </select>
        <div class="form-text small">Pilihan ini hanya untuk memilih jenis akses; hak akses tetap diverifikasi dari database.</div>
        @error('account_type')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email Dinas</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                   value="" placeholder="contoh: nama@polda.go.id" autocomplete="off" required autofocus>
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
                   value="" placeholder="Masukkan kata sandi" autocomplete="off" required>
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

<div class="text-center">
    <span class="text-muted small">Belum memiliki akun pelapor?</span>
    <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
        Daftar Akun Baru
    </a>
</div>
@endsection
