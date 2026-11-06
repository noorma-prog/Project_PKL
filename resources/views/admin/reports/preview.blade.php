@extends('layouts.app')

@section('title', 'Preview Rekapitulasi PDF')
@section('page_title', 'Pratinjau Rekapitulasi Laporan')
@section('page_subtitle', 'Tinjau tata letak dan format resmi dokumen sebelum mengunduh berkas PDF')

@section('content')
<div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
    <div class="card-body p-4">
        <!-- Action Header Bar -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-1 mb-1">Dokumen Resmi TIK Polda</span>
                <h5 class="mb-0 fw-bold text-dark">Pratinjau Format Cetak Rekapitulasi</h5>
                <div class="text-muted small mt-1">
                    <i class="bi bi-calendar-range me-1"></i> Periode: <strong>{{ $reportStart->translatedFormat('d F Y') }}</strong> s.d. <strong>{{ $reportEnd->translatedFormat('d F Y') }}</strong>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.recap', request()->query()) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
                </a>
                <a href="{{ route('admin.reports.recap.export', request()->query()) }}" class="btn btn-primary fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Simpan PDF
                </a>
            </div>
        </div>

        <!-- Simulated Document Sheet -->
        <div class="p-4 p-md-5 rounded-3 border bg-white shadow-sm" style="max-width: 1024px; margin: 0 auto;">
            <!-- Kop Kepolisian -->
            <div class="text-center mb-4 pb-3" style="border-bottom: 2px solid #17243C;">
                <div class="fw-bold text-uppercase" style="font-size: 1rem; letter-spacing: 0.05em; color: #17243C;">KEPOLISIAN NEGARA REPUBLIK INDONESIA</div>
                <div class="fw-bold text-uppercase" style="font-size: 0.95rem; letter-spacing: 0.05em; color: #17243C;">DAERAH KALIMANTAN SELATAN</div>
                <div class="fw-bold text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.05em; color: #17243C;">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</div>
                <div class="text-muted" style="font-size: 0.75rem;">Jl. DI. Panjaitan No.1, Antasan Besar, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70114</div>
            </div>

            <!-- Title -->
            <div class="text-center mb-4">
                <h5 class="fw-bold text-uppercase mb-1" style="color: #17243C; letter-spacing: 0.03em;">REKAPITULASI PENANGANAN LAPORAN GANGGUAN IT</h5>
                <div class="text-secondary small">
                    Sistem Helpdesk Terpadu <strong>LayanIT</strong> • Periode: {{ $reportStart->translatedFormat('d F Y') }} s.d. {{ $reportEnd->translatedFormat('d F Y') }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    Tanggal Cetak: {{ $createdAt->translatedFormat('d F Y, H:i') }} WITA
                </div>
            </div>

            @if($isEmpty)
                <div class="alert alert-light text-center border p-4 my-4">
                    <i class="bi bi-folder-x display-6 text-muted d-block mb-2"></i>
                    <p class="mb-0 text-muted">Tidak ada rekaman laporan gangguan IT pada rentang tanggal yang dipilih.</p>
                </div>
            @else
                <div class="table-responsive table-formal-preview border rounded">
                    <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="text-center" style="width: 35px;">No</th>
                                <th style="width: 120px;">No. Tiket</th>
                                <th>Pelapor & Satker</th>
                                <th>Kategori & Judul</th>
                                <th>Lokasi</th>
                                <th>Padal</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 85px;">Tgl Lapor</th>
                                <th style="width: 85px;">Tgl Selesai</th>
                                <th class="text-center" style="width: 60px;">Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $index => $ticket)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-bold font-monospace text-primary">{{ $ticket->ticket_number }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ticket->user?->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $ticket->user?->satuan_kerja ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $ticket->title ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $ticket->category?->name ?? '-' }}</small>
                                        <x-ticket-event-time :ticket="$ticket" />
                                    </td>
                                    <td>{{ $ticket->location ?? '-' }}</td>
                                    <td>{{ $ticket->padal?->name ?? '-' }}</td>
                                    <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                                    <td>{{ $ticket->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $ticket->completed_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($ticket->rating)
                                            <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-star-fill"></i> {{ $ticket->rating->rating }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                    <span>Total Rekap: <strong>{{ count($tickets) }}</strong> Laporan</span>
                    <span>Bid TIK Polda Kalimantan Selatan</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
