@extends('layouts.app')

@section('title', 'Data Pengguna')
@section('page_title', 'Data Pengguna')
@section('page_subtitle', 'Kelola layanan IT dengan informasi yang jelas.')

@section('content')
<!-- Filter & Action Bar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Cari pengguna</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nama, email, atau NRP/NIP" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Peran</label>
                <select name="role" id="role" class="form-select">
                    <option value="">Semua</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="padal" {{ request('role') == 'padal' ? 'selected' : '' }}>Padal</option>
                    <option value="teknisi" {{ request('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="approval_status" class="form-label">Persetujuan</label>
                <select name="approval_status" id="approval_status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="active" {{ request('approval_status') == 'active' ? 'selected' : '' }}>Disetujui Aktif</option>
                    <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="mb-4">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
    </a>
</div>

<!-- Daftar Pengguna -->
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">PENGGUNA</th>
                        <th>ROLE & SATKER</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3" data-label="Pengguna">
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <div class="small text-muted">
                                    {{ $user->email }}
                                    @if($user->nrp_nip)
                                        • NRP: {{ $user->nrp_nip }}
                                    @endif
                                </div>
                            </td>
                            <td data-label="Role & Satker">
                                <span class="badge role-badge-{{ $user->role }} text-uppercase">{{ $user->role }}</span>
                                <div class="small text-muted mt-1">{{ $user->satuan_kerja ?? '-' }}</div>
                            </td>
                            <td data-label="Status">
                                @if(in_array($user->role, ['padal', 'teknisi'], true) && !$user->is_active && !$user->hasVerifiedEmail() && $user->requiresEmailVerification())
                                    <span class="badge bg-warning text-dark">Menunggu Aktivasi</span>
                                @elseif($user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif

                                @if($user->email_verified_at)
                                    <span class="small text-muted d-block mt-1"><i class="bi bi-check-circle-fill text-success"></i> Email Terverifikasi</span>
                                @else
                                    <span class="small text-muted d-block mt-1"><i class="bi bi-clock-history text-warning"></i> Belum Verifikasi</span>
                                @endif
                            </td>
                            <td class="text-end pe-3" data-label="Aksi">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($user->role !== 'admin')
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="Ubah">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if(in_array($user->role, ['padal', 'teknisi'], true) && !$user->is_active && !$user->hasVerifiedEmail() && $user->requiresEmailVerification() && $user->approval_status === 'active')
                                        <form action="{{ route('admin.users.activation-invitation', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Kirim Ulang Undangan Aktivasi">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== Auth::id() && in_array($user->role, ['padal', 'teknisi'], true) && $user->approval_status === 'pending')
                                        <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui akun ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Setujui Akun">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak akun ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Tolak Akun">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== Auth::id() && $user->role !== 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteUser{{ $user->id }}" 
                                                title="Hapus Akun">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Modal Hapus Akun -->
                                @if($user->id !== Auth::id() && $user->role !== 'admin')
                                    <div class="modal fade" id="modalDeleteUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-dark">Hapus Akun Pengguna</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p class="mb-2">Apakah Anda yakin ingin menghapus akun pengguna berikut? Tindakan ini tidak dapat dibatalkan.</p>
                                                        <div class="p-3 bg-light rounded text-dark">
                                                            <strong>{{ $user->name }}</strong> ({{ $user->email }})<br>
                                                            <small class="text-muted">Role: {{ strtoupper($user->role) }} | NRP: {{ $user->nrp_nip ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="table-empty-row">
                            <td colspan="4" class="text-center py-5 text-muted">Tidak ada data pengguna yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">
                Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </span>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
