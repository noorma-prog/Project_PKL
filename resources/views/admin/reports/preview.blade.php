@extends('layouts.app')

@section('title', 'Pratinjau Rekapitulasi PDF')
@section('page_title', 'Pratinjau Rekapitulasi Laporan')
@section('page_subtitle', 'Tinjau tata letak dan format resmi dokumen sebelum mengunduh berkas PDF')

@section('content')
<div class="mb-4">
    <!-- Action Header Bar -->
    <div class="card bg-white border-0 shadow-sm mb-4" style="border: 1px solid #E2E8F0 !important; border-radius: 8px;">
        <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge px-2.5 py-1 mb-2 fw-semibold" style="background-color: #EFF6FF; color: #234ED8; font-size: 11.5px; border-radius: 4px;">Dokumen Resmi TIK Polda</span>
                <h5 class="mb-1 fw-bold text-dark" style="font-size: 18px;">Pratinjau Format Cetak Rekapitulasi</h5>
                <div class="text-secondary small">
                    <i class="bi bi-calendar3 me-1"></i> Periode: <strong>{{ $reportStart->translatedFormat('d F Y') }}</strong> s.d. <strong>{{ $reportEnd->translatedFormat('d F Y') }}</strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.reports.recap', request()->query()) }}" class="btn btn-outline-secondary bg-white text-dark d-inline-flex align-items-center justify-content-center" style="border: 1px solid #D2D9E2; height: 38px; border-radius: 6px; font-weight: 500; font-size: 13px; padding: 0 16px;">
                    <i class="bi bi-arrow-left me-2"></i> Kembali ke Rekap
                </a>
                <a href="{{ route('admin.reports.recap.export', request()->query()) }}" class="btn btn-primary d-inline-flex align-items-center justify-content-center" style="background-color: #234ED8; border-color: #234ED8; height: 38px; border-radius: 6px; font-weight: 500; font-size: 13px; padding: 0 18px;">
                    <i class="bi bi-download me-2"></i> Unduh PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Simulated Document Sheet (Kertas Cetak Resmi) -->
    <div class="p-4 p-md-5 bg-white shadow-sm" style="max-width: 960px; margin: 0 auto; border: 1px solid #CBD5E1; border-radius: 8px; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06) !important;">
        <!-- Kop Kepolisian (Hanya 3 Baris Instansi Resmi, Tanpa Alamat) -->
        <div class="text-center mb-4 pb-3" style="border-bottom: 2px solid #17243C;">
            <div class="fw-bold text-uppercase" style="font-size: 1.05rem; letter-spacing: 0.06em; color: #17243C; line-height: 1.4;">KEPOLISIAN NEGARA REPUBLIK INDONESIA</div>
            <div class="fw-bold text-uppercase" style="font-size: 0.95rem; letter-spacing: 0.06em; color: #17243C; line-height: 1.4;">DAERAH KALIMANTAN SELATAN</div>
            <div class="fw-bold text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.06em; color: #17243C; line-height: 1.4;">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</div>
        </div>

        <!-- Judul Rekapitulasi -->
        <div class="text-center mb-4">
            <h5 class="fw-bold text-uppercase mb-1" style="color: #17243C; letter-spacing: 0.04em; font-size: 1.05rem;">REKAPITULASI PENANGANAN LAPORAN GANGGUAN IT</h5>
            <div class="text-secondary small">
                Sistem Helpdesk Terpadu <strong>LayanIT</strong> &bull; Periode: {{ $reportStart->translatedFormat('d F Y') }} s.d. {{ $reportEnd->translatedFormat('d F Y') }}
            </div>
            <div class="text-muted" style="font-size: 11.5px; margin-top: 3px;">
                Tanggal Cetak: {{ $createdAt->translatedFormat('d F Y, H:i') }} WITA
            </div>
        </div>

        @if($isEmpty)
            <div class="text-center py-5 border rounded-3 bg-light my-4" style="border-color: #E2E8F0 !important;">
                <i class="bi bi-file-earmark-x display-6 text-muted opacity-50 d-block mb-2"></i>
                <div class="text-muted fw-medium">Tidak ada rekaman laporan gangguan IT pada rentang tanggal yang dipilih.</div>
            </div>
        @else
            <div class="table-responsive border rounded" style="border-color: #CBD5E1 !important;">
                <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 12px;">
                    <thead style="background-color: #F1F5F9; color: #1E293B; font-size: 11px; text-transform: uppercase;">
                        <tr>
                            <th class="text-center" style="width: 35px; border-color: #CBD5E1;">No</th>
                            <th style="width: 120px; border-color: #CBD5E1;">No. Tiket</th>
                            <th style="border-color: #CBD5E1;">Pelapor & Satker</th>
                            <th style="border-color: #CBD5E1;">Kategori & Judul</th>
                            <th style="border-color: #CBD5E1;">Lokasi</th>
                            <th style="border-color: #CBD5E1;">Padal</th>
                            <th style="width: 120px; border-color: #CBD5E1;">Status</th>
                            <th style="width: 85px; border-color: #CBD5E1;">Tgl Lapor</th>
                            <th style="width: 85px; border-color: #CBD5E1;">Tgl Selesai</th>
                            <th class="text-center" style="width: 60px; border-color: #CBD5E1;">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $index => $ticket)
                            <tr>
                                <td class="text-center" style="border-color: #CBD5E1;">{{ $index + 1 }}</td>
                                <td class="fw-bold font-monospace" style="color: #234ED8; border-color: #CBD5E1;">{{ $ticket->ticket_number }}</td>
                                <td style="border-color: #CBD5E1;">
                                    <div class="fw-semibold text-dark">{{ $ticket->user?->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $ticket->user?->satuan_kerja ?? '-' }}</small>
                                </td>
                                <td style="border-color: #CBD5E1;">
                                    <strong>{{ $ticket->title ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $ticket->category?->name ?? '-' }}</small>
                                    <x-ticket-event-time :ticket="$ticket" />
                                </td>
                                <td style="border-color: #CBD5E1;">{{ $ticket->location ?? '-' }}</td>
                                <td style="border-color: #CBD5E1;">{{ $ticket->padal?->name ?? '-' }}</td>
                                <td style="border-color: #CBD5E1;"><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                                <td style="border-color: #CBD5E1;">{{ $ticket->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td style="border-color: #CBD5E1;">{{ $ticket->completed_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-center" style="border-color: #CBD5E1;">
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
                <span>Total Rekapitulasi: <strong>{{ count($tickets) }}</strong> Laporan</span>
                <span>Bidang TIK Polda Kalimantan Selatan</span>
            </div>
        @endif
    </div>
</div>
@endsection
