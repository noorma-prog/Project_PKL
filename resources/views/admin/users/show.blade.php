@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('page_title', 'Detail Pengguna')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <h4 class="fw-bold text-dark mb-4">{{ $user->name }}</h4>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <small class="text-muted d-block">Email</small>
                <span class="text-dark fw-semibold">{{ $user->email }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Peran</small>
                <span class="badge role-badge-{{ $user->role }} text-uppercase">{{ $user->role }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">NRP / NIP</small>
                <span class="text-dark fw-semibold">{{ $user->nrp_nip ?? '-' }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Satuan Kerja</small>
                <span class="text-dark fw-semibold">{{ $user->satuan_kerja ?? '-' }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Nomor Telepon / WhatsApp</small>
                <span class="text-dark">{{ $user->phone_number ?? '-' }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Status</small>
                @if(in_array($user->role, ['padal', 'teknisi'], true) && !$user->is_active && !$user->hasVerifiedEmail() && $user->requiresEmailVerification())
                    <span class="text-warning fw-semibold">Belum aktif / menunggu aktivasi email</span>
                @elseif($user->is_active)
                    <span class="text-success fw-semibold">Aktif</span>
                @else
                    <span class="text-danger fw-semibold">Nonaktif</span>
                @endif
            </div>
            @if($user->alamat)
                <div class="col-12">
                    <small class="text-muted d-block">Alamat</small>
                    <span class="text-dark">{{ $user->alamat }}</span>
                </div>
            @endif
        </div>

        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
            @if(in_array($user->role, ['padal', 'teknisi'], true) && !$user->is_active && !$user->hasVerifiedEmail() && $user->requiresEmailVerification() && $user->approval_status === 'active')
                <form action="{{ route('admin.users.activation-invitation', $user->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Kirim Undangan Aktivasi
                    </button>
                </form>
            @endif

            @if($user->role !== 'admin')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-secondary">
                    Ubah Pengguna
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
