# Broadcast / Real-time Notifications Integration (Laravel → Flutter)

This document explains how to configure Laravel broadcasting for notifications and how Flutter clients can subscribe to notifications via Pusher (or compatible) real-time channels. It also shows example payloads matching the backend notification structure.

**Why**: notifications are now broadcastable and stored in the database with a standardized payload (fields: `title`, `message`, `title_ar`, `message_ar`, `type`, `data`, `deep_link`, `timestamp`). The Flutter app can subscribe to private user channels and receive these payloads immediately.

---

## 1) Server (Laravel) - high level

-   Ensure broadcasting driver is configured in `.env` and `config/broadcasting.php`.
-   Typical values for Pusher:

```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
```

-   Laravel provides the `/broadcasting/auth` endpoint for authorizing private/Presence channels. If you use Sanctum, make sure the Flutter client includes the `Authorization: Bearer <token>` header when calling the auth endpoint.

-   Our notifications implement `toArray()` and `toBroadcast()` so the broadcast payload is the same structured object stored in the `notifications` database column.

## 2) Channel naming convention

-   For per-user private channels a common pattern is `private-user.{id}` or `private-users.{id}`. Example: `private-user.123`.
-   When sending a notification to a user (Notification::send or $user->notify), Laravel will broadcast the notification on the channels the notifiable defines (by default the private `App.Models.User.{id}` channel if you configure it). For simplicity you can subscribe to `private-user.{id}` from the Flutter app and ensure your notifications are broadcast on that channel.

## 3) Notification payload shape (example)

Backend `toArray()` returns this structure for database/broadcast:

```json
{
    "title": "Plan created",
    "message": "A new plan is available",
    "title_ar": "تم إنشاء خطة",
    "message_ar": "الخطة الجديدة أصبحت متاحة.",
    "type": "plan_created",
    "data": { "plan_id": 42, "plan_name": "6-week fat loss" },
    "deep_link": "app://plans/42",
    "timestamp": "2025-12-03 14:22:11"
}
```

When Laravel broadcasts a notification the event payload may be wrapped by the broadcast event structure. Example broadcasted event JSON (what the client may receive) can look like:

```json
{
    "notification": {
        "title": "تم إنشاء خطة",
        "message": "الخطة الجديدة أصبحت متاحة.",
        "title_ar": "تم إنشاء خطة",
        "message_ar": "الخطة الجديدة أصبحت متاحة.",
        "type": "plan_created",
        "data": { "plan_id": 42, "plan_name": "6-week fat loss" },
        "deep_link": "app://plans/42",
        "timestamp": "2025-12-03 14:22:11"
    }
}
```

Note: depending on the broadcast driver and client library, the event wrapper keys can vary. The Flutter app should inspect the received event and look for the standardized fields above.

## 4) Auth (private channels) and Sanctum

-   To authorize private channels Laravel calls `/broadcasting/auth` using the same session/cookie or token-based authentication used by your API.
-   If your Flutter app authenticates using Sanctum (token-based): include the `Authorization: Bearer <sanctum_token>` header for the auth request.
-   Example headers the Flutter client should send during Pusher auth:

```
Authorization: Bearer <TOKEN>
Accept: application/json
```

Make sure the `api` middleware or any middleware protecting broadcast auth accepts the token.

## 5) Example: Laravel server-side (reference)

-   In `config/broadcasting.php` set driver to `pusher` and fill `.env` vars.
-   In `routes/channels.php` you can define private channels:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

-   When you notify a user, Laravel will broadcast the notification to their private channel automatically if the notifiable implements the routeNotificationForBroadcast channel or the default was configured. Example: `$user->notify(new \App\Notifications\PlanCreatedNotification($payload));`

## 6) Flutter example (Pusher client)

This example uses a Pusher-compatible client in Flutter. You can use `pusher_client` or other Pusher libraries.

Dart pseudocode (using `pusher_client`):

```dart
import 'package:pusher_client/pusher_client.dart';

final String PUSHER_KEY = 'YOUR_PUSHER_KEY';
final String PUSHER_CLUSTER = 'YOUR_CLUSTER';
final String API_BASE = 'https://api.example.com';
final String SANCTUM_TOKEN = 'USER_SANCTUM_TOKEN';
final int userId = 123; // authenticated user id

void initPusher() {
  PusherOptions options = PusherOptions(cluster: PUSHER_CLUSTER, encrypted: true);

  // Provide auth options so /broadcasting/auth is called with Authorization header
  options.auth = PusherAuth(
    '$API_BASE/broadcasting/auth',
    headers: {
      'Authorization': 'Bearer $SANCTUM_TOKEN',
      'Accept': 'application/json',
    },
  );

  PusherClient pusher = PusherClient(
    PUSHER_KEY,
    options,
    autoConnect: true,
  );

  String channelName = 'private-user.$userId';
  Channel channel = pusher.subscribe(channelName);

  // Binding to notification event - several event names may be emitted
  channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (PusherEvent? event) {
    if (event == null) return;
    final raw = event.data; // raw is JSON string; parse it
    // parse JSON and extract our payload
    // Many drivers will wrap the notification in `notification` key

    // Example parsing:
    final Map payload = jsonDecode(raw);
    Map notif = payload['notification'] ?? payload;

    String titleAr = notif['title_ar'] ?? notif['title'];
    String messageAr = notif['message_ar'] ?? notif['message'];
    String type = notif['type'];
    Map data = notif['data'] ?? {};

    // Now handle notification in-app
    print('Received notification type: $type');
  });
}
```

Notes:

-   Some Pusher clients may provide the event's data already decoded; check the library docs.
-   Event name may be the fully-namespaced notification class or a generic broadcast event. If you don't receive events by the fully qualified name, try binding to `"App\\Notifications\\PlanCreatedNotification"` or listen to all events and inspect their payload.

## 7) Testing tips

-   Use the Pusher Debug Console to inspect events.
-   Use `php artisan tinker` to send a test notification to a user:

```php
$user = \App\Models\User::find(123);
$payload = ['type' => 'plan_created', 'plan_name' => 'Test Plan', 'data' => ['plan_id' => 42]];
$user->notify(new \App\Notifications\PlanCreatedNotification($payload));
```

-   Ensure `BROADCAST_DRIVER` is set to `pusher` and broadcasting service credentials are valid.
-   If using a local development server without Pusher, consider using `laravel-websockets` package and point Pusher config to your local websockets server.

## 8) Summary quick checklist for Flutter engineers

-   [ ] Confirm API provides a logged-in token (Sanctum) usable for `/broadcasting/auth`.
-   [ ] Configure Flutter Pusher client with `auth` headers including `Authorization: Bearer <token>`.
-   [ ] Subscribe to `private-user.{id}` channel.
-   [ ] Bind to the relevant event(s) (try `BroadcastNotificationCreated` wrapper and/or the notification FQCN).
-   [ ] Inspect event payload and extract the standardized fields: `title`, `message`, `title_ar`, `message_ar`, `type`, `data`, `deep_link`, `timestamp`.

---

If you want, I can also add a small Laravel controller endpoint example to help with local auth testing, or a short Flutter snippet using a concrete package (`pusher_client`) tailored to your Flutter stack.
