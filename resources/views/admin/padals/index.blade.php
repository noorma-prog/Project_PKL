@extends('layouts.app')

@section('title', 'Unit Padal')
@section('page_title', 'Unit Padal')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPadal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Unit Padal
    </button>
</div>

<!-- Modal Tambah Unit Padal -->
<div class="modal fade" id="modalTambahPadal" tabindex="-1" aria-labelledby="modalTambahPadalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.padals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPadalLabel">Tambah Unit Padal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nama_padal" class="form-label">Nama Unit Padal <span class="text-danger">*</span></label>
                        <input type="text" name="nama_padal" id="nama_padal" class="form-control @error('nama_padal') is-invalid @enderror" 
                               placeholder="Unit Padal Jaringan" value="{{ old('nama_padal') }}" required>
                        @error('nama_padal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="user_id" class="form-label">Perwira Pengendali <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Pilih akun Padal</option>
                            @foreach($padalUsers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->nrp_nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control" placeholder="Tugas dan lingkup unit...">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column gap-2 p-4 pt-0 border-0">
                    <button type="submit" class="btn btn-primary w-100">Simpan Unit Padal</button>
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Daftar Unit Padal -->
<div class="d-flex flex-column gap-3 mb-4">
    @forelse($padals as $padal)
        <div class="card mb-0">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $padal->nama_padal }}</h5>
                    <div class="text-muted small">
                        Ketua: <strong>{{ $padal->leader ? $padal->leader->name : 'Belum Ditentukan' }}</strong>
                        • {{ $padal->members_count ?? $padal->members->count() }} anggota
                        • Status: <span class="badge {{ $padal->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($padal->status) }}</span>
                    </div>
                    @if($padal->deskripsi)
                        <div class="small text-secondary mt-1">{{ Str::limit($padal->deskripsi, 80) }}</div>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditPadal{{ $padal->id }}">
                        Ubah Unit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusPadal{{ $padal->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Edit Unit Padal -->
        <div class="modal fade" id="modalEditPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.padals.update', $padal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-dark">Ubah Unit Padal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label">Nama Unit Padal <span class="text-danger">*</span></label>
                                <input type="text" name="nama_padal" class="form-control" value="{{ old('nama_padal', $padal->nama_padal) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Perwira Pengendali <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Pilih akun Padal</option>
                                    @foreach($padalUsers as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $padal->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->nrp_nip ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif" {{ old('status', $padal->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status', $padal->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Deskripsi (Opsional)</label>
                                <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $padal->deskripsi) }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer d-flex flex-column gap-2 p-4 pt-0 border-0">
                            <button type="submit" class="btn btn-primary w-100">Simpan Unit Padal</button>
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus Unit Padal -->
        <div class="modal fade" id="modalHapusPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.padals.destroy', $padal->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-dark">Hapus Unit Padal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start p-4">
                            <p class="mb-2">Apakah Anda yakin ingin menghapus Unit Padal <strong>{{ $padal->nama_padal }}</strong>?</p>
                            <p class="small text-muted mb-0">Tindakan ini tidak dapat dibatalkan jika unit sudah memiliki relasi tiket penugasan.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body p-5 text-center text-muted">
                Belum ada unit Padal yang terdaftar. Silakan klik tombol "Tambah Unit Padal" untuk membuat unit baru.
            </div>
        </div>
    @endforelse
</div>

@if($padals->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="small text-muted">
            Menampilkan {{ $padals->firstItem() }}-{{ $padals->lastItem() }} dari {{ $padals->total() }} unit
        </span>
        <div>
            {{ $padals->links() }}
        </div>
    </div>
@endif
@endsection
