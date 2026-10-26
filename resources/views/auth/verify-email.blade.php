@extends('layouts.auth')

@section('title', 'Verifikasi Email')

@section('content')
<div class="text-center mb-4">
    <div class="mb-3">
        <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2">Verifikasi Alamat Email</h4>
    <p class="text-muted small">
        Verifikasi alamat email diperlukan sebelum Anda dapat menggunakan akun. Jika belum menerima tautan, Anda dapat meminta pengiriman ulang.
    </p>
</div>

@if(session('success'))
    <div class="alert alert-success small" role="alert">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-warning small" role="alert">{{ session('error') }}</div>
@endif

<form action="{{ route('verification.send') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-police w-100 mb-3">
        <i class="bi bi-arrow-clockwise me-2"></i>Kirim Ulang Tautan Verifikasi
    </button>
</form>

<form action="{{ route('logout') }}" method="POST" class="text-center">
    @csrf
    <button type="submit" class="btn btn-link btn-sm text-decoration-none">Kembali ke Login</button>
</form>
@endsection
