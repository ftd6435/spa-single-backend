<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $visitorId;
    public string $path;
    public string $referrer;
    public string $userAgent;
    public string $ip;

    /**
     * Create a new event instance.
     */
    public function __construct(string $visitorId, string $path, string $referrer, string $userAgent, string $ip)
    {
        $this->visitorId = $visitorId;
        $this->path = $path;
        $this->referrer = $referrer;
        $this->userAgent = $userAgent;
        $this->ip = $ip;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
