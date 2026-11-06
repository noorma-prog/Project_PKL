@extends('layouts.auth')

@section('title', 'Aktivasi Akun')

@section('content')
<div class="mb-4">
    <h3 class="auth-form-title">Aktivasi Akun</h3>
    <p class="auth-form-sub">Sistem Helpdesk & Pelayanan Teknologi Informasi</p>
</div>

@if(session('error'))
    <div class="alert alert-warning small mb-3" role="alert">{{ session('error') }}</div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="mb-3">
        <label class="form-label">Alamat Email</label>
        <input type="text" class="form-control bg-light" value="{{ $email }}" disabled>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Kata Sandi Baru <span class="text-danger">*</span></label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
               placeholder="Minimal 8 karakter" minlength="8" required autofocus>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
               placeholder="Ulangi kata sandi" minlength="8" required>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Aktifkan Akun</button>
</form>
@endsection
