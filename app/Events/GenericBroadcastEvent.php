<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic Broadcast Event for API-first applications
 * 
 * This event provides a flexible way to broadcast real-time events to various channels
 * without creating specific event classes for each use case.
 * 
 * Usage Examples:
 * 
 * 1. Chat Message Created:
 *    event(new GenericBroadcastEvent(
 *        'private-chat.123',
 *        'message.created',
 *        ['message' => $message->toArray(), 'sender' => $sender->toArray()]
 *    ));
 * 
 * 2. User Status Update:
 *    event(new GenericBroadcastEvent(
 *        'presence-workout.456',
 *        'user.status_changed',
 *        ['user_id' => 123, 'status' => 'online']
 *    ));
 * 
 * 3. Notification:
 *    event(new GenericBroadcastEvent(
 *        'private-user.789',
 *        'notification.received',
 *        ['notification' => $notification->toArray()]
 *    ));
 * 
 * Client-side Subscription (Flutter/React Native):
 * 
 * // Subscribe to private channel
 * pusher.subscribe('private-chat.123').bind('message.created', (data) => {
 *     console.log('New message:', data.message);
 * });
 * 
 * Channel Naming Conventions:
 * - private-chat.{chat_id} - Private chat channels
 * - private-user.{user_id} - User-specific notifications
 * - presence-workout.{workout_id} - Workout presence channels
 * - public-announcements - Global announcements
 * 
 * Event Naming Conventions:
 * - {resource}.{action} format (e.g., message.created, user.updated)
 * - Use snake_case for consistency with Laravel/API standards
 * - Be descriptive but concise
 */
class GenericBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The channel name to broadcast on
     */
    public string $channelName;

    /**
     * The event name to broadcast as
     */
    public string $eventName;

    /**
     * The data payload to broadcast
     */
    public array $payload;

    /**
     * Create a new event instance
     *
     * @param string $channelName Channel to broadcast on (e.g., 'private-chat.123')
     * @param string $eventName Event name (e.g., 'message.created')
     * @param array $payload Data to send to clients
     */
    public function __construct(string $channelName, string $eventName, array $payload = [])
    {
        $this->channelName = $channelName;
        $this->eventName = $eventName;
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Determine channel type based on prefix
        if (str_starts_with($this->channelName, 'private-')) {
            return [new PrivateChannel(str_replace('private-', '', $this->channelName))];
        }

        if (str_starts_with($this->channelName, 'presence-')) {
            return [new PresenceChannel(str_replace('presence-', '', $this->channelName))];
        }

        // Default to public channel
        return [new Channel($this->channelName)];
    }

    /**
     * The event's broadcast name
     */
    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    /**
     * Get the data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'event' => $this->eventName,
            'timestamp' => now()->toISOString(),
            'data' => $this->payload,
        ];
    }

    /**
     * Determine if this event should be queued
     */
    public function shouldQueue(): bool
    {
        return true;
    }

    /**
     * Get the queue that should be used when broadcasting
     */
    public function onQueue(): string
    {
        return 'broadcasts';
    }
}
