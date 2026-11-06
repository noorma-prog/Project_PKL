@extends('layouts.app')

@section('title', 'Rekapitulasi Laporan IT')
@section('page_title', 'Rekapitulasi Penanganan Laporan')
@section('page_subtitle', 'Laporan periodik data penanganan gangguan dan permohonan layanan TIK')

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
    <h4 class="fw-bold mb-0">KEPOLISIAN NEGARA REPUBLIK INDONESIA</h4>
    <h5 class="fw-bold mb-1">DAERAH KALIMANTAN SELATAN</h5>
    <h6 class="text-uppercase text-secondary mb-0">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h6>
    <div class="mt-2 text-dark fw-bold">REKAPITULASI PENANGANAN GANGGUAN IT - LAYANIT</div>
    <small class="text-muted">Dicetak pada: {{ date('d F Y, H:i') }} WITA</small>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 rounded-3 mb-4 filter-card bg-white">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-funnel text-primary me-2"></i> Parameter Filter Rekapitulasi</h6>
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.recap') }}" class="row g-2 align-items-end">
            @include('components.ticket-sort-filter')
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-secondary mb-1">Dari Tanggal:</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-secondary mb-1">Sampai Tanggal:</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Status:</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                    <option value="ditolak_admin" {{ request('status') == 'ditolak_admin' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Kategori:</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan Rekap (Civic Blue Stat Cards) -->
<div class="stat-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon stat-icon--primary">
            <i class="bi bi-journal-text"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ $tickets->count() }}</div>
            <div class="stat-label">Total Laporan Rekap</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--success">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ $tickets->whereIn('status', ['selesai', 'selesai_dinilai'])->count() }}</div>
            <div class="stat-label">Selesai Ditangani</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--info">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ $tickets->where('status', 'dalam_penanganan')->count() }}</div>
            <div class="stat-label">Sedang Ditangani</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--warning">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ $tickets->where('status', 'menunggu_verifikasi')->count() }}</div>
            <div class="stat-label">Menunggu Verifikasi</div>
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-table text-primary me-2"></i> Data Rekapitulasi ({{ $tickets->count() }} Laporan)</h6>
            <small class="text-muted">Hasil filter data penanganan sesuai parameter yang dipilih</small>
        </div>
        <a href="{{ route('admin.reports.recap.preview', request()->query()) }}" class="btn btn-sm btn-dark btn-print">
            <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Pratinjau & Simpan PDF
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th style="width: 45px;" class="ps-3 text-center">No</th>
                        <th style="width: 140px;">No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Kategori & Judul</th>
                        <th>Lokasi</th>
                        <th>Padal</th>
                        <th style="width: 150px;">Status</th>
                        <th style="width: 100px;">Tgl Lapor</th>
                        <th style="width: 100px;">Tgl Selesai</th>
                        <th style="width: 80px;" class="text-end pe-3">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $index => $ticket)
                        <tr>
                            <td class="ps-3 text-center" data-label="No">{{ $index + 1 }}</td>
                            <td data-label="No. Tiket">
                                <span class="font-monospace fw-bold text-primary">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td data-label="Pelapor">
                                <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->user->satuan_kerja ?? '-' }}</small>
                            </td>
                            <td data-label="Permasalahan">
                                <div class="small fw-semibold text-dark">{{ $ticket->title }}</div>
                                <small class="text-muted">{{ $ticket->category?->name ?? 'Umum' }}</small>
                                <x-ticket-event-time :ticket="$ticket" />
                            </td>
                            <td data-label="Lokasi">
                                <span class="small text-secondary">{{ $ticket->location ?? '-' }}</span>
                            </td>
                            <td data-label="Padal">
                                <span class="small fw-semibold text-dark">{{ $ticket->padal ? $ticket->padal->name : '-' }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td data-label="Tgl Lapor">
                                <small class="text-muted">{{ $ticket->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td data-label="Tgl Selesai">
                                <small class="text-muted">{{ $ticket->completed_at ? $ticket->completed_at->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td class="text-end pe-3" data-label="Rating">
                                @if($ticket->rating)
                                    <span class="badge bg-warning text-dark fw-bold">
                                        <i class="bi bi-star-fill text-dark me-1"></i> {{ $ticket->rating->rating }}
                                    </span>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-clipboard-x text-muted opacity-50 display-6 d-block mb-2"></i>
                                    <p class="mb-0">Tidak ada data laporan pada periode dan kriteria filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
