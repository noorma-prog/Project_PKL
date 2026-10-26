@extends('layouts.auth')

@section('title', 'Aktivasi Akun')

@section('content')
<div class="mb-4 text-center">
    <h4 class="fw-bold text-dark mb-1">Aktivasi Akun</h4>
    <p class="text-muted small">Atur kata sandi untuk mengaktifkan akun LayanIT Anda</p>
</div>

@if(session('error'))
    <div class="alert alert-warning small" role="alert">{{ session('error') }}</div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi Baru</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required autofocus>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8" required>
    </div>

    <button type="submit" class="btn btn-police w-100 mb-3">Atur Kata Sandi & Aktifkan Akun</button>
</form>
@endsection
