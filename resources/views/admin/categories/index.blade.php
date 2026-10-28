@extends('layouts.app')

@section('title', 'Kategori Gangguan IT')
@section('page_title', 'Kategori Permasalahan IT')
@section('page_subtitle', 'Master data jenis gangguan teknologi informasi di lingkungan POLDA')

@section('content')
<div class="row g-4">
    <!-- Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill text-success me-2"></i> Tambah Kategori Baru</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small text-secondary">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="contoh: Keamanan Siber & Firewall" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label fw-semibold small text-secondary">Ikon (Bootstrap Icon)</label>
                        <input type="text" name="icon" id="icon" class="form-control" 
                               value="{{ old('icon', 'bi-tools') }}" placeholder="contoh: bi-wifi, bi-printer, bi-shield">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small text-secondary">Deskripsi Singkat</label>
                        <textarea name="description" id="description" rows="3" class="form-control" placeholder="Jelaskan cakupan permasalahan kategori ini...">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Daftar Semua Kategori Gangguan IT</h6>
                    <span class="badge bg-primary">{{ $categories->count() }} Kategori</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th class="ps-3">Ikon & Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Tiket</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded bg-light border p-2 text-primary fs-5">
                                                <i class="bi {{ $category->icon ?: 'bi-tag' }}"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                                        </div>
                                    </td>
                                    <td><small class="text-muted">{{ Str::limit($category->description, 50) ?: '-' }}</small></td>
                                    <td><span class="badge bg-secondary">{{ $category->tickets_count }} Laporan</span></td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                            <span class="visually-hidden">Edit {{ $category->name }}</span>
                                        </button>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                              data-confirm="Hapus kategori '{{ $category->name }}'? Kategori yang telah digunakan oleh tiket tidak dapat dihapus.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $category->name }}">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Kategori -->
                                <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Edit Kategori: {{ $category->name }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Nama Kategori</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Ikon (Bootstrap Icon)</label>
                                                        <input type="text" name="icon" class="form-control" value="{{ $category->icon }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Deskripsi</label>
                                                        <textarea name="description" rows="3" class="form-control">{{ $category->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-secondary">Status</label>
                                                        <select name="is_active" class="form-select" required>
                                                            <option value="1" {{ $category->is_active ? 'selected' : '' }}>Aktif</option>
                                                            <option value="0" {{ !$category->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori yang terdaftar.</td>
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
