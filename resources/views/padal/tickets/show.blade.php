@extends('layouts.app')

@section('title', 'Penanganan Tiket #' . $ticket->ticket_number)
@section('page_title', 'Penanganan Tiket #' . $ticket->ticket_number)
@section('page_subtitle', 'Proses konfirmasi penugasan, pemilihan anggota teknisi, dan penyelesaian laporan')

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
        border: 2px solid #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('padal.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penugasan
    </a>
    <span class="{{ $ticket->status_badge_class }} px-3 py-2 fs-6">{{ $ticket->status_label }}</span>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Formulir Aksi Padal & Detail Tiket -->
    <div class="col-lg-8">

        <!-- AKSI 1: JIKA MENUNGGU KONFIRMASI PADAL -->
        @if($ticket->status === \App\Models\Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)
            <div class="card border-warning shadow-sm mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2"></i> Konfirmasi Penugasan & Pemilihan Anggota Teknisi</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-secondary small mb-3">
                        Laporan ini telah ditugaskan kepada Anda oleh Administrator TIK. Anda dapat <strong>menerima penugasan</strong> dengan memilih anggota teknisi yang bertugas, atau <strong>menolak</strong> jika terdapat kendala operasional dengan alasan yang jelas.
                    </p>

                    <form action="{{ route('padal.tickets.accept', $ticket->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Pilih Anggota Tim Teknisi yang Bertugas <span class="text-danger">*</span></label>
                            <div class="p-3 bg-light rounded border">
                                @forelse($availableTechnicians as $tech)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="technician_ids[]" value="{{ $tech->id }}" id="tech{{ $tech->id }}"
                                               {{ (is_array(old('technician_ids')) && in_array($tech->id, old('technician_ids'))) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fw-semibold" for="tech{{ $tech->id }}">
                                            {{ $tech->name }} &mdash; <small class="text-muted">{{ $tech->satuan_kerja ?? 'Subbid Tekinfo' }} (NRP/NIP: {{ $tech->nrp_nip ?? '-' }})</small>
                                        </label>
                                    </div>
                                @empty
                                    <small class="text-danger">Belum ada akun personil dengan role Teknisi yang aktif.</small>
                                @endforelse
                            </div>
                            @error('technician_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="padal_notes" class="form-label fw-semibold small text-secondary">Instruksi Awal untuk Tim Teknisi (Opsional)</label>
                            <textarea name="padal_notes" id="padal_notes" rows="2" class="form-control" placeholder="Tuliskan arahan peralatan yang harus disiapkan atau penanganan khusus...">{{ old('padal_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Terima Penugasan & Mulai Penanganan
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Opsi Tolak Penugasan -->
                    <div class="p-3 bg-light rounded border border-danger border-opacity-50">
                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-octagon me-1"></i> Opsi: Tolak Penugasan Laporan Ini</h6>
                        <p class="small text-muted mb-2">Jika Anda sedang melaksanakan dinas luar atau tidak dapat menangani tiket ini, masukkan alasan penolakan secara jelas agar dapat dialihkan oleh Administrator.</p>

                        <form action="{{ route('padal.tickets.reject', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="rejection_reason" rows="2" class="form-control @error('rejection_reason') is-invalid @enderror" 
                                          placeholder="Tuliskan alasan penolakan penugasan..." required>{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Apakah Anda yakin ingin menolak penugasan ini?')">
                                <i class="bi bi-x-circle me-1"></i> Tolak Penugasan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- AKSI 2: JIKA DALAM PENANGANAN -->
        @if($ticket->status === \App\Models\Ticket::STATUS_DALAM_PENANGANAN)
            <div class="card border-success shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-check2-circle me-2"></i> Formulir Penyelesaian Pekerjaan</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-secondary small mb-3">
                        Laporan saat ini sedang dalam proses penanganan oleh tim teknisi Anda. Jika kendala teknis telah teratasi dan teruji dengan baik, silakan dokumentasikan tindakan penanganan dan tandai tiket sebagai <strong>SELESAI</strong>.
                    </p>

                    <form action="{{ route('padal.tickets.complete', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="resolution_notes" class="form-label fw-bold text-dark">Dokumentasi & Tindakan Penyelesaian <span class="text-danger">*</span></label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="4" class="form-control @error('resolution_notes') is-invalid @enderror" 
                                      placeholder="Jelaskan tindakan teknis yang telah dilakukan (misal: crimping ulang kabel LAN, instalasi driver printer, reset modem internet, penggantian power supply)..." required>{{ old('resolution_notes') }}</textarea>
                            <small class="text-muted">Catatan tindakan minimal 10 karakter.</small>
                            @error('resolution_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="resolution_attachment" class="form-label fw-semibold small text-secondary">Bukti Foto / Berita Acara Penyelesaian (Opsional)</label>
                            <input type="file" name="resolution_attachment" id="resolution_attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF (Maksimal 5 MB).</small>
                        </div>

                        <button type="submit" class="btn btn-success fw-bold px-4" onclick="return confirm('Apakah pekerjaan penanganan benar-benar telah selesai dan diuji?')">
                            <i class="bi bi-check-all me-1"></i> Tandai Laporan Selesai
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Opsi Perbarui Anggota Teknisi -->
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-gear me-1"></i> Perbarui Anggota Tim Teknisi:</h6>
                    <form action="{{ route('padal.tickets.technicians', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="p-3 bg-light rounded border mb-2">
                            @foreach($availableTechnicians as $tech)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="technician_ids[]" value="{{ $tech->id }}" id="techUpdate{{ $tech->id }}"
                                           {{ $ticket->technicians->contains('id', $tech->id) ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark" for="techUpdate{{ $tech->id }}">
                                        {{ $tech->name }} ({{ $tech->nrp_nip ?? '-' }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Simpan Perubahan Anggota
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Card Informasi Laporan -->
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
                        <small class="text-muted d-block">Lokasi Satker / Ruangan</small>
                        <strong class="text-dark">{{ $ticket->location }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Masuk</small>
                        <span class="text-dark">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Waktu Ditugaskan</small>
                        <span class="text-dark">{{ $ticket->assigned_at ? $ticket->assigned_at->format('d F Y, H:i') . ' WIB' : '-' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Deskripsi Gangguan dari Pelapor:</h6>
                    <div class="p-3 bg-white rounded border text-dark" style="white-space: pre-line;">{{ $ticket->description }}</div>
                </div>

                @if($ticket->admin_notes)
                    <div class="alert alert-info mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Catatan dari Administrator:</h6>
                        <p class="mb-0">{{ $ticket->admin_notes }}</p>
                    </div>
                @endif

                @if($ticket->attachment)
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary mb-2">Lampiran Berkas Pelapor:</h6>
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Lihat Berkas Lampiran
                        </a>
                    </div>
                @endif

                <!-- Dokumentasi Penyelesaian -->
                @if($ticket->resolution_notes)
                    <div class="card border-success bg-success bg-opacity-10 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-2"></i> Hasil Tindakan Penyelesaian Anda:</h6>
                            <p class="mb-2 text-dark" style="white-space: pre-line;">{{ $ticket->resolution_notes }}</p>
                            @if($ticket->resolution_attachment)
                                <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-image me-1"></i> Bukti Penyelesaian Pekerjaan
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Penilaian Pelayanan dari User -->
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

    <!-- Kolom Kanan: Pelapor, Tim Teknisi & Log Jejak Audit -->
    <div class="col-lg-4">
        <!-- Data Pelapor -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-fill text-primary me-2"></i> Data Pelapor</h6>
            </div>
            <div class="card-body p-3">
                <div class="fw-bold text-dark">{{ $ticket->user->name }}</div>
                <small class="text-muted d-block">NRP/NIP: {{ $ticket->user->nrp_nip ?? '-' }}</small>
                <small class="text-muted d-block">Satker: {{ $ticket->user->satuan_kerja ?? '-' }}</small>
                <small class="text-muted d-block">WhatsApp: {{ $ticket->user->phone_number ?? '-' }}</small>
                <small class="text-muted d-block">Email: {{ $ticket->user->email }}</small>
            </div>
        </div>

        <!-- Tim Teknisi yang Dipilih -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-info me-2"></i> Anggota Tim Teknisi</h6>
            </div>
            <div class="card-body p-3">
                @if($ticket->technicians->isNotEmpty())
                    <ul class="list-unstyled mb-0">
                        @foreach($ticket->technicians as $tech)
                            <li class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-tools" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <div class="small fw-bold text-dark">{{ $tech->name }}</div>
                                    <small class="text-muted">{{ $tech->phone_number ?? '-' }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <small class="text-muted fst-italic">Belum ada teknisi yang dipilih.</small>
                @endif
            </div>
        </div>

        <!-- Log Jejak Status -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Jejak Perkembangan</h6>
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
                        <small class="text-muted">Belum ada jejak audit.</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
