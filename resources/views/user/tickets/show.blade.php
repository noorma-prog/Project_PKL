@extends('layouts.app')

@section('title', 'Detail Laporan #' . $ticket->ticket_number)
@section('page_title', 'Detail Laporan')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <span class="ticket-id fs-6">{{ $ticket->ticket_number }}</span>
        <span class="{{ $ticket->status_badge_class }} ms-2">{{ $ticket->status_label }}</span>
    </div>
    <a href="{{ route('user.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Kiri (64%) -->
    <div class="col-lg-8">
        <!-- Informasi Gangguan Utama -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-dark mb-3">{{ $ticket->title }}</h4>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Kategori Gangguan IT</small>
                        <strong class="text-dark">{{ $ticket->category?->name ?? 'Gangguan Umum' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Lokasi / Satker</small>
                        <strong class="text-dark">{{ $ticket->location }}</strong>
                    </div>
                    @if($ticket->event_date)
                        <div class="col-sm-12">
                            <small class="text-muted d-block">Jadwal Kegiatan</small>
                            <span class="text-dark">
                                {{ $ticket->event_date->locale('id')->translatedFormat('d M Y') }}
                                @if($ticket->event_start_time && $ticket->event_end_time)
                                    • {{ substr($ticket->event_start_time, 0, 5) }}-{{ substr($ticket->event_end_time, 0, 5) }} WITA
                                @endif
                                <span class="badge bg-light text-dark ms-2">{{ $ticket->schedule_status }}</span>
                            </span>
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Deskripsi Permasalahan</h6>
                    <div class="p-3 bg-light rounded text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $ticket->description }}</div>
                </div>

                @if($ticket->attachment)
                    <div class="mb-4">
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Lihat Lampiran: {{ basename($ticket->attachment) }}
                        </a>
                    </div>
                @endif

                @if($ticket->rejection_reason)
                    <div class="alert alert-danger mb-4">
                        <strong class="d-block mb-1"><i class="bi bi-x-circle me-1"></i> Laporan Ditolak</strong>
                        <span>{{ $ticket->rejection_reason }}</span>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    Data Pelapor: <strong>{{ $ticket->user?->name }}</strong> / {{ $ticket->user?->satuan_kerja ?? 'POLDA' }} / Kontak sesuai profil akun
                </small>
            </div>
        </div>

        <!-- FORM RATING JIKA STATUS SELESAI DAN BELUM DINILAI -->
        @if($ticket->status === \App\Models\Ticket::STATUS_SELESAI && !$ticket->rating)
            <div class="card mb-4" style="border-color: #FDE68A;">
                <div class="card-header" style="background: #FFFBEB;">
                    <span class="fw-bold text-dark"><i class="bi bi-star-fill text-warning me-1"></i> Penilaian Pelayanan</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Nilai pelayanan setelah laporan selesai.</p>

                    <form action="{{ route('user.tickets.rate', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tingkat Kepuasan (1 - 5 Bintang) <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 align-items-center flex-wrap my-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-bold text-dark" for="rating{{ $i }}">
                                            {{ $i }} <i class="bi bi-star-fill text-warning"></i>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="review" class="form-label">Ulasan (Opsional)</label>
                            <textarea name="review" id="review" rows="3" class="form-control @error('review') is-invalid @enderror" 
                                      placeholder="Ceritakan pengalaman Anda...">{{ old('review') }}</textarea>
                            @error('review')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary px-4">
                            Kirim Penilaian
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- HASIL TINDAKAN & PENYELESAIAN (Jika sudah selesai) -->
        @if($ticket->resolution_notes)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold text-dark">Hasil Tindakan & Penyelesaian</span>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3 text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $ticket->resolution_notes }}</p>
                    @if($ticket->resolution_attachment)
                        <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-check me-1"></i> Lihat Bukti Penyelesaian
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- PENILAIAN TERSIMPAN -->
        @if($ticket->rating)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold text-dark">Penilaian Pelayanan</span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold fs-5 text-dark">{{ $ticket->rating->rating }} / 5</span>
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $ticket->rating->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($ticket->rating->review)
                        <p class="text-muted mb-0 fst-italic">"{{ $ticket->rating->review }}"</p>
                    @else
                        <small class="text-muted">(Tidak ada ulasan tertulis)</small>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Kolom Kanan (36%) -->
    <div class="col-lg-4">
        <!-- Tim Penanganan -->
        <div class="card mb-4">
            <div class="card-header">
                <span class="fw-bold">Tim Penanganan</span>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <small class="text-muted d-block">Unit Padal</small>
                    <strong class="text-dark">{{ $ticket->padal?->nama_padal ?? 'Belum Ditugaskan' }}</strong>
                </div>

                @if($ticket->technicians->isNotEmpty())
                    <hr class="my-2">
                    <div>
                        <small class="text-muted d-block mb-1">Teknisi Bertugas</small>
                        <div class="small fw-semibold text-dark">
                            {{ $ticket->technicians->pluck('name')->join(' / ') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Perkembangan -->
        <div class="card mb-4">
            <div class="card-header">
                <span class="fw-bold">Riwayat Perkembangan</span>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-3">
                    @forelse($ticket->histories as $history)
                        <div class="border-start border-2 border-primary ps-3 py-1">
                            <div class="fw-bold text-dark small">{{ ucfirst(str_replace('_', ' ', $history->status_to)) }}</div>
                            <small class="text-muted d-block">{{ $history->created_at->format('d M Y, H:i') }} WITA</small>
                            @if($history->notes)
                                <p class="small text-secondary mb-0 mt-1">{{ $history->notes }}</p>
                            @endif
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
