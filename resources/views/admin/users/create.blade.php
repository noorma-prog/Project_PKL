@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Tambah Pengguna Baru')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" placeholder="Andi Pratama" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" placeholder="nama@polda.go.id" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="role" class="form-label">Peran <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">Pilih peran</option>
                        <option value="padal" {{ old('role') == 'padal' ? 'selected' : '' }}>Padal</option>
                        <option value="teknisi" {{ old('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="nrp_nip" class="form-label">NRP / NIP <span class="text-danger">*</span></label>
                    <input type="text" name="nrp_nip" id="nrp_nip" class="form-control @error('nrp_nip') is-invalid @enderror" 
                           value="{{ old('nrp_nip') }}" placeholder="000001" required>
                    @error('nrp_nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                           value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx">
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="satuan_kerja" class="form-label">Satuan Kerja <span class="text-danger">*</span></label>
                    <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror" 
                           value="{{ old('satuan_kerja') }}" placeholder="Bid TIK" required>
                    @error('satuan_kerja')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="alamat" class="form-label">Alamat (Opsional)</label>
                <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                       value="{{ old('alamat') }}" placeholder="Alamat lengkap">
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info mb-4" role="alert">
                <strong class="d-block mb-1">Aktivasi melalui email</strong>
                <span>Akun baru tersedia untuk Padal atau Teknisi. Undangan untuk mengatur kata sandi akan dikirim ke email pengguna.</span>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
