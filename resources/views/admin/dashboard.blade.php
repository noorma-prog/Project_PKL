@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page_title', 'Dashboard Administrator TIK')
@section('page_subtitle', 'Ringkasan operasional dan verifikasi layanan helpdesk IT POLDA')

@section('content')
<!-- Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Total Laporan</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total_tickets'] }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-ticket-detailed"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold">Perlu Verifikasi</span>
                <h3 class="fw-bold mb-0 text-danger mt-1">{{ $stats['menunggu_verifikasi'] }}</h3>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-exclamation-diamond-fill"></i>
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
                <span class="text-muted small fw-semibold">Laporan Selesai</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check2-all"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Sekunder: Personil & Rating -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-primary bg-opacity-10 p-2 text-primary fs-4"><i class="bi bi-person-badge"></i></div>
                <div>
                    <span class="text-muted small d-block">Perwira Pengendali (Padal)</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_padal'] }} Personil</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-info bg-opacity-10 text-info p-2 fs-4"><i class="bi bi-tools"></i></div>
                <div>
                    <span class="text-muted small d-block">Anggota Teknisi</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_teknisi'] }} Personil</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-secondary bg-opacity-10 text-secondary p-2 fs-4"><i class="bi bi-people"></i></div>
                <div>
                    <span class="text-muted small d-block">Total Pelapor (User)</span>
                    <strong class="fs-5 text-dark">{{ $stats['total_users'] }} Pengguna</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded bg-warning bg-opacity-10 text-warning p-2 fs-4"><i class="bi bi-star-fill"></i></div>
                <div>
                    <span class="text-muted small d-block">Kepuasan Pelayanan</span>
                    <strong class="fs-5 text-dark">{{ $stats['avg_rating'] }} / 5.0 Bintang</strong>
                </div>
            </div>
        </div>
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
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
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
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
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
@endsection
