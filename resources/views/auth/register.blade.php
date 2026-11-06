@extends('layouts.auth')

@section('title', 'Pendaftaran Pelapor')

@section('content')
<div class="mb-4">
    <h3 class="auth-form-title">Pendaftaran Akun</h3>
    <p class="auth-form-sub">Sistem Helpdesk & Pelayanan Teknologi Informasi</p>
</div>

@if(session('error'))
    <div class="alert alert-warning small mb-3" role="alert">
        <i class="bi bi-shield-lock-fill me-1"></i>{{ session('error') }}
    </div>
@endif

@if($errors->has('role'))
    <div class="alert alert-warning small mb-3" role="alert">{{ $errors->first('role') }}</div>
@endif

<form action="{{ route('register.post') }}" method="POST">
    @csrf

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" placeholder="Andi Saputra" required autofocus>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="nrp_nip" class="form-label">NRP / NIP <span class="text-danger">*</span></label>
            <input type="text" name="nrp_nip" id="nrp_nip" class="form-control @error('nrp_nip') is-invalid @enderror" 
                   value="{{ old('nrp_nip') }}" placeholder="000000" required>
            @error('nrp_nip')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="phone_number" class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
            <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                   value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx" required>
            @error('phone_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="satuan_kerja" class="form-label">Satuan Kerja / Bagian <span class="text-danger">*</span></label>
            <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror" 
                   value="{{ old('satuan_kerja') }}" placeholder="SPKT" required>
            @error('satuan_kerja')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="alamat" class="form-label">Alamat (Opsional)</label>
            <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                   value="{{ old('alamat') }}" placeholder="Alamat lengkap">
            @error('alamat')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="nama@polda.go.id" required>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Minimal 6 karakter" required>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                   placeholder="Ulangi kata sandi" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3" id="register-button">
        Daftar Akun Baru
    </button>

    <div class="text-center pt-2">
        <span class="text-muted small">Sudah memiliki akun?</span>
        <a href="{{ route('login') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
            Masuk
        </a>
    </div>
</form>
@endsection
