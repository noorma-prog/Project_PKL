@extends('layouts.app')

@section('title', 'Mengelola Unit Padal')
@section('page_title', 'Mengelola Unit Padal (Perwira Pengendali)')
@section('page_subtitle', 'Manajemen regu/unit Padal penanganan gangguan IT di lingkungan POLDA')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-shaded text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Daftar Unit Padal ({{ $padals->total() }})</h6>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPadal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Unit Padal
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Unit Padal</th>
                                <th>Perwira Pengendali (Ketua)</th>
                                <th>Deskripsi Penugasan</th>
                                <th class="text-center">Anggota Teknisi</th>
                                <th class="text-center">Total Tiket</th>
                                <th class="text-center">Status</th>
                                <th class="text-end" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($padals as $index => $padal)
                                <tr>
                                    <td class="text-center">{{ $padals->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $padal->nama_padal }}</div>
                                        <small class="text-muted">ID: #PADAL-{{ str_pad($padal->id, 3, '0', STR_PAD_LEFT) }}</small>
                                    </td>
                                    <td>
                                        @if($padal->leader)
                                            <div class="fw-semibold text-primary">{{ $padal->leader->name }}</div>
                                            <small class="text-muted">NRP: {{ $padal->leader->nrp_nip ?? '-' }}</small>
                                        @else
                                            <span class="badge bg-secondary">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ Str::limit($padal->deskripsi ?? 'Tidak ada keterangan.', 60) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark rounded-pill px-3">
                                            <i class="bi bi-people me-1"></i> {{ $padal->members_count ?? $padal->members->count() }} Anggota
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-2">
                                            {{ $padal->tickets_count ?? $padal->tickets->count() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($padal->status === 'aktif')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditPadal{{ $padal->id }}" 
                                                    title="Ubah Padal">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalHapusPadal{{ $padal->id }}" 
                                                    title="Hapus Padal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Padal -->
                                <div class="modal fade" id="modalEditPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.padals.update', $padal->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Ubah Data Unit Padal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Nama Unit Padal</label>
                                                        <input type="text" name="nama_padal" class="form-control" value="{{ old('nama_padal', $padal->nama_padal) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Ketua / Perwira Pengendali</label>
                                                        <select name="user_id" class="form-select" required>
                                                            <option value="">-- Pilih Perwira Pengendali --</option>
                                                            @foreach($padalOfficers as $officer)
                                                                <option value="{{ $officer->id }}" {{ $padal->user_id == $officer->id ? 'selected' : '' }}>
                                                                    {{ $officer->name }} (NRP: {{ $officer->nrp_nip }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Status Operasional</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="aktif" {{ $padal->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                            <option value="nonaktif" {{ $padal->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-secondary">Deskripsi / Ruang Lingkup</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $padal->deskripsi) }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus Padal -->
                                <div class="modal fade" id="modalHapusPadal{{ $padal->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.padals.destroy', $padal->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger">Konfirmasi Hapus Unit Padal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p class="mb-1">Apakah Anda yakin ingin menghapus unit Padal ini?</p>
                                                    <div class="p-3 bg-light rounded text-dark">
                                                        <strong>{{ $padal->nama_padal }}</strong><br>
                                                        <small class="text-muted">Ketua: {{ $padal->leader ? $padal->leader->name : '-' }}</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Unit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada unit Padal yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($padals->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $padals->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah Padal -->
<div class="modal fade" id="modalTambahPadal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.padals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Unit Padal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Unit Padal</label>
                        <input type="text" name="nama_padal" class="form-control" placeholder="contoh: Regu Padal Charlie (Jaringan)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Pilih Perwira Pengendali (Ketua)</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Perwira Pengendali --</option>
                            @foreach($padalOfficers as $officer)
                                <option value="{{ $officer->id }}">{{ $officer->name }} (NRP: {{ $officer->nrp_nip }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Status Operasional</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Deskripsi / Ruang Lingkup Penugasan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan spesialisasi atau lingkup tugas unit Padal ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Unit Padal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
