@extends('layouts.app')

@section('title', 'Dashboard Padal')
@section('page_title', 'Dashboard Perwira Pengendali (Padal)')
@section('page_subtitle', 'Kelola penugasan tiket, koordinasi tim teknisi, dan penyelesaian laporan IT')

@section('content')
<!-- Statistik Padal -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Tiket Ditugaskan</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total_ditugaskan'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-clipboard-check"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Menunggu Konfirmasi</span>
                <h3 class="fw-bold mb-0 text-warning mt-1">{{ $stats['menunggu_konfirmasi'] }}</h3>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Dalam Penanganan</span>
                <h3 class="fw-bold mb-0 text-info mt-1">{{ $stats['dalam_penanganan'] }}</h3>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Telah Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Anggota Teknisi Tim Anda ({{ $members->count() }})</h6>
        <a href="{{ route('padal.members.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Anggota
        </a>
    </div>
    <div class="card-body">
        @forelse($members as $member)
            <span class="badge bg-light text-dark border me-1 mb-1">
                {{ $member->name }} &mdash; {{ $member->satuan_kerja ?? 'Bid TIK' }}
            </span>
        @empty
            <p class="text-muted small mb-0">Belum ada teknisi di tim. Tambahkan anggota untuk dapat memilih teknisi pada penugasan tiket.</p>
        @endforelse
    </div>
</div>

<!-- Laporan Menunggu Konfirmasi Padal -->
@if($pendingTickets->isNotEmpty())
    <div class="card border-warning shadow-sm mb-4">
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
                                    <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                    <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
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
<div class="card shadow-sm border-0 mb-4">
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
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
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
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada tiket yang sedang dalam proses penanganan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
