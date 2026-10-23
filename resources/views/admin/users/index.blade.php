@extends('layouts.app')

@section('title', 'Mengelola Akun Pengguna')
@section('page_title', 'Data Pengguna Sistem')
@section('page_subtitle', 'Kelola akun Admin, Padal, Teknisi, dan Pelapor')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, NRP, alamat, satuan kerja..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">-- Semua Role --</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="padal" {{ request('role') == 'padal' ? 'selected' : '' }}>Padal (Perwira Pengendali)</option>
                    <option value="teknisi" {{ request('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User (Pelapor)</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-success ms-auto"><i class="bi bi-person-plus-fill me-1"></i> Tambah Akun</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">Daftar Akun Pengguna ({{ $users->total() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3">Nama & NRP/NIP</th>
                        <th>Email Dinas</th>
                        <th>Role</th>
                        <th>Satuan Kerja</th>
                        <th>Alamat</th>
                        <th>Kontak HP</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <small class="text-muted font-monospace">NRP/NIP: {{ $user->nrp_nip ?? '-' }}</small>
                            </td>
                            <td><small class="text-dark">{{ $user->email }}</small></td>
                            <td>
                                <span class="badge rounded-pill text-uppercase role-badge-{{ $user->role }} px-3 py-1">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td><small class="text-secondary">{{ $user->satuan_kerja ?? '-' }}</small></td>
                            <td><small class="text-secondary">{{ Str::limit($user->alamat ?? '-', 25) }}</small></td>
                            <td><small class="text-secondary">{{ $user->phone_number ?? '-' }}</small></td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-info" title="Lihat Akun">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Ubah Akun">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== Auth::id())
                                        <button type="button" class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteUser{{ $user->id }}" 
                                                title="Hapus Akun">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Modal Hapus Akun -->
                                @if($user->id !== Auth::id())
                                    <div class="modal fade" id="modalDeleteUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-danger">Konfirmasi Hapus Akun</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p class="mb-1">Apakah Anda yakin ingin menghapus akun pengguna berikut?</p>
                                                        <div class="p-3 bg-light rounded text-dark">
                                                            <strong>{{ $user->name }}</strong> ({{ $user->email }})<br>
                                                            <small class="text-muted">Role: {{ strtoupper($user->role) }} | NRP: {{ $user->nrp_nip ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Akun</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada data pengguna yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
