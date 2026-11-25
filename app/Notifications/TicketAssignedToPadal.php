<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketAssignedToPadal extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly User   $assignedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'ticket_assigned_to_padal',
            'ticket_id'      => $this->ticket->id,
            'ticket_number'  => $this->ticket->ticket_number,
            'ticket_title'   => $this->ticket->title,
            'assigned_by'    => $this->assignedBy->name,
            'message'        => 'Tiket ' . $this->ticket->ticket_number . ' (' . $this->ticket->title . ') telah ditugaskan kepada unit Anda oleh ' . $this->assignedBy->name . '.',
            'url'            => route('padal.tickets.show', $this->ticket->id),
        ];
    }
}

