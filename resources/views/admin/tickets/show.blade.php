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
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Laporan (64%) -->
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

                <!-- Hasil Penyelesaian oleh Padal -->
                @if($ticket->resolution_notes)
                    <div class="card mb-4" style="border-color: #A7F3D0;">
                        <div class="card-header" style="background: #F0FDF4;">
                            <span class="fw-bold text-success"><i class="bi bi-check2-circle me-1"></i> Hasil Tindakan & Penyelesaian:</span>
                        </div>
                        <div class="card-body p-3">
                            <p class="mb-2 text-dark" style="white-space: pre-line;">{{ $ticket->resolution_notes }}</p>
                            @if($ticket->resolution_attachment)
                                <a href="{{ asset('storage/' . $ticket->resolution_attachment) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-check me-1"></i> Bukti Penyelesaian
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Rating & Ulasan -->
                @if($ticket->rating)
                    <div class="card mb-4">
                        <div class="card-header">
                            <span class="fw-bold text-dark">Penilaian dari Pelapor</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
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
            <div class="card-footer bg-light">
                <small class="text-muted">
                    Data Pelapor: <strong>{{ $ticket->user?->name }}</strong> / {{ $ticket->user?->satuan_kerja ?? 'POLDA' }} / Kontak sesuai data akun
                </small>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Panel Aksi & Tim (36%) -->
    <div class="col-lg-4">
        <!-- Panel Verifikasi & Penugasan (Jika Menunggu Verifikasi atau Ditolak Padal) -->
        @if(in_array($ticket->status, [\App\Models\Ticket::STATUS_MENUNGGU_VERIFIKASI, \App\Models\Ticket::STATUS_DITOLAK_PADAL]))
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Verifikasi & Penugasan</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.tickets.verify-assign', $ticket->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="padal_id" class="form-label">Unit Padal <span class="text-danger">*</span></label>
                            <select name="padal_id" id="padal_id" class="form-select @error('padal_id') is-invalid @enderror" required>
                                <option value="">Pilih unit aktif</option>
                                @foreach($padals as $padal)
                                    <option value="{{ $padal->id }}" {{ (old('padal_id') == $padal->id || $ticket->padal_id == $padal->id) ? 'selected' : '' }}>
                                        {{ $padal->nama_padal }}
                                    </option>
                                @endforeach
                            </select>
                            @error('padal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Catatan Admin (Opsional)</label>
                            <textarea name="admin_notes" id="admin_notes" rows="2" class="form-control" placeholder="Tambahkan catatan...">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Verifikasi & Tugaskan
                        </button>
                    </form>

                    @if($ticket->status === \App\Models\Ticket::STATUS_MENUNGGU_VERIFIKASI)
                        <hr class="my-4">
                        <div>
                            <div class="alert alert-warning small mb-3">
                                <strong>Berikan alasan yang jelas</strong><br>
                                Alasan penolakan akan dicatat di riwayat tiket. Penolakan Padal mengembalikan penugasan kepada Admin.
                            </div>
                            <form action="{{ route('admin.tickets.reject', $ticket->id) }}" method="POST">
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
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menolak laporan ini?')">
                                    Tolak Laporan
                                </button>
                                <div class="form-text text-center mt-2">Alasan ini akan terlihat pada riwayat laporan.</div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @elseif(in_array($ticket->status, [\App\Models\Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL, \App\Models\Ticket::STATUS_DALAM_PENANGANAN]))
            <!-- Panel Penugasan Ulang -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Penugasan Ulang</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.tickets.reassign', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="reassign_padal_id" class="form-label">Unit Padal <span class="text-danger">*</span></label>
                            <select name="padal_id" id="reassign_padal_id" class="form-select" required>
                                <option value="">Pilih unit aktif</option>
                                @foreach($padals as $padal)
                                    <option value="{{ $padal->id }}" {{ $ticket->padal_id == $padal->id ? 'selected' : '' }}>
                                        {{ $padal->nama_padal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reassign_notes" class="form-label">Catatan Admin (Opsional)</label>
                            <textarea name="admin_notes" id="reassign_notes" rows="2" class="form-control" placeholder="Tambahkan catatan...">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Tugaskan Ulang
                        </button>
                    </form>
                    @if($ticket->status === \App\Models\Ticket::STATUS_DITOLAK_PADAL)
                        <div class="form-text text-muted mt-2">Alasan penolakan Padal tercatat.</div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Tim Penanganan -->
        @if($ticket->padal)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-bold">Tim Penanganan</span>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <small class="text-muted d-block">Unit Padal</small>
                        <strong class="text-dark">{{ $ticket->padal->nama_padal }}</strong>
                        <div class="small text-muted">Ketua: {{ $ticket->padal->leader?->name ?? '-' }}</div>
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
        @endif

        <!-- Riwayat Jejak Audit Status -->
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
                        <small class="text-muted">Belum ada jejak audit.</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
