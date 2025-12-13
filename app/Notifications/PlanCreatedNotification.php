<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PlanCreatedNotification extends Notification
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

        $planName = $p['plan_name'] ?? null;
        $title_ar = $p['title_ar'] ?? ($planName ? "تم إنشاء خطة {$planName}" : 'تم إنشاء خطة جديدة');
        $message_ar = $p['message_ar'] ?? ($planName ? "الخطة {$planName} أصبحت متاحة." : 'تمت إضافة خطة جديدة.');

        $title = $p['title'] ?? $title_ar;
        $message = $p['message'] ?? $message_ar;

        $data = $p['data'] ?? $p;
        $type = $p['type'] ?? 'plan_created';
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
