<?php

namespace App\Notifications;

use App\Notifications\Channels\PusherChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewPrivateMessage extends Notification
{
    use Queueable;

    /**
     * Public payload property - PusherChannel prefers this if present.
     *
     * @var array
     */
    public array $payload = [];

    /**
     * Create a new notification instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', PusherChannel::class];
    }

    /**
     * Get the array representation for the notification (database / broadcast fallback).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // If payload is already provided, use it directly. Ensure minimal required keys exist.
        $p = $this->payload ?? [];

        return [
            'id' => $p['id'] ?? null,
            'chat_id' => $p['chat_id'] ?? null,
            'sender_id' => $p['sender_id'] ?? null,
            'sender_name' => $p['sender_name'] ?? null,
            'body' => $p['body'] ?? null,
            'url' => $p['url'] ?? ($p['deep_link'] ?? null),
            'type' => $p['type'] ?? 'new_private_message',
            'timestamp' => now()->toDateTimeString(),
            'meta' => $p['meta'] ?? null,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
