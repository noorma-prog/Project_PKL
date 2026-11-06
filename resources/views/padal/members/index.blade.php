@extends('layouts.app')

@section('title', 'Anggota Tim')
@section('page_title', 'Anggota Tim')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
@php($selectedTechnicianIds = old('technician_ids', []))

<div class="card mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold text-dark mb-1">{{ $padal->nama_padal }}</h5>
        <p class="text-muted small mb-3">Kelola keanggotaan tim. Penugasan teknisi ke tiket tetap dilakukan pada detail tiket.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Anggota Teknisi
        </button>
    </div>
</div>

<!-- Modal Tambah Anggota Teknisi -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('padal.members.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahAnggotaLabel">Tambah Anggota Teknisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($availableTechnicians->isEmpty())
                        <div class="alert alert-info small mb-0">
                            Seluruh personel teknisi aktif sudah terdaftar di unit Padal ini atau belum ada teknisi lain yang aktif.
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Pilih Personel Teknisi <span class="text-danger">*</span></label>
                            <div class="p-3 bg-light rounded border" style="max-height: 240px; overflow-y: auto;">
                                @foreach($availableTechnicians as $tech)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="technician_ids[]" value="{{ $tech->id }}" id="modalTech{{ $tech->id }}"
                                               {{ is_array($selectedTechnicianIds) && in_array($tech->id, $selectedTechnicianIds) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fw-semibold" for="modalTech{{ $tech->id }}">
                                            {{ $tech->name }} (NRP: {{ $tech->nrp_nip ?? '-' }}) &mdash; {{ $tech->satuan_kerja ?? 'Bid TIK' }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('technician_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('technician_ids.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex flex-column gap-2 p-4 pt-0 border-0">
                    @if($availableTechnicians->isNotEmpty())
                        <button type="submit" class="btn btn-primary w-100">Tambahkan ke Tim</button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Daftar Anggota Tim -->
<div class="d-flex flex-column gap-3 mb-4">
    @forelse($members as $member)
        <div class="card mb-0">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">{{ $member->name }}</h6>
                    <div class="text-muted small">
                        NRP/NIP: {{ $member->nrp_nip ?? '-' }} • {{ $member->satuan_kerja ?? 'Bid TIK' }}
                        @if($member->phone_number)
                            • No. HP: {{ $member->phone_number }}
                        @endif
                    </div>
                </div>
                <div>
                    <form action="{{ route('padal.members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Hapus {{ $member->name }} dari tim Anda?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            Hapus Anggota
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body p-5 text-center text-muted">
                Belum ada anggota teknisi dalam unit ini. Klik tombol "Tambah Anggota Teknisi" untuk menambahkan teknisi ke tim Anda.
            </div>
        </div>
    @endforelse
</div>
@endsection
