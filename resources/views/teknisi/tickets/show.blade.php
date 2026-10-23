@extends('layouts.app')

@section('title', 'Rincian Tugas #' . $ticket->ticket_number)
@section('page_title', 'Rincian Penugasan #' . $ticket->ticket_number)
@section('page_subtitle', 'Informasi deskripsi kendala dan lokasi penanganan teknis (Akses Baca Saja)')

@section('styles')
<style>
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
        border: 2px solid #06b6d4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('teknisi.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penugasan
    </a>
    <span class="{{ $ticket->status_badge_class }} px-3 py-2 fs-6">{{ $ticket->status_label }}</span>
</div>

<div class="alert alert-secondary d-flex align-items-center py-2 px-3 mb-4 small" role="alert">
    <i class="bi bi-shield-lock-fill text-muted me-2 fs-5"></i>
    <div>
        <strong>Akses Read-Only:</strong> Sebagai Teknisi, Anda memiliki akses baca untuk memahami detail permasalahan dan lokasi tindakan. Pelaporan penyelesaian dan pengelolaan tiket berada di bawah kendali Padal.
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Detail Tiket Permasalahan -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">Data Pengaduan Permasalahan IT</h6>
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
                        <small class="text-muted d-block">Lokasi Ruangan / Satker Tindakan</small>
                        <strong class="text-dark fs-6 text-primary"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $ticket->location }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Dilaporkan</small>
                        <span class="text-dark">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Mulai Pengerjaan</small>
                        <span class="text-dark">{{ $ticket->started_at ? $ticket->started_at->format('d F Y, H:i') . ' WIB' : '-' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Deskripsi Rinci Permasalahan:</h6>
                    <div class="p-3 bg-white rounded border text-dark" style="white-space: pre-line;">{{ $ticket->description }}</div>
                </div>

                @if($ticket->attachment)
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary mb-2">Lampiran Berkas Pelapor:</h6>
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Buka Lampiran Masalah
                        </a>
                    </div>
                @endif

                <!-- Catatan Penyelesaian oleh Padal -->
                @if($ticket->resolution_notes)
                    <div class="card border-success bg-success bg-opacity-10 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-2"></i> Hasil Tindakan & Penyelesaian dari Padal:</h6>
                            <p class="mb-2 text-dark" style="white-space: pre-line;">{{ $ticket->resolution_notes }}</p>
                            @if($ticket->resolution_attachment)
                                <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-image me-1"></i> Bukti Penyelesaian Pekerjaan
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Rating Pelayanan -->
                @if($ticket->rating)
                    <div class="card border-warning bg-warning bg-opacity-10">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-star-fill text-warning me-1"></i> Penilaian Pelayanan dari Pelapor:</h6>
                            <div class="text-warning fs-5 my-1">
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
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pelapor, Padal, Rekan Tim & Jejak Status -->
    <div class="col-lg-4">
        <!-- Pelapor -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-fill text-primary me-2"></i> Kontak Pelapor</h6>
            </div>
            <div class="card-body p-3">
                <div class="fw-bold text-dark">{{ $ticket->user->name }}</div>
                <small class="text-muted d-block">Satker: {{ $ticket->user->satuan_kerja ?? '-' }}</small>
                <small class="text-muted d-block">No. HP/WA: {{ $ticket->user->phone_number ?? '-' }}</small>
            </div>
        </div>

        <!-- Padal Penanggung Jawab -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-warning me-2"></i> Padal Penanggung Jawab</h6>
            </div>
            <div class="card-body p-3">
                @if($ticket->padal)
                    <div class="fw-bold text-dark">{{ $ticket->padal->name }}</div>
                    <small class="text-muted d-block">NRP: {{ $ticket->padal->nrp_nip ?? '-' }}</small>
                    <small class="text-muted d-block">No. HP: {{ $ticket->padal->phone_number ?? '-' }}</small>
                @else
                    <small class="text-muted">Belum ditetapkan</small>
                @endif
            </div>
        </div>

        <!-- Rekan Satu Tim Teknisi -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-info me-2"></i> Tim Teknisi Bertugas</h6>
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0">
                    @foreach($ticket->technicians as $tech)
                        <li class="d-flex align-items-center gap-2 mb-2 p-2 rounded {{ $tech->id === Auth::id() ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' : 'bg-light' }}">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-tools" style="font-size: 0.75rem;"></i>
                            </div>
                            <div>
                                <div class="small fw-bold text-dark">
                                    {{ $tech->name }}
                                    @if($tech->id === Auth::id())
                                        <span class="badge bg-primary text-white" style="font-size: 0.65rem;">Anda</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $tech->phone_number ?? '-' }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Log Perkembangan Status -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Riwayat Status</h6>
            </div>
            <div class="card-body p-3">
                <div class="timeline">
                    @forelse($ticket->histories as $history)
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <i class="bi bi-circle-fill text-info" style="font-size: 0.5rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ $history->created_at->format('d M Y, H:i') }}</small>
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $history->status_to)) }}</span>
                                <p class="small text-secondary mt-1 mb-0">{{ $history->notes }}</p>
                            </div>
                        </div>
                    @empty
                        <small class="text-muted">Belum ada riwayat.</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
