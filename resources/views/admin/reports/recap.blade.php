@extends('layouts.app')

@section('title', 'Rekapitulasi Laporan')
@section('page_title', 'Rekapitulasi Laporan')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('styles')
<style>
    @media print {
        .sidebar, .topbar, .filter-card, .btn-print, .action-buttons-wrap, .btn-back {
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
<!-- Header Khusus Cetak Browser (Tanpa Alamat) -->
<div class="print-header mb-4 text-center border-bottom pb-3">
    <h4 class="fw-bold mb-0">KEPOLISIAN NEGARA REPUBLIK INDONESIA</h4>
    <h5 class="fw-bold mb-1">DAERAH KALIMANTAN SELATAN</h5>
    <h6 class="text-uppercase text-secondary mb-0">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h6>
    <div class="mt-2 text-dark fw-bold">REKAPITULASI PENANGANAN GANGGUAN IT - LAYANIT</div>
    <small class="text-muted">Dicetak pada: {{ date('d F Y, H:i') }} WITA</small>
</div>

<!-- Filter Card -->
<div class="card bg-white border-0 shadow-sm mb-4 filter-card" style="border: 1px solid #E2E8F0 !important; border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.reports.recap') }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'submission') }}">
            @if(request('padal_id'))
                <input type="hidden" name="padal_id" value="{{ request('padal_id') }}">
            @endif

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1">Tanggal Mulai</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}" style="border-radius: 6px; border-color: #D2D9E2; height: 38px; font-size: 13px;">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1">Tanggal Akhir</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}" style="border-radius: 6px; border-color: #D2D9E2; height: 38px; font-size: 13px;">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1">Status</label>
                    <select name="status" class="form-select" style="border-radius: 6px; border-color: #D2D9E2; height: 38px; font-size: 13px;">
                        <option value="">Semua status</option>
                        <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                        <option value="ditolak_admin" {{ request('status') == 'ditolak_admin' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label text-dark fw-semibold small mb-1">Kategori</label>
                    <select name="category_id" class="form-select" style="border-radius: 6px; border-color: #D2D9E2; height: 38px; font-size: 13px;">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary fw-medium px-4" style="background-color: #234ED8; border-color: #234ED8; height: 38px; border-radius: 6px; font-size: 13px;">
                    Tampilkan Rekap
                </button>
                @if(request()->hasAny(['date_from', 'date_to', 'status', 'category_id']))
                    <a href="{{ route('admin.reports.recap') }}" class="btn btn-link text-secondary text-decoration-none small px-2">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Stat Cards (Ringkasan Total, Selesai, Penanganan, Verifikasi) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total</div>
            <div class="stat-value">{{ $tickets->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Selesai</div>
            <div class="stat-value">{{ $tickets->whereIn('status', ['selesai', 'selesai_dinilai'])->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Penanganan</div>
            <div class="stat-value">{{ $tickets->where('status', 'dalam_penanganan')->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Verifikasi</div>
            <div class="stat-value">{{ $tickets->where('status', 'menunggu_verifikasi')->count() }}</div>
        </div>
    </div>
</div>

<!-- Action Buttons (Preview PDF & Unduh PDF) -->
<div class="d-flex flex-column flex-sm-row justify-content-sm-end align-items-stretch align-items-sm-center gap-2 mb-3 action-buttons-wrap">
    <a href="{{ route('admin.reports.recap.preview', request()->query()) }}" class="btn btn-outline-secondary bg-white text-dark d-inline-flex align-items-center justify-content-center" style="border: 1px solid #D2D9E2; height: 38px; border-radius: 6px; font-weight: 500; font-size: 13px; padding: 0 16px;">
        <i class="bi bi-file-earmark-pdf me-2 text-dark"></i> Preview PDF
    </a>
    <a href="{{ route('admin.reports.recap.export', request()->query()) }}" class="btn btn-primary d-inline-flex align-items-center justify-content-center" style="background-color: #234ED8; border-color: #234ED8; height: 38px; border-radius: 6px; font-weight: 500; font-size: 13px; padding: 0 18px;">
        <i class="bi bi-download me-2"></i> Unduh PDF
    </a>
</div>

<!-- Tabel Rekapitulasi -->
<div class="card bg-white border-0 shadow-sm" style="border: 1px solid #E2E8F0 !important; border-radius: 8px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="margin-bottom: 0;">
            <thead style="background-color: #FAFAFB; border-bottom: 1px solid #EDF2F7;">
                <tr>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; color: #64748B; text-transform: uppercase;">LAPORAN / JADWAL</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; color: #64748B; text-transform: uppercase;">LOKASI</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; color: #64748B; text-transform: uppercase;">STATUS</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; color: #64748B; text-transform: uppercase; text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 14px 16px;">
                            <div class="fw-semibold font-monospace" style="color: #234ED8; font-size: 12px; margin-bottom: 2px;">{{ $ticket->ticket_number }}</div>
                            <div class="fw-bold text-dark" style="font-size: 13.5px; margin-bottom: 2px;">{{ $ticket->title }}</div>
                            <div class="text-muted" style="font-size: 11.5px;">
                                {{ $ticket->category?->name ?? 'Umum' }} &bull; {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                                @if($ticket->user)
                                    &bull; Pelapor: {{ $ticket->user->name }}
                                @endif
                            </div>
                            <x-ticket-event-time :ticket="$ticket" />
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #334155;">
                            {{ $ticket->location ?? '-' }}
                        </td>
                        <td style="padding: 14px 16px;">
                            <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                        </td>
                        <td style="padding: 14px 16px; text-align: right;">
                            <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary bg-white text-dark px-3" style="border: 1px solid #CBD5E1; border-radius: 6px; font-weight: 500; font-size: 12px; height: 32px; display: inline-flex; align-items: center;">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x display-6 text-muted opacity-50 d-block mb-2"></i>
                            <span>Tidak ada data laporan pada periode dan kriteria filter yang dipilih.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
