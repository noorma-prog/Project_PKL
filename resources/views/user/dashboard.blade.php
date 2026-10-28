@extends('layouts.app')

@section('title', 'Dashboard Pelapor')
@section('page_title', 'Dashboard Pelapor')
@section('page_subtitle', 'Selamat datang di Sistem Helpdesk LayanIT POLDA')

@section('content')
<!-- Peringatan jika ada laporan yang selesai dan belum dinilai -->
@if($unratedTickets->isNotEmpty())
    <div class="alert alert-warning border-warning shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-star-fill text-warning fs-3"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1">Penilaian Pelayanan Diperlukan!</h6>
                <p class="mb-2 small">
                    Anda memiliki <strong>{{ $unratedTickets->count() }} laporan</strong> yang telah diselesaikan oleh tim Padal/Teknisi namun belum Anda beri penilaian rating dan ulasan.
                    Sesuai kebijakan sistem, Anda baru dapat mengajukan laporan permasalahan IT baru setelah memberikan rating pada laporan sebelumnya.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($unratedTickets as $unrated)
                        <a href="{{ route('user.tickets.show', $unrated->id) }}" class="btn btn-sm btn-dark">
                            <i class="bi bi-star me-1"></i> Nilai Tiket #{{ $unrated->ticket_number }} ({{ Str::limit($unrated->title, 25) }})
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Action Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold text-dark mb-0">Ringkasan Laporan Saya</h5>
        <small class="text-muted">Pantau perkembangan status penanganan kendala IT Anda</small>
    </div>
    <div>
        @if($unratedTickets->isNotEmpty())
            <button class="btn btn-secondary disabled" title="Selesaikan rating laporan sebelumnya terlebih dahulu">
                <i class="bi bi-lock-fill me-1"></i> Buat Laporan Baru
            </button>
        @else
            <a href="{{ route('user.tickets.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Laporan Baru
            </a>
        @endif
    </div>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('user.tickets.index') }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat seluruh {{ $stats['total'] }} laporan saya">
            <div>
                <span class="text-muted small fw-semibold">Total Laporan</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-journal-text"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('user.tickets.index', ['status' => 'menunggu_verifikasi']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['menunggu_verifikasi'] }} laporan menunggu verifikasi">
            <div>
                <span class="text-muted small fw-semibold">Menunggu Verifikasi</span>
                <h3 class="fw-bold mb-0 text-warning mt-1">{{ $stats['menunggu_verifikasi'] }}</h3>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('user.tickets.index', ['status' => 'dalam_penanganan']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['dalam_penanganan'] }} laporan dalam penanganan">
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
        <a href="{{ route('user.tickets.index', ['status' => 'selesai_semua']) }}" class="stat-card d-flex align-items-center justify-content-between text-decoration-none text-reset" aria-label="Lihat {{ $stats['selesai'] }} laporan selesai">
            <div>
                <span class="text-muted small fw-semibold">Laporan Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle"></i>
            </div>
        </a>
    </div>
</div>

<!-- Tabel Laporan Terbaru -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i> Laporan Terakhir Anda</h6>
        <a href="{{ route('user.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">Nomor Tiket</th>
                        <th>Judul Permasalahan</th>
                        <th>Kategori</th>
                        <th>Lokasi Satker</th>
                        <th>Status</th>
                        <th>Tanggal Lapor</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">
                                    {{ Str::limit($ticket->description, 60) }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ticket->category->name }}</span>
                            </td>
                            <td><small class="text-secondary">{{ $ticket->location }}</small></td>
                            <td>
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td><small class="text-muted">{{ $ticket->created_at->format('d M Y, H:i') }}</small></td>
                            <td class="text-end pe-3">
                                @if($ticket->status === \App\Models\Ticket::STATUS_SELESAI && !$ticket->rating)
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-warning text-dark fw-bold">
                                        <i class="bi bi-star me-1"></i> Beri Rating
                                    </a>
                                @elseif($ticket->rating)
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-circle me-1"></i> Rating Diberikan
                                    </a>
                                @else
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
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
