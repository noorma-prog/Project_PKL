@extends('layouts.app')

@section('title', 'Unit Padal')
@section('page_title', 'Unit Padal')
@section('page_subtitle', 'Kelola regu perwira pengendali dan personel penanganan gangguan IT.')

@section('content')
@php
    $officers = $padalOfficers ?? $padalUsers ?? collect();
@endphp

<div class="mb-4">
    <button type="button" class="btn btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahPadal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Unit Padal
    </button>
</div>

<!-- Modal Tambah Unit Padal -->
<div class="modal fade" id="modalTambahPadal" tabindex="-1" aria-labelledby="modalTambahPadalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            <form action="{{ route('admin.padals.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPadalLabel">
                        <i class="bi bi-shield-plus text-primary me-2"></i>Tambah Unit Padal Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nama_padal" class="form-label fw-semibold small text-secondary">Nama Unit Padal <span class="text-danger">*</span></label>
                        <input type="text" name="nama_padal" id="nama_padal" class="form-control @error('nama_padal') is-invalid @enderror" 
                               placeholder="Contoh: Unit Padal Jaringan & Infrastruktur" value="{{ old('nama_padal') }}" required>
                        @error('nama_padal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-semibold small text-secondary">Perwira Pengendali (Ketua Unit) <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Akun Perwira Pengendali --</option>
                            @foreach($officers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->nrp_nip ? 'NRP: ' . $user->nrp_nip : 'ID #' . $user->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold small text-secondary">Status Operasional <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="deskripsi" class="form-label fw-semibold small text-secondary">Deskripsi & Cakupan Tugas (Opsional)</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control" placeholder="Jelaskan ruang lingkup penanganan unit ini...">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Simpan Unit Padal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Daftar Unit Padal -->
<div class="d-flex flex-column gap-3 mb-4">
    @forelse($padals as $padal)
        <div class="card shadow-sm border-0 rounded-3 mb-0 bg-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-shaded fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="fw-bold text-dark mb-0">{{ $padal->nama_padal }}</h5>
                            <span class="badge {{ $padal->status === 'aktif' ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-secondary' }} px-2 py-1">
                                {{ ucfirst($padal->status) }}
                            </span>
                        </div>
                        <div class="text-muted small">
                            Ketua: <strong class="text-dark">{{ $padal->leader ? $padal->leader->name : 'Belum Ditentukan' }}</strong>
                            • <span class="badge bg-light text-dark border">{{ $padal->members_count ?? $padal->members->count() }} Anggota Teknisi</span>
                            • <span class="badge bg-light text-primary border">{{ $padal->tickets_count ?? $padal->tickets->count() }} Total Tiket</span>
                        </div>
                        @if($padal->deskripsi)
                            <div class="small text-secondary mt-2">{{ Str::limit($padal->deskripsi, 120) }}</div>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPadal{{ $padal->id }}">
                        <i class="bi bi-pencil me-1"></i> Ubah
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusPadal{{ $padal->id }}" title="Hapus Unit">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Edit Unit Padal -->
        <div class="modal fade" id="modalEditPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 border-0 shadow">
                    <form action="{{ route('admin.padals.update', $padal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Unit Padal: {{ $padal->nama_padal }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Nama Unit Padal <span class="text-danger">*</span></label>
                                <input type="text" name="nama_padal" class="form-control" value="{{ old('nama_padal', $padal->nama_padal) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Perwira Pengendali (Ketua) <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Pilih Akun Perwira Pengendali --</option>
                                    @foreach($officers as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $padal->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->nrp_nip ? 'NRP: ' . $user->nrp_nip : 'ID #' . $user->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Status Operasional <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif" {{ old('status', $padal->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status', $padal->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold small text-secondary">Deskripsi (Opsional)</label>
                                <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $padal->deskripsi) }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus Unit Padal -->
        <div class="modal fade" id="modalHapusPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 border-0 shadow">
                    <form action="{{ route('admin.padals.destroy', $padal->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold text-danger">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Hapus Unit Padal
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start p-4">
                            <p class="mb-2">Apakah Anda yakin ingin menghapus Unit Padal <strong>{{ $padal->nama_padal }}</strong>?</p>
                            <div class="p-3 bg-light rounded-3 text-secondary small mb-2 border">
                                <div>Ketua Unit: <strong>{{ $padal->leader ? $padal->leader->name : '-' }}</strong></div>
                                <div>Total Anggota: {{ $padal->members_count ?? $padal->members->count() }} orang</div>
                            </div>
                            <p class="small text-danger mb-0">Perhatian: Unit yang sudah memiliki relasi tiket penugasan tidak dapat dihapus demi integritas data laporan.</p>
                        </div>
                        <div class="modal-footer border-top p-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Ya, Hapus Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-body p-5 text-center text-muted">
                <i class="bi bi-shield-x display-6 text-muted opacity-50 d-block mb-2"></i>
                <p class="mb-0">Belum ada unit Padal yang terdaftar. Silakan klik tombol "Tambah Unit Padal" untuk membuat unit baru.</p>
            </div>
        </div>
    @endforelse
</div>

@if($padals->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
        <span class="small text-muted">
            Menampilkan {{ $padals->firstItem() }}-{{ $padals->lastItem() }} dari {{ $padals->total() }} unit
        </span>
        <div>
            {{ $padals->links() }}
        </div>
    </div>
@endif
@endsection
