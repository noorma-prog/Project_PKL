@props(['tickets', 'showRoute'])

@if($tickets->isNotEmpty())
    <div class="schedule-card">
        <div class="schedule-card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Jadwal Kegiatan Terdekat</span>
            <small class="text-muted">Urutan berdasarkan waktu kegiatan</small>
        </div>
        <div class="d-flex flex-column gap-2">
            @foreach($tickets as $ticket)
                <a href="{{ route($showRoute, $ticket) }}" class="schedule-item">
                    <div class="schedule-date-badge">
                        <span class="day">{{ $ticket->event_date ? $ticket->event_date->format('d') : '-' }}</span>
                        <span class="month">{{ $ticket->event_date ? strtoupper($ticket->event_date->locale('id')->translatedFormat('M')) : 'TBA' }}</span>
                    </div>
                    <div class="schedule-info">
                        <div class="schedule-title">{{ $ticket->title }}</div>
                        <div class="schedule-meta">
                            @if($ticket->event_start_time && $ticket->event_end_time)
                                {{ substr($ticket->event_start_time, 0, 5) }}-{{ substr($ticket->event_end_time, 0, 5) }} • 
                            @endif
                            {{ $ticket->location ?? ($ticket->user?->satuan_kerja ?? 'POLDA') }}
                        </div>
                    </div>
                    <span class="{{ $ticket->status_badge_class }} d-none d-sm-inline-flex">{{ $ticket->status_label }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
