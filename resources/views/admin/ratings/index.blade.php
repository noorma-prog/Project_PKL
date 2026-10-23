@extends('layouts.app')

@section('title', 'Rating & Ulasan Pelayanan')
@section('page_title', 'Rating & Penilaian Pelayanan')
@section('page_subtitle', 'Tinjau indeks kepuasan pengguna dan umpan balik atas pelayanan IT')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-4 text-center">
            <span class="text-muted small fw-semibold">Rata-rata Rating Pelayanan</span>
            <h1 class="display-4 fw-bold text-warning my-2">{{ $avgRating }}</h1>
            <div class="text-warning fs-5 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($avgRating))
                        <i class="bi bi-star-fill"></i>
                    @else
                        <i class="bi bi-star text-muted opacity-50"></i>
                    @endif
                @endfor
            </div>
            <small class="text-muted">Total {{ $ratings->total() }} ulasan kepuasan diterima</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-3 h-100 d-flex flex-column justify-content-center">
            <form method="GET" action="{{ route('admin.ratings.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Filter Berdasarkan Padal Penanggung Jawab:</label>
                    <select name="padal_id" class="form-select">
                        <option value="">-- Semua Padal --</option>
                        @foreach($padals as $padal)
                            <option value="{{ $padal->id }}" {{ request('padal_id') == $padal->id ? 'selected' : '' }}>
                                {{ $padal->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Filter Jumlah Bintang:</label>
                    <select name="rating" class="form-select">
                        <option value="">-- Semua Bintang --</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Bintang (Sangat Baik)</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Bintang (Baik)</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Bintang (Cukup)</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Bintang (Kurang)</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Bintang (Sangat Kurang)</option>
                    </select>
                </div>
                <div class="col-md-2 mt-auto">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-chat-quote-fill text-primary me-2"></i> Riwayat Ulasan & Penilaian Pelapor</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket & Masalah</th>
                        <th>Pelapor (User)</th>
                        <th>Padal Dinilai</th>
                        <th>Bintang</th>
                        <th>Ulasan / Masukan</th>
                        <th>Tanggal</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                        <tr>
                            <td class="ps-3">
                                <span class="font-monospace fw-bold text-primary">{{ $rating->ticket->ticket_number }}</span>
                                <div class="small fw-semibold text-dark">{{ Str::limit($rating->ticket->title, 35) }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $rating->user->name }}</div>
                                <small class="text-muted">{{ $rating->user->satuan_kerja }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $rating->padal->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="bi bi-star-fill text-dark me-1"></i> {{ $rating->rating }}
                                </span>
                            </td>
                            <td>
                                <small class="text-secondary fst-italic">
                                    {{ $rating->review ?: '(Tidak ada komentar tertulis)' }}
                                </small>
                            </td>
                            <td><small class="text-muted">{{ $rating->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.tickets.show', $rating->ticket_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Tiket
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada penilaian pelayanan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ratings->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $ratings->links() }}
        </div>
    @endif
</div>
@endsection
