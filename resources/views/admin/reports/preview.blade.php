@extends('layouts.app')

@section('title', 'Preview Rekapitulasi PDF')
@section('page_title', 'Preview Rekapitulasi Laporan')
@section('page_subtitle', 'Tinjau format laporan sebelum mengunduh PDF')

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Pratinjau Laporan</h5>
                    <div class="text-muted small">Periode: {{ $reportStart->translatedFormat('d F Y') }} - {{ $reportEnd->translatedFormat('d F Y') }}</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.recap', request()->query()) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="{{ route('admin.reports.recap.export', request()->query()) }}" class="btn btn-dark">
                        <i class="bi bi-download me-1"></i> Simpan PDF
                    </a>
                </div>
            </div>

            <div class="border rounded p-4" style="background: #fff;">
                <div class="text-center mb-4 border-bottom pb-3">
                    <div class="fw-bold text-uppercase" style="font-size: 1.1rem; letter-spacing: 0.05em;">KEPOLISIAN NEGARA REPUBLIK INDONESIA</div>
                    <div class="fw-bold text-uppercase" style="font-size: 1.05rem; letter-spacing: 0.05em;">DAERAH KALIMANTAN SELATAN</div>
                    <div class="fw-bold text-uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</div>
                </div>

                <h4 class="text-center fw-bold text-uppercase mb-3">Rekapitulasi Penanganan Laporan Gangguan IT</h4>
                <div class="text-center text-muted small mb-4">
                    LayanIT • Periode: {{ $reportStart->translatedFormat('d F Y') }} - {{ $reportEnd->translatedFormat('d F Y') }} • Dibuat: {{ $createdAt->translatedFormat('d F Y, H:i') }} WIB
                </div>

                @if($isEmpty)
                    <div class="alert alert-light text-center border">Tidak ada laporan untuk periode ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">No</th>
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
                                @foreach($tickets as $index => $ticket)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="fw-bold text-primary">{{ $ticket->ticket_number }}</td>
                                        <td>
                                            {{ $ticket->user?->name ?? '-' }}<br>
                                            <small class="text-muted">{{ $ticket->user?->satuan_kerja ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $ticket->title ?? '-' }}</strong><br>
                                            <small class="text-muted">{{ $ticket->category?->name ?? '-' }}</small>
                                        </td>
                                        <td>{{ $ticket->location ?? '-' }}</td>
                                        <td>{{ $ticket->padal?->name ?? '-' }}</td>
                                        <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                                        <td>{{ $ticket->created_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $ticket->completed_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            @if($ticket->rating)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> {{ $ticket->rating->rating }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
