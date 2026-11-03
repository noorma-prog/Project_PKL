@props(['tickets', 'showRoute'])

<section {{ $attributes->merge(['class' => 'card border-0 shadow-sm mb-4']) }}>
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-2">
        <div>
            <h2 class="h6 fw-bold mb-1">Jadwal Kegiatan Terdekat</h2>
            <p class="small text-muted mb-0">Urutan berdasarkan tanggal dan jam kegiatan.</p>
        </div>
    </div>
    <div class="list-group list-group-flush">
        @forelse($tickets as $ticket)
            <a href="{{ route($showRoute, $ticket) }}" class="list-group-item list-group-item-action py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div class="min-w-0">
                        <div class="fw-semibold">{{ $ticket->title }}</div>
                        <div class="small text-muted">
                            {{ $ticket->ticket_number }} · {{ $ticket->category?->name ?? 'Kategori tidak tersedia' }}
                        </div>
                        @if($ticket->user)
                            <div class="small text-muted">{{ $ticket->user->name }}</div>
                        @endif
                    </div>
                    <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                </div>
                <x-ticket-schedule :ticket="$ticket" />
            </a>
        @empty
            <div class="list-group-item text-muted py-4">Belum ada jadwal kegiatan mendatang.</div>
        @endforelse
    </div>
</section>
