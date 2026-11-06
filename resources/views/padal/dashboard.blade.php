@extends('layouts.app')

@section('title', 'Dashboard Padal')
@section('page_title', 'Dashboard Padal')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- 4 Statistik Padal -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('padal.tickets.index') }}" class="stat-card">
            <span class="stat-label">Ditugaskan</span>
            <div class="stat-value">{{ $stats['total_ditugaskan'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'menunggu_konfirmasi_padal']) }}" class="stat-card">
            <span class="stat-label">Konfirmasi</span>
            <div class="stat-value" style="color: var(--civic-warning);">{{ $stats['menunggu_konfirmasi'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card">
            <span class="stat-label">Penanganan</span>
            <div class="stat-value">{{ $stats['dalam_penanganan'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card">
            <span class="stat-label">Selesai</span>
            <div class="stat-value" style="color: var(--civic-success);">{{ $stats['selesai'] }}</div>
        </a>
    </div>
</div>

<!-- Jadwal Kegiatan Terdekat -->
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="padal.tickets.show" />

<!-- Penugasan Baru Menunggu Konfirmasi -->
@if($pendingTickets->isNotEmpty())
    <div class="card mb-4" style="border-color: #FDE68A;">
        <div class="card-header" style="background: #FFFBEB;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark">{{ $pendingTickets->count() }}</span>
                <span class="fw-bold text-dark">Penugasan Baru Menunggu Konfirmasi</span>
            </div>
            <small class="text-muted">Harap konfirmasi penerimaan atau tolak dengan alasan jelas</small>
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
                        @foreach($pendingTickets as $ticket)
                            <tr>
                                <td class="ps-3" data-label="Laporan / Jadwal">
                                    <span class="ticket-id d-block">{{ $ticket->ticket_number }}</span>
                                    <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                    <div class="small text-muted">
                                        {{ $ticket->user?->name }} • {{ $ticket->category?->name ?? 'Umum' }}
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
                                    <a href="{{ route('padal.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Laporan Aktif Dalam Penanganan -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Laporan Aktif Dalam Penanganan Tim Anda</span>
        <a href="{{ route('padal.tickets.index', ['status' => 'dalam_penanganan']) }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">LAPORAN / JADWAL</th>
                        <th>LOKASI</th>
                        <th>TEKNISI</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeTickets as $ticket)
                        <tr>
                            <td class="ps-3" data-label="Laporan / Jadwal">
                                <span class="ticket-id d-block">{{ $ticket->ticket_number }}</span>
                                <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                <div class="small text-muted">
                                    {{ $ticket->user?->name }} ({{ $ticket->user?->satuan_kerja ?? 'POLDA' }})
                                </div>
                            </td>
                            <td data-label="Lokasi">
                                <span class="text-dark">{{ $ticket->location }}</span>
                            </td>
                            <td data-label="Teknisi">
                                <span class="text-dark small">
                                    {{ $ticket->technicians->isNotEmpty() ? $ticket->technicians->pluck('name')->join(', ') : 'Belum ditugaskan' }}
                                </span>
                            </td>
                            <td data-label="Status">
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td class="text-end pe-3" data-label="Aksi">
                                <a href="{{ route('padal.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada tiket yang sedang dalam proses penanganan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Anggota Teknisi Tim Anda -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Anggota Teknisi Tim Anda ({{ $members->count() }} personel)</span>
        <a href="{{ route('padal.members.index') }}" class="small fw-semibold text-primary text-decoration-none">
            Kelola Anggota <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-3">
        @forelse($members as $member)
            <span class="badge bg-light text-dark me-1 mb-1 p-2">
                <i class="bi bi-person me-1"></i> {{ $member->name }} ({{ $member->nrp_nip ?? '-' }})
            </span>
        @empty
            <p class="text-muted small mb-0">Belum ada teknisi di tim. Tambahkan anggota untuk dapat memilih teknisi pada penugasan tiket.</p>
        @endforelse
    </div>
</div>
@endsection
