@extends('layouts.app')

@section('title', 'Penilaian Pelayanan')
@section('page_title', 'Penilaian Pelayanan dari Pelapor')
@section('page_subtitle', 'Indeks kepuasan pengguna dan evaluasi hasil penanganan laporan IT Anda')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-4 text-center">
            <span class="text-muted small fw-semibold">Rata-rata Rating Kinerja Anda</span>
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
            <small class="text-muted">Total {{ $totalRatings }} penilaian diterima dari pelapor</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-4 h-100 d-flex flex-column justify-content-center">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary me-2"></i> Mengenai Penilaian Pelayanan LayanIT</h6>
            <p class="text-secondary small mb-0">
                Setelah tiket pekerjaan diselesaikan oleh Padal, pelapor (User) diberikan formulir penilaian bintang (1 - 5) beserta ulasan tertulis.
                Hasil evaluasi ini digunakan sebagai indikator kinerja pelayanan IT dan akuntabilitas tim helpdesk di lingkungan POLDA.
            </p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-star-half text-warning me-2"></i> Riwayat Ulasan Pengguna Terhadap Penanganan Anda</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">No. Tiket</th>
                        <th>Pelapor & Satker</th>
                        <th>Judul Permasalahan</th>
                        <th>Nilai Bintang</th>
                        <th>Ulasan / Masukan Pelapor</th>
                        <th>Waktu Dinilai</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">{{ $rating->ticket->ticket_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $rating->user->name }}</div>
                                <small class="text-muted">{{ $rating->user->satuan_kerja }}</small>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ Str::limit($rating->ticket->title, 40) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="bi bi-star-fill text-dark me-1"></i> {{ $rating->rating }} / 5
                                </span>
                            </td>
                            <td>
                                <small class="text-secondary fst-italic">
                                    {{ $rating->review ?: '(Tidak ada komentar tertulis)' }}
                                </small>
                            </td>
                            <td><small class="text-muted">{{ $rating->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('padal.tickets.show', $rating->ticket_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Belum ada laporan selesai yang dinilai oleh pelapor.
                            </td>
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
