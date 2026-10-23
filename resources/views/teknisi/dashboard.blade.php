@extends('layouts.app')

@section('title', 'Dashboard Teknisi')
@section('page_title', 'Dashboard Anggota Teknisi')
@section('page_subtitle', 'Pantau penugasan penanganan teknis IT yang ditugaskan kepada Anda (Akses Read-Only)')

@section('content')
<div class="alert alert-info d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
    <div>
        <strong>Informasi Hak Akses Teknisi:</strong>
        Peran Teknisi bertugas sebagai pelaksana teknis di lapangan dengan hak akses <em>Read-Only</em> (hanya membaca data tiket penugasan, deskripsi masalah, lokasi ruangan, dan status penanganan). Seluruh keputusan penugasan, perubahan status, dan penutupan tiket dipimpin oleh Perwira Pengendali (Padal).
    </div>
</div>

<!-- Statistik Teknisi -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Total Penugasan Tim</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total_penugasan'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-tools"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Sedang Dalam Penanganan</span>
                <h3 class="fw-bold mb-0 text-info mt-1">{{ $stats['dalam_penanganan'] }}</h3>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Telah Diselesaikan</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Penugasan Terbaru -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-card-checklist text-primary me-2"></i> Laporan Terbaru yang Ditugaskan ke Anda</h6>
        <a href="{{ route('teknisi.tickets.index') }}" class="small fw-semibold text-primary text-decoration-none">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Kategori & Judul</th>
                        <th>Lokasi Tindakan</th>
                        <th>Padal Pimpinan</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAssignedTickets as $ticket)
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
                            <td><strong class="text-secondary small">{{ $ticket->location }}</strong></td>
                            <td>
                                @if($ticket->padal)
                                    <small class="fw-semibold text-dark">{{ $ticket->padal->name }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('teknisi.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Lihat Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada tiket penugasan untuk Anda saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
