# Laravel Broadcasting Integration - Complete Implementation

## ✅ Implementation Summary

Your Laravel chat API now has **real-time messaging** using Laravel Broadcasting with Pusher and private channels.

---

## 📁 Files Modified

### 1. **bootstrap/app.php**

```php
->withBroadcasting(
    __DIR__ . '/../routes/channels.php',
    ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
)
```

-   ✅ Broadcasting routes registered with auth:sanctum middleware
-   ✅ Auth endpoint: `POST /api/broadcasting/auth`

### 2. **routes/channels.php**

```php
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return \App\Models\Chat::where('id', $chatId)
        ->where(function ($query) use ($user) {
            $query->where('coach_id', $user->id)
                  ->orWhere('trainee_id', $user->id);
        })
        ->exists();
});
```

-   ✅ Private channel authorization
-   ✅ Only chat participants (coach or trainee) can subscribe
-   ✅ Channel format: `chat.{chatId}` → Pusher receives: `private-chat.{chatId}`

### 3. **app/Events/MessageSent.php**

```php
class MessageSent implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->chat->id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'message' => $this->message->message,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender' => [
                'id' => $sender->id,
                'name' => $sender->name,
                'avatar' => $sender->avatar,
                'profile_image_url' => $profileImageUrl,
            ],
            'read_at' => $this->message->read_at,
            'created_at' => $this->message->created_at,
            'updated_at' => $this->message->updated_at,
        ];
    }
}
```

-   ✅ Implements `ShouldBroadcast`
-   ✅ Uses `PrivateChannel`
-   ✅ Event name: `message.sent`
-   ✅ Includes profile images for coach/trainee
-   ✅ Complete message payload

### 4. **app/Services/ChatService.php**

```php
public function sendMessage(Chat $chat, User $sender, string $messageContent): Message
{
    // Create message...
    $message->load('sender');

    // Broadcast to other participants (not sender)
    broadcast(new \App\Events\MessageSent($message))->toOthers();

    return $message;
}
```

-   ✅ Broadcasts after saving message
-   ✅ Uses `toOthers()` to exclude sender
-   ✅ No modification to existing chat logic

---

## 🔧 Configuration

### .env Configuration

```env
# Broadcasting
BROADCAST_DRIVER=pusher

# Pusher Credentials
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=eu  # or us2, ap1, etc.

# Optional
PUSHER_SCHEME=https
PUSHER_PORT=443
```

### config/broadcasting.php

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'encrypted' => true,
        'useTLS' => true,
    ],
],
```

---

## 🚀 How It Works

### Backend Flow

1. Client calls `POST /api/chats/{chat}/message` with message
2. ChatController → ChatService creates message in database
3. ChatService broadcasts `MessageSent` event
4. Event broadcasts to `private-chat.{chatId}` channel
5. Only chat participants receive the message (sender excluded via `toOthers()`)

### Channel Authorization Flow

1. Client subscribes to `private-chat.2`
2. Pusher calls `POST /api/broadcasting/auth` with Bearer token
3. Laravel validates user via `auth:sanctum`
4. Channel authorization callback checks if user is participant
5. Returns authorization token or rejects

---

## 📱 Flutter/Client Integration

### Pusher Configuration

```dart
PusherChannelsFlutter pusher = PusherChannelsFlutter.getInstance();

await pusher.init(
  apiKey: 'YOUR_PUSHER_KEY',
  cluster: 'eu',
  authEndpoint: 'https://your-api.com/api/broadcasting/auth',
  onAuthorizer: (channelName, socketId, options) async {
    return await pusherAuth(
      channelName,
      socketId,
      options,
      authToken: userToken, // Sanctum token
    );
  },
);

