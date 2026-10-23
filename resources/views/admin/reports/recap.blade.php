@extends('layouts.app')

@section('title', 'Rekapitulasi Laporan IT')
@section('page_title', 'Rekapitulasi Penanganan Laporan')
@section('page_subtitle', 'Laporan berkala penanganan gangguan IT untuk pimpinan')

@section('styles')
<style>
    @media print {
        .sidebar, .topbar, .filter-card, .btn-print, .btn-back {
            display: none !important;
        }
        .main-wrapper {
            margin-left: 0 !important;
        }
        .content-area {
            padding: 0 !important;
        }
        body {
            background: white !important;
        }
        .print-header {
            display: block !important;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endsection

@section('content')
<!-- Header Khusus Cetak -->
<div class="print-header mb-4 text-center border-bottom pb-3">
    <h4 class="fw-bold mb-0">KEPOLISIAN DAERAH (POLDA)</h4>
    <h5 class="fw-bold mb-1">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h5>
    <h6 class="text-uppercase text-secondary mb-0">REKAPITULASI PENANGANAN GANGGUAN IT - LAYANIT</h6>
    <small class="text-muted">Dicetak pada: {{ date('d F Y, H:i') }} WIB</small>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4 filter-card">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.recap') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Dari Tanggal:</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Sampai Tanggal:</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Status:</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                    <option value="ditolak_admin" {{ request('status') == 'ditolak_admin' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Kategori:</label>
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                <button type="button" class="btn btn-outline-dark btn-print" onclick="window.print()" title="Cetak Rekapitulasi">
                    <i class="bi bi-printer-fill"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan Rekap -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-primary bg-opacity-10">
            <span class="text-secondary small fw-semibold">Total Laporan Rekap</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $tickets->count() }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-success bg-opacity-10">
            <span class="text-secondary small fw-semibold">Selesai Ditangani</span>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $tickets->whereIn('status', ['selesai', 'selesai_dinilai'])->count() }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-info bg-opacity-10">
            <span class="text-secondary small fw-semibold">Sedang Ditangani</span>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $tickets->where('status', 'dalam_penanganan')->count() }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0 bg-warning bg-opacity-10">
            <span class="text-secondary small fw-semibold">Menunggu Verifikasi</span>
            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $tickets->where('status', 'menunggu_verifikasi')->count() }}</h3>
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Data Rekapitulasi Laporan ({{ $tickets->count() }} Data)</h6>
        <button type="button" class="btn btn-sm btn-dark btn-print" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Dokumen Rekap
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Kategori & Judul</th>
                        <th>Lokasi</th>
                        <th>Padal</th>
                        <th>Status</th>
                        <th>Tgl Lapor</th>
                        <th>Tgl Selesai</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $index => $ticket)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-monospace fw-bold text-primary">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div>{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->user->satuan_kerja }}</small>
                            </td>
                            <td>
                                <strong>{{ $ticket->title }}</strong><br>
                                <small class="text-muted">{{ $ticket->category->name }}</small>
                            </td>
                            <td>{{ $ticket->location }}</td>
                            <td>{{ $ticket->padal ? $ticket->padal->name : '-' }}</td>
                            <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                            <td>{{ $ticket->completed_at ? $ticket->completed_at->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($ticket->rating)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> {{ $ticket->rating->rating }}</span>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">Tidak ada data laporan pada periode yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
