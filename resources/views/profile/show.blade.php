@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Informasi akun personil dan pengaturan keamanan kata sandi')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i> Informasi Personel & Akun</h6>
                    <small class="text-muted">Data identitas resmi akun Anda pada sistem helpdesk LayanIT</small>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase px-3 py-2 fw-bold">
                    {{ $user->role }}
                </span>
            </div>
            <div class="card-body p-4 p-md-5">
                @if($user->isAdmin() && $user->requiresEmailVerification() && !$user->hasVerifiedEmail())
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div>
                            <div class="fw-bold mb-1">Email Belum Diverifikasi</div>
                            <div class="small text-secondary mb-2">
                                Alamat email baru <strong>{{ $user->email }}</strong> belum diverifikasi. Seluruh fungsi pimpinan memerlukan verifikasi email.
                            </div>
                            <form action="{{ route('verification.send') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark fw-semibold">
                                    <i class="bi bi-send me-1"></i> Kirim Ulang Tautan Verifikasi
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email Dinas (Login Akun)</label>
                            @if($user->isAdmin())
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">Perubahan email memerlukan kata sandi saat ini dan verifikasi melalui tautan konfirmasi.</small>
                            @else
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0 text-muted" value="{{ $user->email }}" disabled>
                                </div>
                                <small class="text-muted d-block mt-1">Alamat email dikelola terpusat sesuai hak akses akun Anda.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Peran & Kewenangan Akun</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock-fill"></i></span>
                                <input type="text" class="form-control bg-light border-start-0 ps-0 text-uppercase fw-bold text-primary" value="{{ $user->role }}" disabled>
                            </div>
                            <small class="text-muted d-block mt-1">Tingkat hak akses pada portal LayanIT POLDA.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small text-secondary">Nama Lengkap & Pangkat / Gelar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required placeholder="Contoh: Bripka Ahmad Fauzi, S.Kom">
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nrp_nip" class="form-label fw-semibold small text-secondary">NRP / NIP Personel</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="nrp_nip" id="nrp_nip" class="form-control border-start-0 ps-0 @error('nrp_nip') is-invalid @enderror" 
                                       value="{{ old('nrp_nip', $user->nrp_nip) }}" placeholder="Nomor Registrasi Pokok / Pegawai">
                            </div>
                            @error('nrp_nip')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label fw-semibold small text-secondary">Nomor Kontak WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone_number" id="phone_number" class="form-control border-start-0 ps-0 @error('phone_number') is-invalid @enderror" 
                                       value="{{ old('phone_number', $user->phone_number) }}" placeholder="08xxxxxxxxxx">
                            </div>
                            @error('phone_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="satuan_kerja" class="form-label fw-semibold small text-secondary">Satuan Kerja / Bagian / Subdit</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                            <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control border-start-0 ps-0 @error('satuan_kerja') is-invalid @enderror" 
                                   value="{{ old('satuan_kerja', $user->satuan_kerja) }}" placeholder="Contoh: Ditreskrimsus / Subbag Renmin">
                        </div>
                        @error('satuan_kerja')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ubah Kata Sandi -->
                    <div class="p-4 rounded-3 bg-light border my-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-key-fill text-warning fs-5"></i>
                            <h6 class="fw-bold text-dark mb-0">Ubah Kata Sandi Akun (Opsional)</h6>
                        </div>
                        <p class="text-muted small mb-3">Kosongkan kolom di bawah jika Anda tidak ingin memperbarui kata sandi saat ini.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold small text-secondary">
                                Kata Sandi Saat Ini
                                @if($user->isAdmin()) 
                                    <span class="text-muted fw-normal">(wajib diisi bila mengganti email atau sandi baru)</span>
                                @endif
                            </label>
                            <input type="password" name="current_password" id="current_password" class="form-control bg-white @error('current_password') is-invalid @enderror"
                                   placeholder="Masukkan kata sandi saat ini">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label fw-semibold small text-secondary">Kata Sandi Baru</label>
                                <input type="password" name="new_password" id="new_password" class="form-control bg-white @error('new_password') is-invalid @enderror" 
                                       placeholder="Minimal 6 karakter">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="new_password_confirmation" class="form-label fw-semibold small text-secondary">Ulangi Kata Sandi Baru</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control bg-white"
                                       placeholder="Ketik ulang kata sandi baru">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
