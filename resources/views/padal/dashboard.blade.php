@extends('layouts.app')

@section('title', 'Dashboard Padal')
@section('page_title', 'Dashboard Perwira Pengendali (Padal)')
@section('page_subtitle', 'Kelola penugasan tiket, koordinasi tim teknisi, dan penyelesaian laporan IT')

@section('styles')
<style>
    .padal-dashboard > * {
        animation: padal-dashboard-enter 0.4s ease both;
    }

    .padal-dashboard > *:nth-child(2) { animation-delay: 50ms; }
    .padal-dashboard > *:nth-child(3) { animation-delay: 100ms; }
    .padal-dashboard > *:nth-child(4) { animation-delay: 150ms; }

    .padal-dashboard .padal-schedule-card,
    .padal-dashboard .padal-members-card,
    .padal-dashboard .padal-table-card {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.9rem;
        box-shadow: 0 2px 8px rgba(15, 29, 56, 0.045) !important;
    }

    .padal-dashboard .padal-table-card.border-warning {
        border-color: var(--bs-warning) !important;
    }

    .padal-dashboard .padal-schedule-card .card-header,
    .padal-dashboard .padal-members-card .card-header,
    .padal-dashboard .padal-table-card .card-header {
        padding: 1rem 1.25rem;
        border-bottom-color: #edf1f6;
    }

    .padal-dashboard .padal-schedule-card .card-header h2 {
        color: #172554;
        letter-spacing: -0.02em;
    }

    .padal-dashboard .padal-schedule-card .card-header p {
        color: #64748b !important;
        line-height: 1.55;
    }

    .padal-dashboard .padal-schedule-card .list-group-item {
        padding: 1rem 1.25rem !important;
        border-color: #edf1f6;
        transition: background-color 0.2s ease, padding 0.2s ease;
    }

    .padal-dashboard .padal-schedule-card a.list-group-item:hover {
        padding-left: 1.5rem !important;
        background-color: #f8faff;
    }

    .padal-dashboard .padal-schedule-card .list-group-item > div > div:first-child {
        color: #1e293b;
        font-weight: 650;
    }

    .padal-dashboard .padal-schedule-card .list-group-item .mt-1 {
        line-height: 1.7;
    }

    .padal-dashboard .padal-schedule-card .badge {
        flex-shrink: 0;
        padding: 0.4rem 0.65rem;
        border-radius: 999px;
        font-weight: 650;
    }

    .padal-dashboard .padal-schedule-card .list-group-item:only-child {
        padding: 1.5rem 1rem !important;
        color: #64748b !important;
        background: #fbfcfe;
        text-align: center;
    }

    .padal-dashboard .padal-stat-card {
        min-height: 94px;
        padding: 1.2rem 1.25rem;
        border-color: #e1e8f0;
        border-radius: 0.9rem;
        box-shadow: 0 2px 7px rgba(15, 29, 56, 0.045);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .padal-dashboard .padal-stat-card:hover,
    .padal-dashboard .padal-stat-card:focus-visible {
        transform: translateY(-3px);
        border-color: #cbd9ee;
        box-shadow: 0 10px 22px rgba(15, 29, 56, 0.1);
    }

    .padal-dashboard .padal-stat-card:focus-visible,
    .padal-dashboard .padal-members-card a:focus-visible,
    .padal-dashboard .card a:focus-visible {
        outline: 3px solid rgba(37, 99, 235, 0.25);
        outline-offset: 2px;
    }

    .padal-dashboard .padal-stat-card span {
        letter-spacing: 0.005em;
    }

    .padal-dashboard .padal-stat-card h3 {
        font-size: 1.75rem;
        line-height: 1.15;
        letter-spacing: -0.04em;
    }

    .padal-dashboard .padal-stat-card .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        font-size: 1.3rem;
        transition: transform 0.2s ease;
    }

    .padal-dashboard .padal-stat-card:hover .stat-icon {
        transform: scale(1.06);
    }

    .padal-dashboard .padal-members-card .card-body {
        padding: 1rem 1.25rem;
    }

    .padal-dashboard .padal-members-card .card-body > p {
        padding: 0.85rem 1rem;
        border: 1px dashed #d7e0eb;
        border-radius: 0.75rem;
        background: #fbfcfe;
        line-height: 1.6;
    }

    .padal-dashboard .padal-member-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
        gap: 0.65rem;
    }

    .padal-dashboard .padal-member-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        padding: 0.7rem 0.8rem;
        border: 1px solid #e8edf4;
        border-radius: 0.75rem;
        background: #fbfcfe;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .padal-dashboard .padal-member-row:hover {
        transform: translateY(-1px);
        border-color: #cbd9ee;
        background: #f5f8ff;
    }

    .padal-dashboard .padal-member-avatar {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #1d4ed8;
        background: #eaf1ff;
        font-size: 0.9rem;
        font-weight: 750;
    }

    .padal-dashboard .padal-member-name {
        overflow: hidden;
        color: #1e293b;
        font-size: 0.875rem;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .padal-dashboard .padal-member-unit {
        color: #64748b;
        font-size: 0.75rem;
    }

    .padal-dashboard .padal-manage-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    .padal-dashboard .padal-manage-link:hover {
        transform: translateY(-1px);
    }

    .padal-dashboard .padal-table-card .table {
        --bs-table-hover-bg: #f7f9fd;
    }

    .padal-dashboard .padal-table-card thead th {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #e6ebf2;
        background: #f7f9fc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 750;
        letter-spacing: 0.035em;
        white-space: nowrap;
    }

    .padal-dashboard .padal-table-card tbody td {
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
        border-color: #edf1f6;
        color: #475569;
        vertical-align: middle;
    }

    .padal-dashboard .padal-table-card tbody tr {
        transition: background-color 0.18s ease;
    }

    .padal-dashboard .padal-table-card tbody .badge {
        border-radius: 999px;
        font-weight: 600;
    }

    .padal-dashboard .padal-table-card .btn {
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .padal-dashboard .padal-table-card .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 29, 56, 0.12);
    }

    .padal-dashboard .padal-empty-state {
        padding: 2rem 1rem !important;
        color: #64748b !important;
        background: #fbfcfe;
    }

    @keyframes padal-dashboard-enter {
        from { opacity: 0; transform: translateY(7px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 575.98px) {
        .padal-dashboard .padal-schedule-card .card-header,
        .padal-dashboard .padal-members-card .card-header,
        .padal-dashboard .padal-table-card .card-header {
            padding: 0.9rem 1rem;
        }

        .padal-dashboard .padal-stat-card {
            min-height: 88px;
            padding: 1rem;
        }

        .padal-dashboard .padal-members-card .card-body {
            padding: 0.9rem 1rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .padal-dashboard > *,
        .padal-dashboard *,
        .padal-dashboard *::before,
        .padal-dashboard *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
@endsection

@section('content')
<div class="padal-dashboard">
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="padal.tickets.show" class="padal-schedule-card" />
<!-- Statistik Padal -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('padal.tickets.index') }}" class="stat-card padal-stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat seluruh {{ $stats['total_ditugaskan'] }} tiket yang ditugaskan">
            <div>
                <span class="text-muted small fw-semibold">Tiket Ditugaskan</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total_ditugaskan'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-clipboard-check"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'menunggu_konfirmasi_padal']) }}" class="stat-card padal-stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['menunggu_konfirmasi'] }} tiket yang menunggu konfirmasi">
            <div>
                <span class="text-muted small fw-semibold">Menunggu Konfirmasi</span>
                <h3 class="fw-bold mb-0 text-warning mt-1">{{ $stats['menunggu_konfirmasi'] }}</h3>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock-history"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card padal-stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['dalam_penanganan'] }} tiket dalam penanganan">
            <div>
                <span class="text-muted small fw-semibold">Dalam Penanganan</span>
                <h3 class="fw-bold mb-0 text-info mt-1">{{ $stats['dalam_penanganan'] }}</h3>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('padal.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card padal-stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['selesai'] }} tiket selesai">
            <div>
                <span class="text-muted small fw-semibold">Telah Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </a>
    </div>
</div>

<section class="card border-0 shadow-sm mb-4 padal-members-card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Anggota Teknisi Tim Anda ({{ $members->count() }})</h6>
        <a href="{{ route('padal.members.index') }}" class="btn btn-sm btn-outline-primary padal-manage-link" aria-label="Kelola {{ $stats['total_anggota'] }} anggota teknisi tim Anda">
            <i class="bi bi-people-fill me-1"></i> Kelola Anggota
        </a>
    </div>
    <div class="card-body">
        @forelse($members as $member)
            @if($loop->first)<div class="padal-member-list">@endif
                <div class="padal-member-row">
                    <span class="padal-member-avatar" aria-hidden="true">{{ Str::of($member->name)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') }}</span>
                    <div class="min-w-0">
                        <div class="padal-member-name">{{ $member->name }}</div>
                        <div class="padal-member-unit">{{ $member->satuan_kerja ?? 'Bid TIK' }}</div>
                    </div>
                </div>
            @if($loop->last)</div>@endif
        @empty
            <p class="text-muted small mb-0">Belum ada teknisi di tim. Tambahkan anggota untuk dapat memilih teknisi pada penugasan tiket.</p>
        @endforelse
    </div>
</section>

<!-- Laporan Menunggu Konfirmasi Padal -->
@if($pendingTickets->isNotEmpty())
    <div class="card border-warning shadow-sm mb-4 padal-table-card">
        <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark rounded-pill">{{ $pendingTickets->count() }}</span>
                <h6 class="mb-0 fw-bold text-dark">Penugasan Baru Menunggu Konfirmasi Anda</h6>
            </div>
            <small class="text-muted">Harap terima dan tetapkan teknisi, atau tolak dengan alasan jelas</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-secondary">
                        <tr>
                            <th class="ps-3">No. Tiket</th>
                            <th>Pelapor & Satker</th>
                            <th>Judul Permasalahan</th>
                            <th>Lokasi</th>
                            <th>Waktu Ditugaskan</th>
                            <th class="text-end pe-3">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingTickets as $ticket)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                    <small class="text-muted">{{ $ticket->user->satuan_kerja }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border mb-1">{{ $ticket->category?->name ?? '-' }}</span>
                                    <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                    <x-ticket-schedule :ticket="$ticket" />
                                </td>
                                <td><small class="text-secondary">{{ $ticket->location }}</small></td>
                                <td><small class="text-muted">{{ $ticket->assigned_at ? $ticket->assigned_at->diffForHumans() : '-' }}</small></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('padal.tickets.show', $ticket->id) }}" class="btn btn-sm btn-warning text-dark fw-bold">
                                        <i class="bi bi-check-square me-1"></i> Respon Penugasan
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
<div class="card shadow-sm border-0 mb-4 padal-table-card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Laporan Aktif Dalam Penanganan Tim Anda</h6>
        <a href="{{ route('padal.tickets.index', ['status' => 'dalam_penanganan']) }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket</th>
                        <th>Pelapor</th>
                        <th>Judul Permasalahan</th>
                        <th>Anggota Teknisi</th>
                        <th>Mulai Penanganan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeTickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->location }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category?->name ?? '-' }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                <x-ticket-schedule :ticket="$ticket" />
                            </td>
                            <td>
                                @forelse($ticket->technicians as $tech)
                                    <span class="badge bg-info text-dark me-1"><i class="bi bi-person me-1"></i>{{ $tech->name }}</span>
                                @empty
                                    <span class="text-muted small">Belum ditugaskan</span>
                                @endforelse
                            </td>
                            <td><small class="text-muted">{{ $ticket->started_at ? $ticket->started_at->diffForHumans() : '-' }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('padal.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Kelola / Selesaikan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted padal-empty-state">Tidak ada tiket yang sedang dalam proses penanganan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection
