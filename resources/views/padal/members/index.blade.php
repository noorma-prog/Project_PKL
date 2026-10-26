@extends('layouts.app')

@section('title', 'Mengelola Anggota Tim')
@section('page_title', 'Mengelola Anggota Tim Padal')
@section('page_subtitle', 'Manajemen personel teknisi yang bertugas di dalam unit ' . $padal->nama_padal)

@section('content')
@php($selectedTechnicianIds = old('technician_ids', []))
<div class="row g-4">
    <!-- Ringkasan Unit Padal -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-police text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-shaded me-2"></i>Informasi Unit Padal</h6>
            </div>
            <div class="card-body">
                <div class="text-center py-2 mb-3 border-bottom">
                    <div class="avatar-lg rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-check fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">{{ $padal->nama_padal }}</h5>
                    <span class="badge bg-success">Status: {{ ucfirst($padal->status) }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Perwira Pengendali (Ketua):</small>
                    <span class="fw-bold text-dark">{{ Auth::user()->name }}</span>
                    <small class="text-muted d-block">NRP/NIP: {{ Auth::user()->nrp_nip ?? '-' }}</small>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Jumlah Anggota Teknisi:</small>
                    <span class="fw-bold fs-5 text-primary">{{ $members->count() }} Personel</span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Deskripsi / Ruang Lingkup:</small>
                    <p class="small text-secondary mb-0">{{ $padal->deskripsi ?? 'Tidak ada catatan ruang lingkup.' }}</p>
                </div>
            </div>
        </div>

        <!-- Tambah Anggota Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Anggota Teknisi</h6>
            </div>
            <div class="card-body">
                @if($availableTechnicians->isEmpty())
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Seluruh personel teknisi aktif sudah terdaftar di unit Padal ini atau belum ada teknisi lain.
                    </div>
                @else
                    <form action="{{ route('padal.members.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Pilih Personel Teknisi</label>
                            <select name="technician_ids[]" class="form-select @error('technician_ids') is-invalid @enderror @error('technician_ids.*') is-invalid @enderror" size="5" multiple required>
                                @foreach($availableTechnicians as $tech)
                                    <option value="{{ $tech->id }}" @selected(is_array($selectedTechnicianIds) && in_array($tech->id, $selectedTechnicianIds))>
                                        {{ $tech->name }} (NRP: {{ $tech->nrp_nip ?? '-' }}) - {{ $tech->satuan_kerja ?? 'Bid TIK' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Gunakan Ctrl (Windows) atau Command (Mac) untuk memilih beberapa teknisi.</small>
                            @error('technician_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('technician_ids.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambahkan ke Tim
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Anggota Teknisi Padal -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-people-fill text-primary me-2"></i>Daftar Personel Teknisi Unit ({{ $members->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Teknisi</th>
                                <th>NRP / NIP</th>
                                <th>No. Telepon / WA</th>
                                <th>Tanggal Bergabung</th>
                                <th class="text-end" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $index => $member)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $member->name }}</div>
                                        <small class="text-muted">{{ $member->satuan_kerja ?? 'Subbid Tekkom Bid TIK' }}</small>
                                    </td>
                                    <td><span class="font-monospace text-secondary">{{ $member->nrp_nip ?? '-' }}</span></td>
                                    <td>
                                        @if($member->phone_number)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone_number) }}" target="_blank" class="text-success text-decoration-none small">
                                                <i class="bi bi-whatsapp me-1"></i>{{ $member->phone_number }}
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $member->pivot->tanggal_bergabung ? \Carbon\Carbon::parse($member->pivot->tanggal_bergabung)->format('d/m/Y') : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('padal.members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan teknisi {{ $member->name }} dari unit Padal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Keluarkan dari Tim">
                                                <i class="bi bi-person-x"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-people display-6 d-block mb-2 text-secondary"></i>
                                        Belum ada personel teknisi yang ditambahkan ke dalam unit Padal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
