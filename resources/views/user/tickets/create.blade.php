@extends('layouts.app')

@section('title', 'Buat Laporan Baru')
@section('page_title', 'Buat Laporan Baru')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Pelapor / Satuan Kerja</label>
                <input type="text" class="form-control bg-light" 
                       value="{{ Auth::user()->name }} (NRP/NIP: {{ Auth::user()->nrp_nip ?? '-' }}) / {{ Auth::user()->satuan_kerja ?? 'POLDA' }}" disabled>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Kategori Gangguan IT <span class="text-danger">*</span></label>
                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror"
                       value="{{ old('category') }}" maxlength="255" required
                       placeholder="Gangguan Jaringan Internet">
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label for="event_date" class="form-label">Tanggal Kegiatan <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" id="event_date" class="form-control @error('event_date') is-invalid @enderror"
                           value="{{ old('event_date') }}" required>
                    @error('event_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-4">
                    <label for="event_start_time" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="event_start_time" id="event_start_time" class="form-control @error('event_start_time') is-invalid @enderror"
                           value="{{ old('event_start_time') }}" required>
                    @error('event_start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-4">
                    <label for="event_end_time" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="event_end_time" id="event_end_time" class="form-control @error('event_end_time') is-invalid @enderror"
                           value="{{ old('event_end_time') }}" required>
                    @error('event_end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Judul Laporan Permasalahan <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="Jaringan LAN ruang SPKT terputus" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Lokasi Spesifik / Ruangan / Satker <span class="text-danger">*</span></label>
                <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                       value="{{ old('location', Auth::user()->satuan_kerja) }}" placeholder="Gedung utama, ruang SPKT" required>
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi Rinci Permasalahan <span class="text-danger">*</span></label>
                <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="Koneksi LAN terputus sejak pagi. Perangkat tidak dapat mengakses aplikasi internal." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="attachment" class="form-label">Lampiran Foto Bukti / Berkas (Opsional)</label>
                <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">Pilih berkas • JPG, PNG, PDF • Maks. 5 MB</div>
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info mb-4" role="alert">
                <strong class="d-block mb-1">Ketentuan Pelaporan</strong>
                <span>Laporan diperiksa dan diverifikasi Administrator Bid TIK sebelum diteruskan kepada Padal dan tim teknisi. Pantau melalui Laporan Saya.</span>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3">
                <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    Kirim Laporan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
