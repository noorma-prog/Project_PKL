@extends('layouts.app')

@section('title', 'Laporan Saya')
@section('page_title', 'Daftar Laporan Saya')
@section('page_subtitle', 'Riwayat seluruh tiket pengaduan gangguan IT yang Anda ajukan')

@section('content')
<!-- Peringatan jika belum rating -->
@if($unratedCount > 0)
    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-star-fill text-warning fs-4"></i>
            <div>
                <strong>Perhatian:</strong> Anda memiliki <strong>{{ $unratedCount }} tiket selesai</strong> yang belum diberi penilaian. Harap berikan rating agar dapat membuat laporan baru.
            </div>
        </div>
    </div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('user.tickets.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor tiket, judul, lokasi..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="menunggu_konfirmasi_padal" {{ request('status') == 'menunggu_konfirmasi_padal' ? 'selected' : '' }}>Menunggu Konfirmasi Padal</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai (Belum Dinilai)</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                    <option value="ditolak_admin" {{ request('status') == 'ditolak_admin' ? 'selected' : '' }}>Ditolak Admin</option>
                    <option value="ditolak_padal" {{ request('status') == 'ditolak_padal' ? 'selected' : '' }}>Ditolak Padal</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Data Laporan Gangguan IT</h6>
        @if($unratedCount > 0)
            <button class="btn btn-sm btn-secondary disabled" title="Beri rating laporan sebelumnya terlebih dahulu">
                <i class="bi bi-lock-fill me-1"></i> Buat Laporan Baru
            </button>
        @else
            <a href="{{ route('user.tickets.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Buat Laporan Baru
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket</th>
                        <th>Kategori & Judul</th>
                        <th>Lokasi Satker</th>
                        <th>Padal & Teknisi</th>
                        <th>Status</th>
                        <th>Waktu Pelaporan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                            </td>
                            <td><small class="text-secondary">{{ $ticket->location }}</small></td>
                            <td>
                                @if($ticket->padal)
                                    <div class="small fw-semibold text-dark"><i class="bi bi-person-badge text-warning me-1"></i> {{ $ticket->padal->nama_padal }}</div>
                                @else
                                    <small class="text-muted fst-italic">Belum ditugaskan</small>
                                @endif
                            </td>
                            <td>
                                <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                            </td>
                            <td><small class="text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end pe-3">
                                @if($ticket->status === \App\Models\Ticket::STATUS_SELESAI && !$ticket->rating)
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-warning text-dark fw-bold">
                                        <i class="bi bi-star me-1"></i> Beri Rating
                                    </a>
                                @elseif($ticket->rating)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Rating Diberikan ({{ $ticket->rating->rating }}/5)</span>
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
                                Tidak ada data laporan yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
