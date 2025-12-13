<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TraineePurchaseNotification extends Notification
{
    use Queueable;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', \App\Notifications\Channels\PusherChannel::class];
    }

    public function toArray($notifiable)
    {
        $p = $this->payload ?? [];

        $traineeName = $p['trainee_name'] ?? ($p['trainee_id'] ?? 'Trainee');
        $planType = $p['plan_type'] ?? '';

        $title_ar = $p['title_ar'] ?? "شراء جديد من {$traineeName}";
        $message_ar = $p['message_ar'] ?? "{$traineeName} اشترى خطة {$planType}.";

        $title = $p['title'] ?? $title_ar;
        $message = $p['message'] ?? $message_ar;

        $data = $p['data'] ?? $p;
        $type = $p['type'] ?? 'trainee_purchase';
        $deep_link = $p['deep_link'] ?? null;

        return [
            'title' => $title,
            'message' => $message,
            'title_ar' => $title_ar,
            'message_ar' => $message_ar,
            'type' => $type,
            'data' => $data,
            'deep_link' => $deep_link,
            'timestamp' => now()->toDateTimeString(),
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
