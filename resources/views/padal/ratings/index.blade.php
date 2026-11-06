@extends('layouts.app')

@section('title', 'Penilaian Pelayanan')
@section('page_title', 'Penilaian Pelayanan dari Pelapor')
@section('page_subtitle', 'Indeks kepuasan pengguna dan evaluasi hasil penanganan laporan IT unit kerja Anda')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-5">
        <div class="card shadow-sm border-0 rounded-3 p-4 text-center h-100 bg-white">
            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Rata-rata Penilaian Kinerja</span>
            <div class="d-flex align-items-baseline justify-content-center my-2">
                <span class="display-4 fw-bold text-dark">{{ number_format($avgRating, 1) }}</span>
                <span class="text-muted fs-5 ms-1">/ 5.0</span>
            </div>
            <div class="text-warning fs-5 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($avgRating))
                        <i class="bi bi-star-fill text-warning"></i>
                    @else
                        <i class="bi bi-star text-muted opacity-50"></i>
                    @endif
                @endfor
            </div>
            <div class="small text-muted mt-auto pt-2 border-top">
                <i class="bi bi-chat-heart text-primary me-1"></i> Total <strong>{{ $totalRatings }}</strong> penilaian diterima dari pelapor
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-7">
        <div class="card shadow-sm border-0 rounded-3 p-4 h-100 bg-white d-flex flex-column justify-content-center">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Standar Evaluasi Pelayanan LayanIT</h6>
                    <p class="text-secondary small mb-2">
                        Setiap tiket yang diselesaikan oleh Padal dan tim teknisi akan menerima evaluasi berupa bintang kepuasan (1 - 5) dan catatan evaluasi langsung dari personel pelapor.
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle text-primary me-1"></i> Ulasan ini menjadi parameter akuntabilitas mutu dan kecepatan helpdesk TIK Polda Kalimantan Selatan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-star-half text-warning me-2"></i> Riwayat Ulasan & Penilaian Pelapor</h6>
            <small class="text-muted">Daftar masukan terhadap penanganan yang dipimpin oleh akun Anda</small>
        </div>
        <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">{{ $totalRatings }} Ulasan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3" style="width: 140px;">No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Permasalahan IT</th>
                        <th style="width: 130px;">Rating</th>
                        <th>Ulasan / Catatan Pelapor</th>
                        <th style="width: 140px;">Waktu Dinilai</th>
                        <th class="text-end pe-3" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                        <tr>
                            <td class="ps-3" data-label="No. Tiket">
                                <span class="fw-bold text-primary font-monospace">{{ $rating->ticket->ticket_number }}</span>
                            </td>
                            <td data-label="Pelapor">
                                <div class="fw-semibold text-dark">{{ $rating->user->name }}</div>
                                <small class="text-muted">{{ $rating->user->satuan_kerja ?? '-' }}</small>
                            </td>
                            <td data-label="Permasalahan">
                                <div class="small fw-semibold text-dark">{{ Str::limit($rating->ticket->title, 40) }}</div>
                                <small class="text-muted">{{ $rating->ticket->category?->name ?? 'Kategori Umum' }}</small>
                            </td>
                            <td data-label="Rating">
                                <span class="badge bg-warning text-dark px-2 py-1 fw-bold rounded-pill">
                                    <i class="bi bi-star-fill text-dark me-1"></i> {{ $rating->rating }} / 5
                                </span>
                            </td>
                            <td data-label="Ulasan">
                                <div class="small text-secondary {{ empty($rating->review) ? 'fst-italic text-muted' : '' }}">
                                    {{ $rating->review ?: '(Tidak ada catatan tertulis)' }}
                                </div>
                            </td>
                            <td data-label="Waktu Dinilai">
                                <small class="text-muted d-block">{{ $rating->created_at->format('d/m/Y') }}</small>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $rating->created_at->format('H:i') }} WITA</small>
                            </td>
                            <td class="text-end pe-3" data-label="Aksi">
                                <a href="{{ route('padal.tickets.show', $rating->ticket_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-star text-muted opacity-50 display-6 d-block mb-2"></i>
                                    <p class="mb-0">Belum ada penilaian yang diterima untuk tiket penugasan Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ratings->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $ratings->links() }}
        </div>
    @endif
</div>
@endsection
