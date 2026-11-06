@extends('layouts.app')

@section('title', 'Dashboard Pelapor')
@section('page_title', 'Dashboard Pelapor')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- Peringatan jika ada laporan yang selesai dan belum dinilai -->
@if($unratedTickets->isNotEmpty())
    <div class="alert alert-warning mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-star-fill text-warning fs-4 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">Penilaian Pelayanan Diperlukan</h6>
                <p class="mb-2 small">
                    Ada laporan selesai yang belum dinilai. Berikan penilaian sebelum membuat laporan baru.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($unratedTickets as $unrated)
                        <a href="{{ route('user.tickets.show', $unrated->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-star me-1"></i> Beri Penilaian #{{ $unrated->ticket_number }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<!-- 4 Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('user.tickets.index') }}" class="stat-card">
            <span class="stat-label">Total Laporan</span>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('user.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="stat-card">
            <span class="stat-label">Verifikasi</span>
            <div class="stat-value">{{ $stats['menunggu_verifikasi'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('user.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card">
            <span class="stat-label">Penanganan</span>
            <div class="stat-value">{{ $stats['dalam_penanganan'] }}</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('user.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card">
            <span class="stat-label">Selesai</span>
            <div class="stat-value">{{ $stats['selesai'] }}</div>
        </a>
    </div>
</div>

<!-- Tombol Buat Laporan Baru -->
<div class="mb-4">
    @if($unratedTickets->isNotEmpty())
        <button class="btn btn-disabled" disabled title="Selesaikan penilaian laporan sebelumnya terlebih dahulu">
            <i class="bi bi-lock-fill me-1"></i> Buat Laporan Baru
        </button>
    @else
        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Buat Laporan Baru
        </a>
    @endif
</div>

<!-- Jadwal Kegiatan Terdekat -->
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="user.tickets.show" />

<!-- Tabel Laporan Terakhir Anda -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Laporan Terakhir Anda</span>
        <a href="{{ route('user.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
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
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-3" data-label="Laporan / Jadwal">
                                <span class="ticket-id d-block">{{ $ticket->ticket_number }}</span>
                                <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                <div class="small text-muted">
                                    {{ $ticket->category?->name ?? 'Umum' }}
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
                                @if($ticket->status === \App\Models\Ticket::STATUS_SELESAI && !$ticket->rating)
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-star me-1"></i> Nilai
                                    </a>
                                @else
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">
                                        Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="4" class="text-center py-5 text-muted">
                                Belum ada laporan permasalahan IT yang diajukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
