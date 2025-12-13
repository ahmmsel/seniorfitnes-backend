<?php

namespace App\Http\Controllers\Api;

use App\Events\GenericBroadcastEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Broadcast Controller for API-triggered real-time events
 * 
 * This controller provides endpoints for triggering real-time broadcasts
 * directly from API calls, useful for testing or external integrations.
 */
class BroadcastController extends Controller
{
    /**
     * Trigger a generic broadcast event
     * 
     * POST /api/broadcast
     * 
     * Body:
     * {
     *   "channel": "private-chat.123",
     *   "event": "message.created",
     *   "data": {
     *     "message": "Hello World",
     *     "sender": {...}
     *   }
     * }
     * 
     * Response:
     * {
     *   "status": "success",
     *   "message": "Event broadcasted successfully",
     *   "data": {
     *     "channel": "private-chat.123",
     *     "event": "message.created",
     *     "timestamp": "2025-12-09T10:30:00.000000Z"
     *   }
     * }
     */
    public function trigger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => 'required|string|max:255',
            'event' => 'required|string|max:100',
            'data' => 'sometimes|array',
        ]);

        // Validate channel naming convention
        if (!$this->isValidChannelName($validated['channel'])) {
            throw ValidationException::withMessages([
                'channel' => 'Invalid channel name. Use format: [private-|presence-]resource.id'
            ]);
        }

        // Validate event naming convention
        if (!$this->isValidEventName($validated['event'])) {
            throw ValidationException::withMessages([
                'event' => 'Invalid event name. Use format: resource.action (e.g., message.created)'
            ]);
        }

        $timestamp = now();

        // Trigger the broadcast event
        event(new GenericBroadcastEvent(
            $validated['channel'],
            $validated['event'],
            $validated['data'] ?? []
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Event broadcasted successfully',
            'data' => [
                'channel' => $validated['channel'],
                'event' => $validated['event'],
                'timestamp' => $timestamp->toISOString(),
            ],
        ]);
    }

    /**
     * Get broadcasting information and examples
     * 
     * GET /api/broadcast/info
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Broadcasting configuration and examples',
            'data' => [
                'driver' => config('broadcasting.default'),
                'pusher_app_key' => config('broadcasting.connections.pusher.key'),
                'pusher_cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'channel_conventions' => [
                    'private-chat.{chat_id}' => 'Private chat messages between users',
                    'private-user.{user_id}' => 'User-specific notifications and updates',
                    'presence-workout.{workout_id}' => 'Real-time workout participation',
                    'public-announcements' => 'Global announcements to all users',
                ],
                'event_conventions' => [
                    'message.created' => 'New message in chat',
                    'message.updated' => 'Message edited or status changed',
                    'user.status_changed' => 'User online/offline status',
                    'workout.participant_joined' => 'New participant in workout',
                    'notification.received' => 'New notification for user',
                ],
                'client_examples' => [
                    'javascript' => [
                        'subscribe' => "pusher.subscribe('private-chat.123').bind('message.created', callback)",
                        'unsubscribe' => "pusher.unsubscribe('private-chat.123')",
                    ],
                    'flutter' => [
                        'subscribe' => "channel.bind('message.created', (PusherEvent event) => handleMessage(event))",
                        'auth_endpoint' => url('/api/broadcasting/auth'),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test broadcast functionality
     * 
     * GET /api/broadcast/test
     */
    public function test(): JsonResponse
    {
        $testEvent = 'system.test';
        $testChannel = 'public-test';
        $testData = [
            'message' => 'This is a test broadcast',
            'timestamp' => now()->toISOString(),
            'random_id' => uniqid(),
        ];

        // Trigger test event
        event(new GenericBroadcastEvent($testChannel, $testEvent, $testData));

        return response()->json([
            'status' => 'success',
            'message' => 'Test broadcast sent',
            'data' => [
                'channel' => $testChannel,
                'event' => $testEvent,
                'payload' => $testData,
                'instructions' => [
                    'subscribe_to' => $testChannel,
                    'listen_for' => $testEvent,
                    'client_code' => "pusher.subscribe('$testChannel').bind('$testEvent', (data) => console.log(data))",
                ],
            ],
        ]);
    }

    /**
     * Validate channel name format
     */
    private function isValidChannelName(string $channel): bool
    {
        // Allow: private-chat.123, presence-workout.456, public-announcements, etc.
        return preg_match('/^(private-|presence-|public-)?[a-z0-9_-]+(\.[a-z0-9_-]+)*$/i', $channel);
    }

    /**
     * Validate event name format
     */
    private function isValidEventName(string $event): bool
    {
        // Allow: resource.action format (message.created, user.updated, etc.)
        return preg_match('/^[a-z0-9_-]+\.[a-z0-9_-]+$/i', $event);
    }
}
