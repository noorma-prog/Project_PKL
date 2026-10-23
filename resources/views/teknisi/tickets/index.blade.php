@extends('layouts.app')

@section('title', 'Daftar Tiket Penugasan Teknisi')
@section('page_title', 'Daftar Tiket Penugasan Tim')
@section('page_subtitle', 'Seluruh tiket penanganan IT yang melibatkan Anda sebagai tim pelaksana teknis')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('teknisi.tickets.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor tiket, judul, lokasi..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="dalam_penanganan" {{ request('status') == 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="selesai_dinilai" {{ request('status') == 'selesai_dinilai' ? 'selected' : '' }}>Selesai & Dinilai</option>
                </select>
            </div>
            <div class="col-md-2">
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
                <a href="{{ route('teknisi.tickets.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Daftar Tiket Penugasan Tim Teknisi (Read-Only)</h6>
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
                        <th>Padal Penanggung Jawab</th>
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
                            </td>
                            <td><strong class="text-secondary small"><i class="bi bi-geo-alt me-1"></i>{{ $ticket->location }}</strong></td>
                            <td>
                                @if($ticket->padal)
                                    <div class="small fw-semibold text-dark"><i class="bi bi-person-badge text-warning me-1"></i>{{ $ticket->padal->name }}</div>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td><span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td><small class="text-muted">{{ $ticket->assigned_at ? $ticket->assigned_at->format('d/m/Y H:i') : '-' }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('teknisi.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada tiket penugasan yang sesuai.</td>
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
