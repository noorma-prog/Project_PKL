@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page_title', 'Dashboard Administrator TIK')
@section('page_subtitle', 'Ringkasan operasional dan verifikasi layanan helpdesk IT POLDA')

@section('content')
<div class="admin-dashboard-polish">
<x-ticket-schedule-list :tickets="$scheduleTickets" show-route="admin.tickets.show" />
<!-- Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.tickets.index') }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat seluruh {{ $stats['total_tickets'] }} laporan">
            <div>
                <span class="text-muted small fw-semibold">Total Laporan</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total_tickets'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-ticket-detailed"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['menunggu_verifikasi'] }} laporan yang perlu verifikasi">
            <div>
                <span class="text-muted small fw-semibold">Perlu Verifikasi</span>
                <h3 class="fw-bold mb-0 text-danger mt-1">{{ $stats['menunggu_verifikasi'] }}</h3>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-exclamation-diamond-fill"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['dalam_penanganan'] }} laporan dalam penanganan">
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
        <a href="{{ route('admin.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['selesai'] }} laporan selesai">
            <div>
                <span class="text-muted small fw-semibold">Laporan Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check2-all"></i>
            </div>
        </a>
    </div>
</div>

<!-- Statistik Sekunder: Personil & Rating -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.padals.index') }}" class="card shadow-sm border-0 p-3 text-decoration-none text-reset">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-primary bg-opacity-10 p-2 text-primary fs-4"><i class="bi bi-person-badge"></i></div>
                <div>
                    <span class="text-muted small d-block">Perwira Pengendali (Padal)</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_padal'] }} Personil</strong>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index', ['role' => 'teknisi']) }}" class="card shadow-sm border-0 p-3 text-decoration-none text-reset">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-info bg-opacity-10 text-info p-2 fs-4"><i class="bi bi-tools"></i></div>
                <div>
                    <span class="text-muted small d-block">Anggota Teknisi</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_teknisi'] }} Personil</strong>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="card shadow-sm border-0 p-3 text-decoration-none text-reset">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-secondary bg-opacity-10 text-secondary p-2 fs-4"><i class="bi bi-people"></i></div>
                <div>
                    <span class="text-muted small d-block">Total Pelapor (User)</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_users'] }} Pengguna</strong>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.ratings.index') }}" class="card shadow-sm border-0 p-3 text-decoration-none text-reset">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-warning bg-opacity-10 text-warning p-2 fs-4"><i class="bi bi-star-fill"></i></div>
                <div>
                    <span class="text-muted small d-block">Kepuasan Pelayanan</span>
                    <strong class="fs-5 text-dark">{{ $stats['avg_rating'] }} / 5.0 Bintang</strong>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Laporan Membutuhkan Verifikasi Segera -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger rounded-pill">{{ $pendingVerificationCount }}</span>
            <h6 class="mb-0 fw-bold text-dark">Laporan Masuk Menunggu Verifikasi Administrator</h6>
        </div>
        <a href="{{ route('admin.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Semua Antrean <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">Nomor Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Kategori & Judul</th>
                        <th>Lokasi</th>
                        <th>Waktu Masuk</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingVerificationTickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->user->satuan_kerja ?? $ticket->user->alamat ?? 'Satker POLDA' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category?->name ?? '-' }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                <x-ticket-schedule :ticket="$ticket" />
                            </td>
                            <td><small class="text-secondary">{{ $ticket->location }}</small></td>
                            <td><small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-shield-check me-1"></i> Periksa & Tugaskan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                Tidak ada antrean laporan baru yang menunggu verifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Seluruh Laporan Terkini -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Laporan Aktivitas Terkini</h6>
        <a href="{{ route('admin.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
            Semua Laporan <i class="bi bi-arrow-right"></i>
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
                        <th>Padal Ditugaskan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-end pe-3">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->user->satuan_kerja ?? $ticket->user->alamat ?? 'Satker POLDA' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category?->name ?? '-' }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                <x-ticket-schedule :ticket="$ticket" />
                            </td>
                            <td>
                                @if($ticket->padal)
                                    <small class="fw-semibold text-dark">{{ $ticket->padal->nama_padal }}</small>
                                @else
                                    <small class="text-muted fst-italic">Belum ditugaskan</small>
                                @endif
                            </td>
                            <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td><small class="text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@section('styles')
