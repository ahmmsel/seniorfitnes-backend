# Laravel Pusher Broadcasting Implementation

## Overview

This implementation provides a flexible, API-first broadcasting system using Pusher for real-time communication in Laravel. It's designed specifically for mobile apps (Flutter, React Native) and API consumers.

## Architecture

### 1. Generic Broadcast Event (`app/Events/GenericBroadcastEvent.php`)

A reusable event class that implements `ShouldBroadcast` and accepts:

-   **Channel name**: Where to broadcast (e.g., `private-chat.123`)
-   **Event name**: What happened (e.g., `message.created`)
-   **Payload**: Data to send to clients

```php
// Usage in controllers
event(new GenericBroadcastEvent(
    'private-chat.123',
    'message.created',
    $messageData
));
```

### 2. Channel Naming Conventions

-   **Private channels**: `private-{resource}.{id}`

    -   `private-chat.123` - Chat between users
    -   `private-user.456` - User-specific notifications

-   **Presence channels**: `presence-{resource}.{id}`

    -   `presence-workout.789` - Live workout participants

-   **Public channels**: `public-{category}` or `{category}`
    -   `public-announcements` - Global announcements

### 3. Event Naming Conventions

Use `{resource}.{action}` format:

-   `message.created` - New message
-   `message.updated` - Message edited
-   `user.status_changed` - User online/offline
-   `workout.participant_joined` - New workout participant
-   `notification.received` - New notification

## Client Integration

### Flutter/Dart Example

```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

// Initialize Pusher
PusherChannelsFlutter pusher = PusherChannelsFlutter.getInstance();
await pusher.init(
  apiKey: "YOUR_PUSHER_KEY",
  cluster: "YOUR_CLUSTER",
);

// Connect
await pusher.connect();

// Subscribe to private channel (requires auth)
Channel channel = await pusher.subscribe(
  channelName: "private-chat.123",
);

// Listen for events
channel.bind('message.created', (PusherEvent event) {
  final data = jsonDecode(event.data);
  print('New message: ${data['data']['message']['body']}');
});

// Auth endpoint for private channels
await pusher.init(
  apiKey: "YOUR_PUSHER_KEY",
  cluster: "YOUR_CLUSTER",
  authEndpoint: "https://yourapi.com/api/broadcasting/auth",
  onAuthorizer: (channelName, socketId, options) async {
    return {
      "Authorization": "Bearer $userToken",
    };
  },
);
```

### JavaScript/React Native Example

```javascript
import Pusher from "pusher-js";

const pusher = new Pusher("YOUR_PUSHER_KEY", {
    cluster: "YOUR_CLUSTER",
    authEndpoint: "/api/broadcasting/auth",
    auth: {
        headers: {
            Authorization: "Bearer " + userToken,
        },
    },
});

// Subscribe to channel
const channel = pusher.subscribe("private-chat.123");

// Listen for events
channel.bind("message.created", (data) => {
    console.log("New message:", data.data.message);
});
```

## API Endpoints

### Broadcasting Control

-   **POST** `/api/broadcast` - Trigger custom broadcast
-   **GET** `/api/broadcast/info` - Get broadcasting configuration
-   **GET** `/api/broadcast/test` - Test broadcast functionality

### Chat Integration

Chat messages automatically broadcast to `private-chat.{chat_id}` with event `message.created`.

## Implementation Examples

### 1. Chat Message Broadcasting

```php
// In ChatService::sendMessage()
event(new GenericBroadcastEvent(
    'private-chat.' . $chat->id,
    'message.created',
    [
        'message' => [
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at->toISOString(),
        ],
        'sender' => [
            'id' => $sender->id,
            'name' => $sender->name,
            'type' => $senderType,
        ],
        'chat_id' => $chat->id,
    ]
));
```

### 2. User Notification

```php
// In NotificationController
event(new GenericBroadcastEvent(
    'private-user.' . $user->id,
    'notification.received',
    [
        'notification' => [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'created_at' => $notification->created_at->toISOString(),
        ]
    ]
));
```

### 3. Workout Status Update

```php
// In WorkoutController
event(new GenericBroadcastEvent(
    'presence-workout.' . $workout->id,
    'participant.joined',
    [
        'participant' => [
            'id' => $user->id,
            'name' => $user->name,
        ],
        'workout_id' => $workout->id,
        'total_participants' => $workout->participants->count(),
    ]
));
```

## Configuration

### Environment Variables

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Broadcasting Channels (routes/channels.php)

```php
// Chat authorization
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return \App\Models\Chat::where('id', $chatId)
        ->where(function ($query) use ($user) {
            $query->where('coach_id', $user->id)
                  ->orWhere('trainee_id', $user->id);
        })
        ->exists();
});

// User notifications
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

## Queue Configuration

Broadcasting events are queued by default for better performance:

```php
// In GenericBroadcastEvent
public function shouldQueue(): bool
{
    return true;
}

public function onQueue(): string
{
    return 'broadcasts';
}
```

Make sure to run queue workers:

```bash
php artisan queue:work --queue=broadcasts
```

## Testing

### Test Broadcast Endpoint

```bash
curl -X GET "http://yourapi.com/api/broadcast/test" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Manual Trigger

```bash
curl -X POST "http://yourapi.com/api/broadcast" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "channel": "private-chat.123",
    "event": "message.created",
    "data": {
      "message": {"body": "Test message"},
      "sender": {"name": "Test User"}
    }
  }'
```

## Migration from Existing Events

The system maintains backward compatibility with existing `MessageSent` event while encouraging use of `GenericBroadcastEvent` for new implementations.

## Best Practices

1. **Consistent Naming**: Use snake_case for events, kebab-case for channels
2. **Structured Payloads**: Always include timestamp and clear data structure
3. **Authorization**: Implement proper channel authorization in `routes/channels.php`
4. **Error Handling**: Wrap broadcasts in try-catch for error resilience
5. **Queuing**: Use queues for broadcasts to prevent blocking API responses
6. **Testing**: Use the test endpoints to verify configuration

This implementation provides a solid foundation for real-time features while maintaining API-first principles and mobile app compatibility.
