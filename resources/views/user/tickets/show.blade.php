@extends('layouts.app')

@section('title', 'Detail Laporan #' . $ticket->ticket_number)
@section('page_title', 'Detail Laporan #' . $ticket->ticket_number)
@section('page_subtitle', 'Pantau perkembangan dan penanganan permasalahan IT')

@section('styles')
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.35rem;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        font-size: 2rem;
        color: #cbd5e1;
        cursor: pointer;
        transition: color 0.2s;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #f59e0b;
    }
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0.75rem;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-badge {
        position: absolute;
        left: -2rem;
        top: 0.15rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('user.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan
    </a>
</div>

<!-- FORM RATING JIKA STATUS SELESAI DAN BELUM DINILAI -->
@if($ticket->status === \App\Models\Ticket::STATUS_SELESAI && !$ticket->rating)
    <div class="card border-warning shadow-sm mb-4">
        <div class="card-header bg-warning bg-opacity-10 py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i> Berikan Penilaian Pelayanan IT</h6>
        </div>
        <div class="card-body p-4">
            <p class="text-secondary small mb-3">
                Laporan penanganan gangguan Anda telah dinyatakan <strong>SELESAI</strong> oleh Perwira Pengendali (Padal).
                Mohon luangkan waktu sejenak untuk memberikan penilaian kepuasan dan ulasan Anda terhadap kualitas layanan yang diberikan.
                <strong>Catatan:</strong> Setelah penilaian ini dikirim, Anda dapat membuat laporan gangguan baru.
            </p>

            <form action="{{ route('user.tickets.rate', $ticket->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Beri Bintang Kepuasan Pelayanan (1 - 5) <span class="text-danger">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" {{ old('rating') == 5 ? 'checked' : '' }} required>
                        <label for="star5" title="Sangat Baik (5 Bintang)"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star4" name="rating" value="4" {{ old('rating') == 4 ? 'checked' : '' }}>
                        <label for="star4" title="Baik (4 Bintang)"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star3" name="rating" value="3" {{ old('rating') == 3 ? 'checked' : '' }}>
                        <label for="star3" title="Cukup (3 Bintang)"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star2" name="rating" value="2" {{ old('rating') == 2 ? 'checked' : '' }}>
                        <label for="star2" title="Kurang (2 Bintang)"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star1" name="rating" value="1" {{ old('rating') == 1 ? 'checked' : '' }}>
                        <label for="star1" title="Sangat Kurang (1 Bintang)"><i class="bi bi-star-fill"></i></label>
                    </div>
                    @error('rating')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="review" class="form-label fw-semibold small text-secondary">Ulasan / Masukan Pelayanan (Opsional)</label>
                    <textarea name="review" id="review" rows="3" class="form-control @error('review') is-invalid @enderror" 
                              placeholder="Tuliskan pengalaman atau saran Anda terhadap kecepatan dan respon petugas...">{{ old('review') }}</textarea>
                    @error('review')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                    <i class="bi bi-send-check me-1"></i> Kirim Penilaian Pelayanan
                </button>
            </form>
        </div>
    </div>
@endif

<!-- JIKA SUDAH DINILAI -->
@if($ticket->rating)
    <div class="card bg-light border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark"><i class="bi bi-patch-check-fill text-success me-2"></i> Penilaian Anda Terhadap Pelayanan Ini:</span>
                <span class="text-muted small">{{ $ticket->rating->created_at->format('d M Y, H:i') }}</span>
            </div>
            <div class="text-warning fs-5 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $ticket->rating->rating)
                        <i class="bi bi-star-fill"></i>
                    @else
                        <i class="bi bi-star text-muted opacity-50"></i>
                    @endif
                @endfor
                <span class="ms-2 fw-bold text-dark fs-6">{{ $ticket->rating->rating }} / 5 Bintang</span>
            </div>
            @if($ticket->rating->review)
                <p class="mb-0 text-secondary fst-italic">"{{ $ticket->rating->review }}"</p>
            @endif
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Laporan -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">Informasi Gangguan IT</h6>
                <span class="{{ $ticket->status_badge_class }} px-3 py-2 fs-6">{{ $ticket->status_label }}</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="badge bg-light text-dark border mb-1">{{ $ticket->category->name }}</span>
                    <h4 class="fw-bold text-dark">{{ $ticket->title }}</h4>
                </div>

                <div class="row g-3 p-3 bg-light rounded-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Nomor Tiket</small>
                        <strong class="font-monospace text-primary fs-6">{{ $ticket->ticket_number }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Lokasi Satker / Ruangan</small>
                        <strong class="text-dark">{{ $ticket->location }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Dilaporkan</small>
                        <span class="text-dark">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Selesai</small>
                        <span class="text-dark">{{ $ticket->completed_at ? $ticket->completed_at->format('d F Y, H:i') . ' WIB' : '-' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Deskripsi Permasalahan:</h6>
                    <div class="p-3 bg-white rounded border text-dark" style="white-space: pre-line;">{{ $ticket->description }}</div>
                </div>

                @if($ticket->attachment)
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary mb-2">Lampiran Berkas Laporan:</h6>
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Buka / Unduh Lampiran
                        </a>
                    </div>
                @endif

                <!-- Jika Ditolak -->
                @if($ticket->rejection_reason)
                    <div class="alert alert-danger mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i> Alasan Penolakan:</h6>
                        <p class="mb-0">{{ $ticket->rejection_reason }}</p>
                    </div>
                @endif

                <!-- Catatan Penyelesaian oleh Padal -->
                @if($ticket->resolution_notes)
                    <div class="card border-success bg-success bg-opacity-10 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-2"></i> Hasil Tindakan & Penyelesaian:</h6>
                            <p class="mb-2 text-dark" style="white-space: pre-line;">{{ $ticket->resolution_notes }}</p>
                            @if($ticket->resolution_attachment)
                                <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-image me-1"></i> Bukti Penyelesaian Pekerjaan
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Tim Penanggung Jawab & Riwayat Status -->
    <div class="col-lg-4">
        <!-- Tim Penanggung Jawab -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i> Tim Penanganan</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Perwira Pengendali (Padal):</small>
                    @if($ticket->padal)
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <div class="fw-bold small">{{ $ticket->padal->name }}</div>
                                <small class="text-muted">NRP: {{ $ticket->padal->nrp_nip ?? '-' }}</small>
                            </div>
                        </div>
                    @else
                        <span class="badge bg-secondary">Menunggu Penugasan</span>
                    @endif
                </div>

                <hr class="my-2">

                <div>
                    <small class="text-muted d-block mb-1">Anggota Tim Teknisi:</small>
                    @if($ticket->technicians->isNotEmpty())
                        <ul class="list-unstyled mb-0">
                            @foreach($ticket->technicians as $tech)
                                <li class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="bi bi-tools" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="small fw-semibold text-dark">{{ $tech->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <small class="text-muted fst-italic">Belum ada teknisi yang dipilih.</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Riwayat Jejak Status -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Riwayat Perkembangan</h6>
            </div>
            <div class="card-body p-3">
                <div class="timeline">
                    @forelse($ticket->histories as $history)
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ $history->created_at->format('d M Y, H:i') }}</small>
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $history->status_to)) }}</span>
                                <p class="small text-secondary mt-1 mb-0">{{ $history->notes }}</p>
                            </div>
                        </div>
                    @empty
                        <small class="text-muted">Belum ada riwayat perkembangan.</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
