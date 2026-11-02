@props(['ticket'])

@if($ticket->event_date && $ticket->event_start_time && $ticket->event_end_time)
    <div class="small">
        {{ $ticket->event_date->locale('id')->translatedFormat('d F Y') }},
        {{ substr($ticket->event_start_time, 0, 5) }}–{{ substr($ticket->event_end_time, 0, 5) }} WITA
        <span class="badge bg-light text-dark">{{ $ticket->schedule_status }}</span>
    </div>
@else
    <div class="small text-muted">Jadwal belum ditentukan</div>
@endif
