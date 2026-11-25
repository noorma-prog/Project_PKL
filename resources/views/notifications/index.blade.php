@extends('layouts.app')

@section('title', 'Semua Notifikasi')
@section('page_title', 'Notifikasi')
@section('page_subtitle', 'Daftar seluruh notifikasi penugasan dan aktivitas sistem')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bell-fill text-primary me-2"></i> Semua Notifikasi</h6>
                    <small class="text-muted">Riwayat notifikasi penugasan tiket dan aktivitas sistem</small>
                </div>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($notifications as $notif)
                    <a href="{{ route('notifications.read', $notif->id) }}"
                       class="d-flex align-items-start gap-3 px-4 py-3 border-bottom text-decoration-none {{ $notif->read_at ? 'bg-white text-muted' : 'bg-light-blue' }}"
                       style="{{ !$notif->read_at ? 'background:#f0f7ff;' : '' }}">
                        <div class="flex-shrink-0 mt-1">
                            <i class="bi bi-info-circle-fill {{ $notif->read_at ? 'text-muted' : 'text-primary' }}" style="font-size:1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="{{ !$notif->read_at ? 'fw-semibold text-dark' : 'text-secondary' }}" style="font-size:0.88rem;">
                                {{ $notif->data['message'] ?? 'Notifikasi baru' }}
                            </div>
                            <div class="text-muted mt-1" style="font-size:0.76rem;">
                                <i class="bi bi-clock me-1"></i>{{ $notif->created_at->translatedFormat('d F Y, H:i') }} WITA
                                @if(!$notif->read_at)
                                    <span class="badge bg-primary ms-2" style="font-size:0.65rem;">Baru</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                        Belum ada notifikasi.
                    </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

