<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GabineteStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $ticketId;
    public string $status;

    public function __construct(string $ticketId, string $status)
    {
        $this->ticketId = $ticketId;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        // La App escuchará el canal "ticket.ID_DEL_TICKET"
        return [
            new Channel('ticket.' . $this->ticketId), # Cambiar channel por PrivateChannel cuando se pase a la PI
        ];
    }
}
