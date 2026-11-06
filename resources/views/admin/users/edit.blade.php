@extends('layouts.app')

@section('title', 'Perbarui Data Pengguna')
@section('page_title', 'Perbarui Data Pengguna')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $user->name) }}" placeholder="Andi Pratama" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" placeholder="nama@polda.go.id" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="role" class="form-label">Peran <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="padal" {{ old('role', $user->role) == 'padal' ? 'selected' : '' }}>Padal</option>
                        <option value="teknisi" {{ old('role', $user->role) == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User/Pelapor</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="nrp_nip" class="form-label">NRP / NIP <span class="text-danger">*</span></label>
                    <input type="text" name="nrp_nip" id="nrp_nip" class="form-control @error('nrp_nip') is-invalid @enderror" 
                           value="{{ old('nrp_nip', $user->nrp_nip) }}" placeholder="000001" required>
                    @error('nrp_nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                           value="{{ old('phone_number', $user->phone_number) }}" placeholder="08xxxxxxxxxx">
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="satuan_kerja" class="form-label">Satuan Kerja <span class="text-danger">*</span></label>
                    <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror" 
                           value="{{ old('satuan_kerja', $user->satuan_kerja) }}" placeholder="Bid TIK" required>
                    @error('satuan_kerja')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="is_active" class="form-label">Status Akun <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                    <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
