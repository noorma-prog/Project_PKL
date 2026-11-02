@props(['ticket'])

<div class="mt-1 small text-muted">
    <div>Pengajuan: {{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }} WITA</div>
    @if($ticket->event_date && $ticket->event_start_time && $ticket->event_end_time)
        <div>
            Jadwal:
            {{ $ticket->event_date->locale('id')->translatedFormat('d F Y') }},
            {{ substr($ticket->event_start_time, 0, 5) }}–{{ substr($ticket->event_end_time, 0, 5) }} WITA
        </div>
        @php
            $scheduleBadgeClass = match ($ticket->schedule_status) {
                'Jadwal hari ini' => 'bg-warning-subtle text-warning-emphasis',
                'Jadwal sudah lewat' => 'bg-secondary-subtle text-secondary-emphasis',
                default => 'bg-success-subtle text-success-emphasis',
            };
        @endphp
        <span class="badge {{ $scheduleBadgeClass }}">{{ $ticket->schedule_status }}</span>
    @else
        <div>Jadwal: Belum ditentukan</div>
    @endif
</div>