<style>
    .admin-dashboard-polish {
        --admin-blue: #2563eb;
        --admin-red: #dc4c4c;
        --admin-cyan: #0891b2;
        --admin-green: #159466;
        --admin-indigo: #4f46e5;
        --admin-teal: #0f9b8e;
        --admin-neutral: #64748b;
        --admin-gold: #d89013;
    }

    .admin-dashboard-polish > section.card:first-of-type {
        overflow: hidden;
        margin-bottom: 1.5rem !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.9rem;
        box-shadow: 0 3px 12px rgba(15, 29, 56, 0.045) !important;
    }

    .admin-dashboard-polish > section.card:first-of-type > .card-header {
        padding: 1rem 1.25rem;
        border-bottom-color: #e8edf4;
        background: linear-gradient(110deg, #fff 0%, #f8fbff 100%) !important;
    }

    .admin-dashboard-polish > section.card:first-of-type > .card-header h2 {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        color: #17243b;
    }

    .admin-dashboard-polish > section.card:first-of-type > .card-header h2::before {
        width: 0.25rem;
        height: 1.25rem;
        border-radius: 1rem;
        background: linear-gradient(180deg, #2563eb, #38bdf8);
        content: "";
    }

    .admin-dashboard-polish > section.card:first-of-type .list-group-item {
        padding: 1rem 1.25rem;
        border-color: #edf1f6;
        overflow-wrap: break-word;
    }

    .admin-dashboard-polish > section.card:first-of-type .list-group-item:hover {
        background-color: #f8fbff;
    }

    .admin-dashboard-polish > .row {
        --bs-gutter-x: 1.15rem;
        --bs-gutter-y: 1.15rem;
        margin-bottom: 1.35rem !important;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card {
        --admin-tone: var(--admin-blue);
        --admin-tone-soft: #eff6ff;
        min-height: 118px;
        height: 100%;
        align-items: center;
        padding: 1.15rem 1.2rem;
        border: 1px solid #e3e9f1;
        border-inline-start: 3px solid var(--admin-tone);
        border-radius: 0.85rem;
        background: #fff;
        box-shadow: 0 2px 7px rgba(15, 29, 56, 0.045);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card:hover,
    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card:hover {
        border-color: var(--admin-tone);
        box-shadow: 0 7px 18px rgba(15, 29, 56, 0.09);
        transform: translateY(-2px);
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card h3 {
        color: var(--admin-tone) !important;
        font-size: clamp(1.65rem, 2vw, 1.9rem);
        letter-spacing: -0.04em;
        line-height: 1.1;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card > div:first-child {
        min-width: 0;
        padding-inline-end: 0.5rem;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card .small {
        display: block;
        color: #536176 !important;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"]:nth-child(2) > .stat-card {
        --admin-tone: var(--admin-red);
        --admin-tone-soft: #fff1f1;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"]:nth-child(3) > .stat-card {
        --admin-tone: var(--admin-cyan);
        --admin-tone-soft: #ecfeff;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"]:nth-child(4) > .stat-card {
        --admin-tone: var(--admin-green);
        --admin-tone-soft: #ecfdf5;
    }

    .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card .stat-icon {
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 13px;
        color: var(--admin-tone) !important;
        background: var(--admin-tone-soft) !important;
        font-size: 1.3rem;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card {
        --admin-tone: var(--admin-indigo);
        --admin-tone-soft: #eef2ff;
        min-height: 104px;
        height: 100%;
        align-items: center;
        padding: 1rem !important;
        border: 1px solid #e3e9f1 !important;
        border-inline-start: 3px solid var(--admin-tone);
        border-radius: 0.85rem;
        box-shadow: 0 2px 7px rgba(15, 29, 56, 0.045) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card > .d-flex {
        min-width: 0;
        gap: 0.85rem !important;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card > .d-flex > div:first-child {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        border-radius: 12px !important;
        color: var(--admin-tone) !important;
        background: var(--admin-tone-soft) !important;
        font-size: 1.2rem !important;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card > .d-flex > div:last-child {
        min-width: 0;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card .small {
        color: #5b687c !important;
        font-size: 0.76rem;
        line-height: 1.4;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card strong {
        display: block;
        color: #17243b !important;
        font-size: 1rem;
        line-height: 1.3;
        overflow-wrap: break-word;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"]:nth-child(2) > .card {
        --admin-tone: var(--admin-teal);
        --admin-tone-soft: #ecfdf9;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"]:nth-child(3) > .card {
        --admin-tone: var(--admin-neutral);
        --admin-tone-soft: #f1f5f9;
    }

    .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"]:nth-child(4) > .card {
        --admin-tone: var(--admin-gold);
        --admin-tone-soft: #fffbeb;
    }

    .admin-dashboard-polish > .card {
        overflow: hidden;
        border-radius: 0.85rem;
        box-shadow: 0 2px 8px rgba(15, 29, 56, 0.045) !important;
    }

    .admin-dashboard-polish > .card > .card-header {
        min-height: 56px;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-bottom-color: #e8edf4;
    }

    .admin-dashboard-polish > .card > .card-body .table {
        --bs-table-border-color: #edf1f6;
    }

    .admin-dashboard-polish > .card > .card-body .table thead th {
        padding: 0.8rem 0.75rem;
        color: #536176;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .admin-dashboard-polish > .card > .card-body .table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
    }

    .admin-dashboard-polish > .card > .card-body .table tbody tr:last-child > * {
        border-bottom: 0;
    }

    @media (max-width: 575.98px) {
        .admin-dashboard-polish > .row {
            --bs-gutter-x: 0.85rem;
            --bs-gutter-y: 0.85rem;
            margin-bottom: 1.1rem !important;
        }

        .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card {
            min-height: 104px;
            padding: 0.9rem;
        }

        .admin-dashboard-polish > .row:first-of-type > [class*="col-"] > .stat-card .stat-icon {
            flex-basis: 40px;
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }

        .admin-dashboard-polish > .row:nth-of-type(2) > [class*="col-"] > .card {
            min-height: 96px;
            padding: 0.85rem !important;
        }

        .admin-dashboard-polish > .card > .card-header {
            padding: 0.8rem 0.9rem;
        }

        .admin-dashboard-polish > section.card:first-of-type > .card-header,
        .admin-dashboard-polish > section.card:first-of-type .list-group-item {
            padding-inline: 0.9rem;
        }
    }
</style>
@endsection
