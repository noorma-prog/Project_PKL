@extends('layouts.app')

@section('title', 'Buat Laporan Baru')
@section('page_title', 'Form Pengaduan Kendala IT')
@section('page_subtitle', 'Sampaikan permasalahan teknologi informasi pada satuan kerja Anda')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Formulir Pelaporan Gangguan IT</h6>
                <a href="{{ route('user.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Nama Pelapor</label>
                            <input type="text" class="form-control bg-light" value="{{ Auth::user()->name }} (NRP/NIP: {{ Auth::user()->nrp_nip }})" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Satuan Kerja / Bagian</label>
                            <input type="text" class="form-control bg-light" value="{{ Auth::user()->satuan_kerja ?? 'POLDA' }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold small text-secondary">Kategori Gangguan IT <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori Permasalahan --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} &mdash; {{ $cat->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold small text-secondary">Judul Laporan Permasalahan <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" placeholder="contoh: Jaringan internet LAN di ruang SPKT terputus" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label fw-semibold small text-secondary">Lokasi Spesifik / Ruangan / Satker <span class="text-danger">*</span></label>
                        <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location', Auth::user()->satuan_kerja) }}" placeholder="contoh: Gedung A Lantai 2, Ruang Riksa Ditreskrimum" required>
                        <small class="text-muted">Tuliskan ruangan atau letak perangkat secara spesifik untuk mempermudah teknisi.</small>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small text-secondary">Deskripsi Rinci Permasalahan <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="Jelaskan kendala yang dialami, sejak kapan terjadi, pesan kesalahan (jika ada), dan perangkat yang terdampak..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label fw-semibold small text-secondary">Lampiran Foto Bukti / Berkas (Opsional)</label>
                        <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format yang didukung: JPG, PNG, PDF. Ukuran berkas maksimal 5 MB.</small>
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                            <div class="small text-secondary">
                                <strong>Ketentuan Pelaporan:</strong>
                                Laporan Anda akan diperiksa dan diverifikasi oleh Administrator Bid TIK sebelum diteruskan kepada Perwira Pengendali (Padal) dan tim Teknisi. Anda dapat memantau status secara berkala melalui menu Laporan Saya.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-send-fill me-1"></i> Kirim Laporan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