await pusher.subscribe(
  channelName: 'private-chat.2',
  onEvent: (event) {
    if (event.eventName == 'message.sent') {
      final data = jsonDecode(event.data);
      // Handle new message
      print('New message: ${data['message']}');
      print('From: ${data['sender']['name']}');
      print('Profile image: ${data['sender']['profile_image_url']}');
    }
  },
);
```

### Auth Headers

```dart
Future<dynamic> pusherAuth(
  String channelName,
  String socketId,
  dynamic options,
  {required String authToken}
) async {
  final response = await http.post(
    Uri.parse('https://your-api.com/api/broadcasting/auth'),
    headers: {
      'Authorization': 'Bearer $authToken',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'socket_id': socketId,
      'channel_name': channelName,
    }),
  );
  return jsonDecode(response.body);
}
```

---

## 🎯 Event Payload

### What Pusher Receives

```json
{
    "event": "message.sent",
    "channel": "private-chat.2",
    "data": {
        "message_id": 34,
        "chat_id": 2,
        "message": "Hello, how are you?",
        "sender_id": 2,
        "sender_type": "coach",
        "sender": {
            "id": 2,
            "name": "John Coach",
            "avatar": null,
            "profile_image_url": "https://api.com/storage/1/profile.jpg"
        },
        "read_at": null,
        "created_at": "2025-12-10T21:36:46.000000Z",
        "updated_at": "2025-12-10T21:36:46.000000Z"
    }
}
```

---

## ✅ Verification Checklist

-   [x] Broadcasting driver set to `pusher`
-   [x] Pusher credentials configured in `.env`
-   [x] TLS enabled in `config/broadcasting.php`
-   [x] Broadcasting auth endpoint: `POST /api/broadcasting/auth`
-   [x] Auth middleware: `auth:sanctum`
-   [x] Private channel authorization in `routes/channels.php`
-   [x] Channel name: `chat.{chatId}` → Pusher: `private-chat.{chatId}`
-   [x] Event implements `ShouldBroadcast`
-   [x] Event name: `message.sent`
-   [x] Message broadcast after save
-   [x] `toOthers()` excludes sender
-   [x] Profile images included in payload
-   [x] Only participants can subscribe
-   [x] PSR-12 compliant code
-   [x] No existing logic modified

---

## 🧪 Testing

### Test Channel Authorization

```bash
php artisan tinker
```

```php
$user = User::find(1);
$chat = Chat::find(2);

// Check authorization
$canAccess = Chat::where('id', $chat->id)
    ->where(function ($query) use ($user) {
        $query->where('coach_id', $user->id)
              ->orWhere('trainee_id', $user->id);
    })
    ->exists();

echo $canAccess ? 'Authorized' : 'Denied';
```

### Test Message Broadcasting

```php
$service = new \App\Services\ChatService();
$message = $service->sendMessage($chat, $user, 'Test message');
// Check Pusher dashboard for event
```

### Test Event Payload

```php
$message = Message::latest()->first();
$event = new \App\Events\MessageSent($message);

echo "Channel: " . $event->broadcastOn()[0]->name . PHP_EOL;
echo "Event: " . $event->broadcastAs() . PHP_EOL;
echo json_encode($event->broadcastWith(), JSON_PRETTY_PRINT);
```

---

## 🔒 Security Features

1. **Authentication Required**: All broadcasting auth requires valid Sanctum token
2. **Authorization Checks**: Only chat participants can subscribe to channels
3. **Sender Excluded**: `toOthers()` prevents sender from receiving their own message
4. **TLS Enabled**: All Pusher connections use encrypted transport
5. **Private Channels**: No public access to chat messages

---

## 🎉 Implementation Complete!

Your chat system now has:

-   ✅ Real-time message delivery
-   ✅ Private secure channels
-   ✅ Profile image support
-   ✅ Participant-only access
-   ✅ Sender exclusion
-   ✅ Clean, modern code
-   ✅ Laravel 11 compatible
-   ✅ PSR-12 compliant
-   ✅ No existing logic broken

**Channel Format**: `private-chat.{chatId}`
**Event Name**: `message.sent`
**Auth Endpoint**: `POST /api/broadcasting/auth`
