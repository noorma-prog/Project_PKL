@extends('layouts.app')

@section('title', 'Detail Laporan #' . $ticket->ticket_number)
@section('page_title', 'Detail Laporan')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
@php($selectedTechnicianIds = old('technician_ids', $ticket->technicians->modelKeys()))
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <span class="ticket-id fs-6">{{ $ticket->ticket_number }}</span>
        <span class="{{ $ticket->status_badge_class }} ms-2">{{ $ticket->status_label }}</span>
    </div>
    <a href="{{ route('padal.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Detail Laporan & Form Penanganan (64%) -->
    <div class="col-lg-8">
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

                @if($ticket->admin_notes)
                    <div class="alert alert-info mb-4">
                        <strong class="d-block mb-1"><i class="bi bi-info-circle me-1"></i> Catatan Administrator:</strong>
                        <span>{{ $ticket->admin_notes }}</span>
                    </div>
                @endif

                @if($ticket->attachment)
                    <div class="mb-4">
                        <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-paperclip me-1"></i> Lihat Lampiran: {{ basename($ticket->attachment) }}
                        </a>
                    </div>
                @endif

                @if($ticket->rejection_reason)
                    <div class="alert alert-danger mb-4">
                        <strong class="d-block mb-1"><i class="bi bi-x-circle me-1"></i> Alasan Penolakan:</strong>
                        <span>{{ $ticket->rejection_reason }}</span>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    Data Pelapor: <strong>{{ $ticket->user?->name }}</strong> / {{ $ticket->user?->satuan_kerja ?? 'POLDA' }} / Kontak sesuai data akun
                </small>
            </div>
        </div>

        <!-- JIKA DALAM PENANGANAN: DOKUMENTASI PENYELESAIAN & PILIH TEKNISI (Halaman 19) -->
        @if($ticket->status === \App\Models\Ticket::STATUS_DALAM_PENANGANAN)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Dokumentasi Penyelesaian</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('padal.tickets.complete', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="resolution_notes" class="form-label">Dokumentasi & Tindakan Penyelesaian <span class="text-danger">*</span></label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="4" class="form-control @error('resolution_notes') is-invalid @enderror" 
                                      placeholder="Crimping ulang kabel LAN dan pengujian konektivitas. Aplikasi internal kembali dapat diakses." required>{{ old('resolution_notes') }}</textarea>
                            <div class="form-text">Catatan minimal 10 karakter.</div>
                            @error('resolution_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="resolution_attachment" class="form-label">Bukti Penyelesaian (Opsional)</label>
                            <input type="file" name="resolution_attachment" id="resolution_attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Pilih berkas • JPG, PNG, PDF, maks. 5 MB</div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4" onclick="return confirm('Apakah pekerjaan penanganan benar-benar telah selesai dan diuji?')">
                            Tandai Laporan Selesai
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Perbarui Anggota Tim Teknisi (Submit Terpisah Sesuai Kontrak) -->
                    <h6 class="fw-bold text-dark mb-2">Perbarui Anggota Tim Teknisi</h6>
                    <form action="{{ route('padal.tickets.technicians', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="p-3 bg-light rounded border mb-3">
                            @forelse($availableTechnicians as $tech)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="technician_ids[]" value="{{ $tech->id }}" id="tech{{ $tech->id }}"
                                           {{ is_array($selectedTechnicianIds) && in_array($tech->id, $selectedTechnicianIds) ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark fw-semibold" for="tech{{ $tech->id }}">
                                        {{ $tech->name }} / {{ $tech->satuan_kerja ?? 'Bid TIK' }}
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted">Belum ada anggota Teknisi aktif di unit Anda. <a href="{{ route('padal.members.index') }}">Kelola anggota</a>.</small>
                            @endforelse
                        </div>
                        @error('technician_ids')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-outline-secondary">
                            Simpan Teknisi
                        </button>
                        <div class="form-text mt-2">Pilih teknisi dari anggota aktif unit Anda. Pastikan hasil penanganan telah diuji sebelum menandai selesai.</div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Hasil Tindakan & Penyelesaian yang Sudah Disimpan -->
        @if($ticket->resolution_notes && $ticket->status !== \App\Models\Ticket::STATUS_DALAM_PENANGANAN)
            <div class="card mb-4" style="border-color: #A7F3D0;">
                <div class="card-header" style="background: #F0FDF4;">
                    <span class="fw-bold text-success"><i class="bi bi-check2-circle me-1"></i> Hasil Tindakan & Penyelesaian:</span>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3 text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $ticket->resolution_notes }}</p>
                    @if($ticket->resolution_attachment)
                        <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-check me-1"></i> Bukti Penyelesaian Pekerjaan
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Penilaian dari Pelapor -->
        @if($ticket->rating)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold text-dark">Penilaian Pelayanan dari Pelapor</span>
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
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Kolom Kanan: Konfirmasi Penugasan & Riwayat (36%) -->
    <div class="col-lg-4">
        <!-- Panel Konfirmasi Penugasan (Halaman 18 & 33) -->
        @if($ticket->status === \App\Models\Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Konfirmasi Penugasan</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('padal.tickets.accept', $ticket->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="padal_notes" class="form-label">Catatan Konfirmasi (Opsional)</label>
                            <textarea name="padal_notes" id="padal_notes" rows="2" class="form-control" placeholder="Tambahkan catatan...">{{ old('padal_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Terima Penugasan
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Tolak Penugasan (Halaman 33) -->
                    <div>
                        <div class="alert alert-warning small mb-3">
                            <strong>Berikan alasan yang jelas</strong><br>
                            Alasan penolakan akan dicatat di riwayat tiket. Penolakan Padal mengembalikan penugasan kepada Admin.
                        </div>

                        <form action="{{ route('padal.tickets.reject', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" class="form-control @error('rejection_reason') is-invalid @enderror" 
                                          placeholder="Jelaskan alasan penolakan..." required>{{ old('rejection_reason') }}</textarea>
                                <div class="form-text">Minimal 5 karakter, maksimal 1.000 karakter.</div>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menolak penugasan ini?')">
                                Tolak Penugasan
                            </button>
                            <div class="form-text text-center mt-2">Alasan wajib diisi ketika menolak penugasan.</div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Anggota Teknisi Bertugas -->
        @if($ticket->technicians->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Teknisi Bertugas</span>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0">
                        @foreach($ticket->technicians as $tech)
                            <li class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded">
                                <i class="bi bi-tools text-primary"></i>
                                <span class="small fw-semibold text-dark">{{ $tech->name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Riwayat Jejak Status -->
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
