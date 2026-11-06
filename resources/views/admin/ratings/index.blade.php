@extends('layouts.app')

@section('title', 'Rating & Ulasan')
@section('page_title', 'Rating & Ulasan')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- Rata-rata & Filter Bar -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100 p-4 d-flex flex-column justify-content-center text-center">
            <span class="text-muted small fw-semibold">Rata-rata Rating</span>
            <div class="display-5 fw-bold text-dark my-2">{{ number_format((float) $avgRating, 1) }} / 5</div>
            <div class="text-warning mb-1">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                @endfor
            </div>
            <small class="text-muted">Total {{ $ratings->total() }} penilaian diterima</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100 p-4 d-flex flex-column justify-content-center">
            <form method="GET" action="{{ route('admin.ratings.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="padal_id" class="form-label">Unit Padal</label>
                    <select name="padal_id" id="padal_id" class="form-select">
                        <option value="">Semua unit</option>
                        @foreach($padals as $padal)
                            <option value="{{ $padal->id }}" {{ request('padal_id') == $padal->id ? 'selected' : '' }}>
                                {{ $padal->nama_padal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="rating" class="form-label">Rating</label>
                    <select name="rating" id="rating" class="form-select">
                        <option value="">Semua rating</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Bintang</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Bintang</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Bintang</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Bintang</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Bintang</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rata-rata dan Jumlah Rating per Padal -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Rata-rata dan Jumlah Rating per Padal</span>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            @forelse($padals as $padal)
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 bg-light rounded border">
                        <div class="fw-bold text-dark">{{ $padal->nama_padal }}</div>
                        <div class="small text-muted mt-1">
                            @if($padal->ratings_count)
                                <span class="text-warning fw-bold"><i class="bi bi-star-fill"></i> {{ number_format((float) $padal->ratings_avg_rating, 1) }} / 5</span>
                                • {{ $padal->ratings_count }} penilaian
                            @else
                                Belum ada penilaian
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted small">Belum ada data unit Padal.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Daftar Ulasan Pelapor -->
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-bold">Ulasan Pengguna</span>
    </div>
    <div class="card-body p-0">
        <div class="d-flex flex-column">
            @forelse($ratings as $rating)
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold text-dark">{{ $rating->user?->name }}</div>
                            <div class="text-warning small my-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <small class="text-muted">{{ $rating->created_at->format('d M Y, H:i') }} WITA</small>
                    </div>
                    <p class="text-dark mb-2" style="font-size: 14px; line-height: 1.5;">
                        {{ $rating->review ?: '(Tidak ada ulasan tertulis)' }}
                    </p>
                    <div class="small text-muted">
                        <span class="ticket-id">{{ $rating->ticket?->ticket_number }}</span>
                        @if($rating->ticket?->padal)
                            • {{ $rating->ticket->padal->nama_padal }}
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-muted">Belum ada ulasan yang sesuai dengan kriteria filter.</div>
            @endforelse
        </div>
    </div>
    @if($ratings->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">
                Menampilkan {{ $ratings->firstItem() }}-{{ $ratings->lastItem() }} dari {{ $ratings->total() }} ulasan
            </span>
            <div>
                {{ $ratings->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
