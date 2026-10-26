@extends('layouts.auth')

@section('title', 'Pendaftaran Pelapor')

@section('content')
<div class="mb-4 text-center">
    <h4 class="fw-bold text-dark mb-1">Pendaftaran Akun</h4>
    <p class="text-muted small">Daftar sebagai User/Pelapor untuk membuat laporan layanan IT</p>
</div>

@if(session('error'))
    <div class="alert alert-warning small" role="alert">
        <i class="bi bi-shield-lock-fill me-1"></i>{{ session('error') }}
    </div>
@endif

@if($errors->has('role'))
    <div class="alert alert-warning small" role="alert">{{ $errors->first('role') }}</div>
@endif

<form action="{{ route('register.post') }}" method="POST">
    @csrf

    <div class="alert alert-light border small" id="role-information">
        Akun Padal dan Teknisi dikelola oleh Admin.
    </div>

    <div class="mb-3">
        <label for="name" class="form-label small fw-semibold text-secondary">Nama Lengkap & Gelar</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
            <input type="text" name="name" id="name" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" placeholder="contoh: Bripka Agus Setiawan, S.H" required autofocus>
        </div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nrp_nip" class="form-label small fw-semibold text-secondary">NRP / NIP</label>
            <input type="text" name="nrp_nip" id="nrp_nip" class="form-control @error('nrp_nip') is-invalid @enderror" 
                   value="{{ old('nrp_nip') }}" placeholder="88010xxx / 1988xxx" required>
            @error('nrp_nip')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="phone_number" class="form-label small fw-semibold text-secondary">No. WhatsApp / HP</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                   value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx" required>
            @error('phone_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="satuan_kerja" class="form-label small fw-semibold text-secondary">Satuan Kerja / Bagian / Fungsi</label>
        <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror" 
               value="{{ old('satuan_kerja') }}" placeholder="contoh: SPKT, Ditlantas, Ditreskrimum, Bag Ops" required>
        @error('satuan_kerja')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="alamat" class="form-label small fw-semibold text-secondary">Alamat Kantor / Domisili</label>
        <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" 
               value="{{ old('alamat') }}" placeholder="contoh: Gedung SPKT Lantai 1 / Asrama POLDA">
        @error('alamat')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email Dinas</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="nama@polda.go.id" required>
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Minimal 6 karakter" required>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                   placeholder="Konfirmasi kata sandi" required>
        </div>
    </div>

    <button type="submit" class="btn btn-police w-100 mb-3 shadow-sm" id="register-button">
        <i class="bi bi-person-plus-fill me-2"></i> Daftar Sebagai Pelapor
    </button>
</form>

<div class="text-center">
    <span class="text-muted small">Sudah memiliki akun?</span>
    <a href="{{ route('login') }}" class="small fw-semibold text-primary text-decoration-none ms-1">
        Masuk di Sini
    </a>
</div>
@endsection
