@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Informasi akun dan pengaturan keamanan kata sandi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i> Informasi Personil / Pengguna</h6>
            </div>
            <div class="card-body p-4">
                @if($user->isAdmin() && $user->requiresEmailVerification() && !$user->hasVerifiedEmail())
                    <div class="alert alert-warning small" role="alert">
                        Email baru <strong>{{ $user->email }}</strong> belum terverifikasi. Dashboard memerlukan verifikasi email.
                        <form action="{{ route('verification.send') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Kirim Ulang Tautan Verifikasi</button>
                        </form>
                    </div>
                @endif
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email Dinas (Login)</label>
                            @if($user->isAdmin())
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Perubahan email memerlukan kata sandi saat ini dan verifikasi melalui tautan yang dikirim ke email baru.</small>
                            @else
                                <input type="text" class="form-control bg-light" value="{{ $user->email }}" disabled>
                                <small class="text-muted">Email dikelola sesuai kebijakan akun Anda.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Peran Akun (Role)</label>
                            <input type="text" class="form-control bg-light text-uppercase fw-bold" value="{{ $user->role }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small text-secondary">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nrp_nip" class="form-label fw-semibold small text-secondary">NRP / NIP</label>
                            <input type="text" name="nrp_nip" id="nrp_nip" class="form-control @error('nrp_nip') is-invalid @enderror" 
                                   value="{{ old('nrp_nip', $user->nrp_nip) }}">
                            @error('nrp_nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label fw-semibold small text-secondary">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                                   value="{{ old('phone_number', $user->phone_number) }}">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="satuan_kerja" class="form-label fw-semibold small text-secondary">Satuan Kerja / Bagian / Fungsi</label>
                        <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror" 
                               value="{{ old('satuan_kerja', $user->satuan_kerja) }}">
                        @error('satuan_kerja')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-warning me-2"></i> Ubah Kata Sandi (Opsional)</h6>
                    <p class="text-muted small">Kosongkan kolom berikut jika tidak bermaksud mengubah kata sandi Anda.</p>

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold small text-secondary">
                            Kata Sandi Saat Ini
                            @if($user->isAdmin()) <span class="text-muted">(wajib saat mengganti email atau kata sandi)</span> @endif
                        </label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label fw-semibold small text-secondary">Kata Sandi Baru</label>
                            <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" 
                                   placeholder="Minimal 6 karakter">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="new_password_confirmation" class="form-label fw-semibold small text-secondary">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
