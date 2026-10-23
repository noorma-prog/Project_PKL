@extends('layouts.app')

@section('title', 'Tiket Penugasan Padal')
@section('page_title', 'Daftar Tiket Penugasan Padal')
@section('page_subtitle', 'Seluruh tiket permasalahan IT yang ditugaskan kepada Anda oleh Administrator')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('padal.tickets.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor tiket, judul, pelapor..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_konfirmasi_padal" {{ request('status') == 'menunggu_konfirmasi_padal' ? 'selected' : '' }}>Menunggu Konfirmasi Saya</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai (Menunggu Penilaian)</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                    <option value="ditolak_padal" {{ request('status') == 'ditolak_padal' ? 'selected' : '' }}>Ditolak oleh Saya</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                <a href="{{ route('padal.tickets.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Data Tiket Penugasan Saya</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Judul Permasalahan</th>
                        <th>Anggota Teknisi</th>
                        <th>Status</th>
                        <th>Waktu Ditugaskan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $ticket->user->name }}</div>
                                <small class="text-muted">{{ $ticket->user->satuan_kerja ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                                <div class="fw-semibold text-dark">{{ $ticket->title }}</div>
                                <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $ticket->location }}</small>
                            </td>
                            <td>
                                @if($ticket->technicians->isNotEmpty())
                                    @foreach($ticket->technicians as $tech)
                                        <div class="small text-dark"><i class="bi bi-person me-1 text-info"></i>{{ $tech->name }}</div>
                                    @endforeach
                                @else
                                    <small class="text-muted fst-italic">Belum dipilih</small>
                                @endif
                            </td>
                            <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td><small class="text-muted">{{ $ticket->assigned_at ? $ticket->assigned_at->format('d/m/Y H:i') : '-' }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('padal.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Detail / Respon
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada penugasan tiket yang sesuai.</td>
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
