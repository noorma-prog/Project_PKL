@extends('layouts.app')

@section('title', 'Kelola Laporan #' . $ticket->ticket_number)
@section('page_title', 'Kelola Laporan #' . $ticket->ticket_number)
@section('page_subtitle', 'Verifikasi, penugasan Padal, dan pemantauan penanganan laporan')

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
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan
    </a>
    <span class="{{ $ticket->status_badge_class }} px-3 py-2 fs-6">{{ $ticket->status_label }}</span>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Detail Laporan & Form Verifikasi -->
    <div class="col-lg-8">
        <!-- Aksi Verifikasi & Penugasan Awal (Jika Menunggu Verifikasi atau Ditolak Padal) -->
        @if(in_array($ticket->status, [\App\Models\Ticket::STATUS_MENUNGGU_VERIFIKASI, \App\Models\Ticket::STATUS_DITOLAK_PADAL]))
            <div class="card border-primary shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2"></i> Tindakan Administrator: Verifikasi & Penugasan Padal</h6>
                </div>
                <div class="card-body p-4">
                    @if($ticket->status === \App\Models\Ticket::STATUS_DITOLAK_PADAL)
                        <div class="alert alert-warning mb-3">
                            <strong>Perhatian:</strong> Penugasan sebelumnya ditolak oleh Padal. Alasan: <em>"{{ $ticket->rejection_reason }}"</em>. Silakan pilih unit Padal lain untuk menugaskan kembali.
                        </div>
                    @endif

                    <form action="{{ route('admin.tickets.verify-assign', $ticket->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="padal_id" class="form-label fw-bold text-dark">Pilih Unit Padal (Perwira Pengendali) <span class="text-danger">*</span></label>
                            <select name="padal_id" id="padal_id" class="form-select @error('padal_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Unit Padal yang Ditugaskan --</option>
                                @foreach($padals as $padal)
                                    <option value="{{ $padal->id }}" {{ (old('padal_id') == $padal->id || $ticket->padal_id == $padal->id) ? 'selected' : '' }}>
                                        {{ $padal->nama_padal }} (Ketua: {{ $padal->leader ? $padal->leader->name : '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('padal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label fw-semibold small text-secondary">Catatan / Instruksi Administrator (Opsional)</label>
                            <textarea name="admin_notes" id="admin_notes" rows="2" class="form-control" placeholder="Tambahkan catatan arahan penanganan untuk Padal...">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Laporan & Tugaskan ke Padal
                        </button>
                    </form>

                    @if($ticket->status === \App\Models\Ticket::STATUS_MENUNGGU_VERIFIKASI)
                        <hr class="my-4">
                        <div class="p-3 bg-light rounded border border-danger border-opacity-50">
                            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-octagon-fill me-1"></i> Tolak Laporan Ini</h6>
                            <p class="small text-muted mb-2">Jika laporan tidak valid atau tidak memenuhi kriteria penanganan TIK POLDA, berikan alasan resmi untuk pelapor.</p>
                            
                            <form action="{{ route('admin.tickets.reject', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="rejection_reason" rows="2" class="form-control @error('rejection_reason') is-invalid @enderror" 
                                              placeholder="Tuliskan alasan penolakan secara jelas untuk pelapor..." required>{{ old('rejection_reason') }}</textarea>
                                    @error('rejection_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Apakah Anda yakin ingin menolak laporan ini?')">
                                    <i class="bi bi-x-circle me-1"></i> Tolak Laporan Ini
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @elseif(in_array($ticket->status, [\App\Models\Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL, \App\Models\Ticket::STATUS_DALAM_PENANGANAN]))
            <!-- Fitur Ubah Penugasan (Use Case: Menugaskan Padal -> Ubah Penugasan) -->
            <div class="card border-warning shadow-sm mb-4">
                <div class="card-header bg-warning bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold text-dark"><i class="bi bi-arrow-repeat me-1"></i> Opsi Administrator: Ubah Penugasan Padal</span>
                    <button class="btn btn-xs btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReassign">
                        Ubah Penugasan
                    </button>
                </div>
                <div class="collapse" id="collapseReassign">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.tickets.reassign', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Alihkan Penugasan ke Unit Padal Lain:</label>
                                <select name="padal_id" class="form-select form-select-sm" required>
                                    @foreach($padals as $padal)
                                        <option value="{{ $padal->id }}" {{ $ticket->padal_id == $padal->id ? 'selected' : '' }}>
                                            {{ $padal->nama_padal }} (Ketua: {{ $padal->leader ? $padal->leader->name : '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-secondary">Catatan Perubahan Penugasan:</label>
                                <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Alasan pengalihan penugasan...">
                            </div>
                            <button type="submit" class="btn btn-sm btn-warning fw-bold">
                                <i class="bi bi-arrow-repeat me-1"></i> Simpan Perubahan Penugasan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Detail Laporan -->
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
                        <small class="text-muted d-block">Waktu Dilaporkan</small>
                        <span class="text-dark">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Status Penyelesaian</small>
                        <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Deskripsi Gangguan:</h6>
                    <div class="p-3 bg-white rounded border text-dark" style="white-space: pre-line;">{{ $ticket->description }}</div>
                </div>

                @if($ticket->attachment)
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary mb-2">Lampiran Berkas Permasalahan:</h6>
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Buka / Unduh Berkas Pelapor
                        </a>
                    </div>
                @endif

                @if($ticket->rejection_reason)
                    <div class="alert alert-danger mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i> Alasan Penolakan:</h6>
                        <p class="mb-0">{{ $ticket->rejection_reason }}</p>
                    </div>
                @endif

                <!-- Hasil Penyelesaian oleh Padal -->
                @if($ticket->resolution_notes)
                    <div class="card border-success bg-success bg-opacity-10 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-2"></i> Dokumentasi & Hasil Tindakan Padal:</h6>
                            <p class="mb-2 text-dark" style="white-space: pre-line;">{{ $ticket->resolution_notes }}</p>
                            @if($ticket->resolution_attachment)
                                <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-image me-1"></i> Bukti Hasil Pekerjaan
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Rating & Ulasan Pelapor -->
                @if($ticket->rating)
                    <div class="card border-warning bg-warning bg-opacity-10">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-star-fill text-warning me-1"></i> Penilaian dari Pelapor:</h6>
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

    <!-- Kolom Kanan: Pelapor, Tim & Riwayat Status -->
    <div class="col-lg-4">
        <!-- Informasi Pelapor -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-fill text-primary me-2"></i> Data Pelapor</h6>
            </div>
            <div class="card-body p-3">
                <div class="fw-bold text-dark">{{ $ticket->user->name }}</div>
                <small class="text-muted d-block">NRP/NIP: {{ $ticket->user->nrp_nip ?? '-' }}</small>
                <small class="text-muted d-block">Satker: {{ $ticket->user->satuan_kerja ?? '-' }}</small>
                <small class="text-muted d-block">Alamat: {{ $ticket->user->alamat ?? '-' }}</small>
                <small class="text-muted d-block">WhatsApp: {{ $ticket->user->phone_number ?? '-' }}</small>
                <small class="text-muted d-block">Email: {{ $ticket->user->email }}</small>
            </div>
        </div>

        <!-- Tim Penanganan Padal -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-shaded text-primary me-2"></i> Unit Padal Penanggung Jawab</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Unit Padal:</small>
                    @if($ticket->padal)
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-shield-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold small">{{ $ticket->padal->nama_padal }}</div>
                                <small class="text-muted">Ketua: {{ $ticket->padal->leader ? $ticket->padal->leader->name : '-' }}</small>
                            </div>
                        </div>
                    @else
                        <span class="badge bg-secondary">Belum Ditugaskan</span>
                    @endif
                </div>

                @if($ticket->padal && $ticket->padal->members->isNotEmpty())
                    <hr class="my-2">
                    <div>
                        <small class="text-muted d-block mb-1">Anggota Tim Teknisi:</small>
                        <ul class="list-unstyled mb-0">
                            @foreach($ticket->padal->members as $member)
                                <li class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                        <i class="bi bi-tools" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <div>
                                        <span class="small fw-semibold text-dark">{{ $member->name }}</span><br>
                                        <small class="text-muted">{{ $member->nrp_nip ?? 'Teknisi' }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($ticket->technicians->isNotEmpty())
                    <hr class="my-2">
                    <div>
                        <small class="text-muted d-block mb-1">Teknisi yang Ditugaskan pada Tiket Ini:</small>
                        <ul class="list-unstyled mb-0">
                            @foreach($ticket->technicians as $technician)
                                <li class="small fw-semibold text-dark">
                                    <i class="bi bi-tools text-info me-1"></i>{{ $technician->name }}
                                    <span class="text-muted">({{ $technician->nrp_nip ?? 'Teknisi' }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($ticket->diperiksaOleh || $ticket->ditugaskanOleh)
                    <hr class="my-2">
                    <div class="small text-muted">
                        @if($ticket->diperiksaOleh)
                            <div><i class="bi bi-check2-square me-1"></i> Diperiksa oleh: <strong>{{ $ticket->diperiksaOleh->name }}</strong></div>
                        @endif
                        @if($ticket->ditugaskanOleh)
                            <div><i class="bi bi-send-check me-1"></i> Ditugaskan oleh: <strong>{{ $ticket->ditugaskanOleh->name }}</strong></div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Jejak Status -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Log Jejak Audit Status</h6>
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
