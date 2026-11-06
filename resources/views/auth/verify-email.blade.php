@extends('layouts.auth')

@section('title', 'Verifikasi Email')

@section('content')
<div class="mb-4">
    <h3 class="auth-form-title">Verifikasi Email</h3>
    <p class="auth-form-sub">Sistem Helpdesk & Pelayanan Teknologi Informasi</p>
</div>

<div class="alert alert-info d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
    <div>
        <strong class="d-block mb-1">Periksa kotak masuk email</strong>
        <span>Tautan dikirim ke <strong>{{ auth()->user()->email }}</strong>. Periksa juga folder Spam atau Promosi.</span>
    </div>
</div>

<form action="{{ route('verification.send') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-primary w-100 mb-3">
        Kirim Ulang Tautan Verifikasi
    </button>
</form>

<form action="{{ route('logout') }}" method="POST" class="text-center">
    @csrf
    <button type="submit" class="btn btn-outline-secondary w-100">Kembali ke Login</button>
</form>
@endsection
