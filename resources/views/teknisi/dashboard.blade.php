@extends('layouts.app')

@section('title', 'Dashboard Teknisi')
@section('page_title', 'Dashboard Teknisi')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- 3 Statistik Teknisi -->
<div class="row g-3 mb-4">
    <div class="col-4 col-md-4">
        <a href="{{ route('teknisi.tickets.index') }}" class="stat-card">
            <span class="stat-label">Penugasan</span>
            <div class="stat-value">{{ $stats['total_penugasan'] }}</div>
        </a>
    </div>
    <div class="col-4 col-md-4">
        <a href="{{ route('teknisi.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card">
            <span class="stat-label">Penanganan</span>
            <div class="stat-value">{{ $stats['dalam_penanganan'] }}</div>
        </a>
    </div>
    <div class="col-4 col-md-4">
        <a href="{{ route('teknisi.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card">
            <span class="stat-label">Selesai</span>
            <div class="stat-value" style="color: var(--civic-success);">{{ $stats['selesai'] }}</div>
        </a>
    </div>
</div>

<!-- Jadwal Kegiatan Terdekat -->
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="teknisi.tickets.show" />

<!-- Tabel Laporan Terbaru yang Ditugaskan -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Laporan Terbaru yang Ditugaskan</span>
        <a href="{{ route('teknisi.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
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
                        <th>STATUS</th>
                        <th class="text-end pe-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAssignedTickets as $ticket)
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
                                <a href="{{ route('teknisi.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada tiket penugasan untuk Anda saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
