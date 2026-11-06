@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page_title', 'Dashboard Administrator')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- 4 Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.tickets.index') }}" class="stat-card">
            <span class="stat-label">Total Laporan</span>
            <div class="stat-value">{{ $stats['total_tickets'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="stat-card">
            <span class="stat-label">Verifikasi</span>
            <div class="stat-value" style="color: var(--civic-danger);">{{ $stats['menunggu_verifikasi'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card">
            <span class="stat-label">Penanganan</span>
            <div class="stat-value">{{ $stats['dalam_penanganan'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card">
            <span class="stat-label">Selesai</span>
            <div class="stat-value" style="color: var(--civic-success);">{{ $stats['selesai'] }}</div>
        </a>
    </div>
</div>

<!-- Jadwal Kegiatan Terdekat -->
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="admin.tickets.show" />

<!-- Menunggu Verifikasi Administrator -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Menunggu Verifikasi Administrator</span>
        <a href="{{ route('admin.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Antrean ({{ $pendingVerificationCount }}) <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">LAPORAN / JADWAL</th>
                        <th>LOKASI</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingVerificationTickets as $ticket)
                        <tr>
                            <td class="ps-3" data-label="Laporan / Jadwal">
                                <span class="ticket-id d-block">{{ $ticket->ticket_number }}</span>
                                <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                <div class="small text-muted">
                                    {{ $ticket->user?->name }} ({{ $ticket->user?->satuan_kerja ?? 'POLDA' }})
                                    @if($ticket->event_date)
                                        • {{ $ticket->event_date->format('d M Y') }}
                                        @if($ticket->event_start_time && $ticket->event_end_time)
                                            , {{ substr($ticket->event_start_time, 0, 5) }}-{{ substr($ticket->event_end_time, 0, 5) }}
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td data-label="Lokasi">
                                <span class="text-dark">{{ $ticket->location }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td class="text-end pe-3" data-label="Aksi">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="4" class="text-center py-4 text-muted">
                                Tidak ada antrean laporan baru yang menunggu verifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Laporan Aktivitas Terkini -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Laporan Aktivitas Terkini</span>
        <a href="{{ route('admin.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
            Semua Laporan <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">LAPORAN / JADWAL</th>
                        <th>LOKASI</th>
                        <th>PADAL</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-3" data-label="Laporan / Jadwal">
                                <span class="ticket-id d-block">{{ $ticket->ticket_number }}</span>
                                <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                <div class="small text-muted">
                                    {{ $ticket->user?->name }} • {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td data-label="Lokasi">
                                <span class="text-dark">{{ $ticket->location }}</span>
                            </td>
                            <td data-label="Padal">
                                <span class="text-dark">{{ $ticket->padal?->nama_padal ?? '-' }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td class="text-end pe-3" data-label="Aksi">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
