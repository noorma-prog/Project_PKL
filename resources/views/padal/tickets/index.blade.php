@extends('layouts.app')

@section('title', 'Tiket Penugasan Padal')
@section('page_title', 'Tiket Penugasan Padal')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('padal.tickets.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Cari laporan</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nomor tiket, judul, pelapor" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="menunggu_konfirmasi_padal" {{ request('status') == 'menunggu_konfirmasi_padal' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai (Menunggu Penilaian)</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Telah Dinilai</option>
                    <option value="ditolak_padal" {{ request('status') == 'ditolak_padal' ? 'selected' : '' }}>Ditolak Padal</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Tiket Penugasan -->
<div class="card mb-4">
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
                    @forelse($tickets as $ticket)
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
                                @if($ticket->technicians->isNotEmpty())
                                    <div class="small text-secondary mt-1">
                                        <i class="bi bi-tools text-info me-1"></i> Teknisi: {{ $ticket->technicians->pluck('name')->join(', ') }}
                                    </div>
                                @endif
                            </td>
                            <td data-label="Lokasi">
                                <span class="text-dark">{{ $ticket->location }}</span>
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
                            <td colspan="4" class="text-center py-5">
                                <div class="fw-bold text-dark mb-1">Belum ada hasil yang sesuai</div>
                                <p class="text-muted small mb-0">Ubah filter atau kosongkan kata pencarian untuk melihat penugasan lainnya.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">
                Menampilkan {{ $tickets->firstItem() }}-{{ $tickets->lastItem() }} dari {{ $tickets->total() }} laporan
            </span>
            <div>
                {{ $tickets->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
