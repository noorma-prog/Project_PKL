@extends('layouts.app')

@section('title', 'Kategori Gangguan IT')
@section('page_title', 'Kategori Permasalahan IT')
@section('page_subtitle', 'Master data klasifikasi jenis kendala teknologi informasi di lingkungan POLDA')

@section('content')
<div class="row g-4">
    <!-- Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Tambah Kategori Baru</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small text-secondary">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="Contoh: Keamanan Jaringan & Firewall" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label fw-semibold small text-secondary">Ikon (Bootstrap Icon)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-palette"></i></span>
                            <input type="text" name="icon" id="icon" class="form-control border-start-0 ps-0" 
                                   value="{{ old('icon', 'bi-tools') }}" placeholder="Contoh: bi-wifi, bi-printer, bi-shield">
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Nama kelas icon Bootstrap Icons</small>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small text-secondary">Deskripsi Klasifikasi</label>
                        <textarea name="description" id="description" rows="3" class="form-control" placeholder="Jelaskan cakupan masalah dalam kategori ini...">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Daftar Kategori Gangguan IT</h6>
                        <small class="text-muted">Master klasifikasi untuk mempermudah pemilahan penugasan</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">{{ $categories->count() }} Kategori</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th class="ps-3">Nama Kategori & Ikon</th>
                                <th>Deskripsi</th>
                                <th style="width: 130px;">Jumlah Tiket</th>
                                <th style="width: 100px;">Status</th>
                                <th class="text-end pe-3" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="ps-3" data-label="Kategori">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi {{ $category->icon ?: 'bi-tag' }} fs-6"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                                        </div>
                                    </td>
                                    <td data-label="Deskripsi">
                                        <small class="text-muted">{{ Str::limit($category->description, 50) ?: '-' }}</small>
                                    </td>
                                    <td data-label="Jumlah Tiket">
                                        <span class="badge bg-light text-dark border fw-normal">{{ $category->tickets_count }} Laporan</span>
                                    </td>
                                    <td data-label="Status">
                                        @if($category->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary px-2 py-1">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3" data-label="Aksi">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}" title="Edit Kategori">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                                <span class="visually-hidden">Edit {{ $category->name }}</span>
                                            </button>
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                                  data-confirm="Hapus kategori '{{ $category->name }}'? Kategori yang telah digunakan oleh tiket tidak dapat dihapus.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $category->name }}" title="Hapus Kategori">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Kategori -->
                                <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 border-0 shadow">
                                            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header border-bottom py-3">
                                                    <h6 class="modal-title fw-bold text-dark">
                                                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Kategori: {{ $category->name }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Nama Kategori <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Ikon (Bootstrap Icon)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-palette"></i></span>
                                                            <input type="text" name="icon" class="form-control border-start-0 ps-0" value="{{ $category->icon }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Deskripsi Klasifikasi</label>
                                                        <textarea name="description" rows="3" class="form-control">{{ $category->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Status Kategori</label>
                                                        <select name="is_active" class="form-select" required>
                                                            <option value="1" {{ $category->is_active ? 'selected' : '' }}>Aktif (Dapat dipilih pelapor)</option>
                                                            <option value="0" {{ !$category->is_active ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top py-3">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="bi bi-tags text-muted opacity-50 display-6 d-block mb-2"></i>
                                            <p class="mb-0">Belum ada kategori yang terdaftar pada sistem.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection
