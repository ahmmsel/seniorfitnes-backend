<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Pusher\Pusher;
use Illuminate\Support\Facades\Log;

class PusherChannel
{
    protected function makePusher(): ?Pusher
    {
        $driver = config('broadcasting.default', env('BROADCAST_CONNECTION', env('BROADCAST_DRIVER', 'log')));

        if ($driver !== 'pusher') {
            return null;
        }

        $key = env('PUSHER_APP_KEY');
        $secret = env('PUSHER_APP_SECRET');
        $appId = env('PUSHER_APP_ID');
        $cluster = env('PUSHER_APP_CLUSTER');

        if (! $key || ! $secret || ! $appId || ! $cluster) {
            Log::warning('Pusher credentials are missing; notification skipped.');

            return null;
        }

        $options = [
            'cluster' => $cluster,
            'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
        ];

        // Allow overriding host/port for self-hosted soketi
        if ($host = env('PUSHER_HOST')) {
            $options['host'] = $host;
        }
        if ($port = env('PUSHER_PORT')) {
            $options['port'] = (int) $port;
        }
        if ($scheme = env('PUSHER_SCHEME')) {
            $options['scheme'] = $scheme;
        }

        return new Pusher($key, $secret, $appId, $options);
    }

    public function send($notifiable, Notification $notification)
    {
        if (! isset($notifiable->id)) {
            return;
        }

        $channel = 'private-user.' . $notifiable->id;

        if (property_exists($notification, 'payload') && is_array($notification->payload)) {
            $payload = $notification->payload;
        } else {
            $payload = $notification->toArray($notifiable);
        }

        try {
            if ($pusher = $this->makePusher()) {
                // Trigger event name 'notification' with payload
                $pusher->trigger($channel, 'notification', ['notification' => $payload]);
            }
        } catch (\Throwable $e) {
            Log::error('PusherChannel send failed: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $notifiable->id]);
        }
    }
}
