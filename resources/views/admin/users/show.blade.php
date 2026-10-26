@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('page_title', 'Detail Pengguna')
@section('page_subtitle', 'Informasi akun dan keterkaitan dengan aktivitas LayanIT')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Detail Akun</h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                        <div class="text-muted small">{{ $user->email }}</div>
                    </div>
                    <span class="badge role-badge-{{ $user->role }} text-uppercase">{{ $user->role }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">NRP / NIP</div>
                            <div class="fw-semibold">{{ $user->nrp_nip ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Status Akun</div>
                            <div class="fw-semibold text-{{ $user->is_active ? 'success' : 'danger' }}">
                                @if(in_array($user->role, ['padal', 'teknisi'], true) && !$user->is_active && !$user->hasVerifiedEmail() && $user->requiresEmailVerification())
                                    Menunggu Aktivasi
                                @else
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Satuan Kerja</div>
                            <div class="fw-semibold">{{ $user->satuan_kerja ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">No. WhatsApp / HP</div>
                            <div class="fw-semibold">{{ $user->phone_number ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="text-muted small mb-1">Alamat</div>
                            <div class="fw-semibold">{{ $user->alamat ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Jumlah Laporan</div>
                            <div class="fw-semibold">{{ $user->tickets_count }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small mb-1">Keanggotaan Padal</div>
                            <div class="fw-semibold">{{ $user->padal_teams_count }}</div>
                        </div>
                    </div>
                </div>

                @if($user->role !== 'admin')
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Pengguna
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
