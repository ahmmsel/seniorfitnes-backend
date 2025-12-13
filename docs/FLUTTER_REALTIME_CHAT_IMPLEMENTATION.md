# Flutter Real-Time Chat Implementation with Pusher

## 🎯 Overview

Add real-time messaging capabilities to your existing Flutter chat implementation using Pusher WebSockets with Laravel Broadcasting.

---

## 📦 Step 1: Add Dependencies

Add to `pubspec.yaml`:

```yaml
dependencies:
    pusher_channels_flutter: ^2.2.1
    http: ^1.1.0
```

Run:

```bash
flutter pub get
```

---

## 🔧 Step 2: Create Pusher Service

Create `lib/services/pusher_service.dart`:

```dart
import 'dart:async';
import 'dart:convert';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:http/http.dart' as http;

class PusherService {
  static final PusherService _instance = PusherService._internal();
  factory PusherService() => _instance;
  PusherService._internal();

  PusherChannelsFlutter? _pusher;
  final Map<String, StreamController<Map<String, dynamic>>> _channelControllers = {};

  // Your API configuration
  static const String apiBaseUrl = 'https://your-api.com';
  static const String pusherKey = 'YOUR_PUSHER_APP_KEY';
  static const String pusherCluster = 'eu'; // or your cluster

  String? _authToken;
  bool _isInitialized = false;

  /// Initialize Pusher with user authentication token
  Future<void> initialize(String authToken) async {
    if (_isInitialized) return;

    _authToken = authToken;
    _pusher = PusherChannelsFlutter.getInstance();

    try {
      await _pusher!.init(
        apiKey: pusherKey,
        cluster: pusherCluster,
        onConnectionStateChange: _onConnectionStateChange,
        onError: _onError,
        onSubscriptionSucceeded: _onSubscriptionSucceeded,
        onEvent: _onEvent,
        onSubscriptionError: _onSubscriptionError,
        onDecryptionFailure: _onDecryptionFailure,
        onMemberAdded: _onMemberAdded,
        onMemberRemoved: _onMemberRemoved,
        onAuthorizer: _onAuthorizer,
      );

      await _pusher!.connect();
      _isInitialized = true;
      print('✅ Pusher connected successfully');
    } catch (e) {
      print('❌ Pusher initialization failed: $e');
      rethrow;
    }
  }

  /// Authorize private channels with Laravel backend
  Future<dynamic> _onAuthorizer(
    String channelName,
    String socketId,
    dynamic options,
  ) async {
    if (_authToken == null) {
      throw Exception('Auth token not set');
    }

    try {
      final response = await http.post(
        Uri.parse('$apiBaseUrl/api/broadcasting/auth'),
        headers: {
          'Authorization': 'Bearer $_authToken',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'socket_id': socketId,
          'channel_name': channelName,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Authorization failed: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Pusher auth error: $e');
      rethrow;
    }
  }

  /// Subscribe to a chat channel
  Future<Stream<Map<String, dynamic>>> subscribeToChat(int chatId) async {
    final channelName = 'private-chat.$chatId';

    if (_channelControllers.containsKey(channelName)) {
      return _channelControllers[channelName]!.stream;
    }

    final controller = StreamController<Map<String, dynamic>>.broadcast();
    _channelControllers[channelName] = controller;

    try {
      await _pusher!.subscribe(channelName: channelName);
      print('✅ Subscribed to $channelName');
    } catch (e) {
      print('❌ Failed to subscribe to $channelName: $e');
      controller.addError(e);
    }

    return controller.stream;
  }

  /// Unsubscribe from a chat channel
  Future<void> unsubscribeFromChat(int chatId) async {
    final channelName = 'private-chat.$chatId';

    try {
      await _pusher!.unsubscribe(channelName: channelName);
      _channelControllers[channelName]?.close();
      _channelControllers.remove(channelName);
      print('✅ Unsubscribed from $channelName');
    } catch (e) {
      print('❌ Failed to unsubscribe from $channelName: $e');
    }
  }

  /// Handle incoming events
  void _onEvent(PusherEvent event) {
    final channelName = event.channelName;
    final eventName = event.eventName;

    print('📨 Received event: $eventName on $channelName');

    if (eventName == 'message.sent' && _channelControllers.containsKey(channelName)) {
      try {
        final data = jsonDecode(event.data);
        _channelControllers[channelName]!.add({
          'event': eventName,
          'data': data,
        });
      } catch (e) {
        print('❌ Error parsing event data: $e');
      }
    }
  }

  /// Connection state changes
  void _onConnectionStateChange(dynamic currentState, dynamic previousState) {
    print('🔌 Pusher connection: $previousState -> $currentState');
  }

  /// Subscription succeeded
  void _onSubscriptionSucceeded(String channelName, dynamic data) {
    print('✅ Subscription succeeded: $channelName');
  }

  /// Handle errors
  void _onError(String message, int? code, dynamic e) {
    print('❌ Pusher error: $message (code: $code)');
  }

  /// Subscription error
  void _onSubscriptionError(String message, dynamic e) {
    print('❌ Subscription error: $message');
  }

  /// Decryption failure
  void _onDecryptionFailure(String event, String reason) {
    print('❌ Decryption failure: $event - $reason');
  }

  /// Member added (for presence channels)
  void _onMemberAdded(String channelName, PusherMember member) {
    print('👤 Member added to $channelName: ${member.userId}');
  }

  /// Member removed (for presence channels)
  void _onMemberRemoved(String channelName, PusherMember member) {
    print('👤 Member removed from $channelName: ${member.userId}');
  }

  /// Disconnect and cleanup
  Future<void> disconnect() async {
    try {
      for (var controller in _channelControllers.values) {
        await controller.close();
      }
      _channelControllers.clear();

      await _pusher?.disconnect();
      _isInitialized = false;
      print('✅ Pusher disconnected');
    } catch (e) {
      print('❌ Error disconnecting Pusher: $e');
    }
  }

  /// Check if connected
  bool get isConnected => _isInitialized;
}
```

---

## 💬 Step 3: Update Chat Screen

Update your existing chat screen to integrate real-time messaging:

```dart
import 'package:flutter/material.dart';
import '../services/pusher_service.dart';
import '../services/chat_service.dart'; // Your existing chat service
import '../models/message.dart';
import 'dart:async';

class ChatScreen extends StatefulWidget {
  final int chatId;
  final String otherUserName;
  final String? otherUserImage;

  const ChatScreen({
    Key? key,
    required this.chatId,
    required this.otherUserName,
    this.otherUserImage,
  }) : super(key: key);

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final PusherService _pusherService = PusherService();
  final ChatService _chatService = ChatService(); // Your existing service
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  List<Message> _messages = [];
  StreamSubscription? _pusherSubscription;
  bool _isLoading = true;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    _initializeChat();
  }

  Future<void> _initializeChat() async {
    // Load existing messages from API
    await _loadMessages();

    // Subscribe to real-time updates
    await _subscribeToRealtime();
  }

  Future<void> _loadMessages() async {
    try {
      final messages = await _chatService.getMessages(widget.chatId);
      setState(() {
        _messages = messages;
        _isLoading = false;
      });
      _scrollToBottom();
    } catch (e) {
      print('Error loading messages: $e');
      setState(() => _isLoading = false);
    }
  }

  Future<void> _subscribeToRealtime() async {
    try {
      final stream = await _pusherService.subscribeToChat(widget.chatId);

      _pusherSubscription = stream.listen((event) {
        if (event['event'] == 'message.sent') {
          _handleNewMessage(event['data']);
        }
      });
    } catch (e) {
      print('Error subscribing to Pusher: $e');
    }
  }

  void _handleNewMessage(Map<String, dynamic> data) {
    final message = Message.fromRealtimeEvent(data);

    setState(() {
      _messages.add(message);
    });

    _scrollToBottom();

    // Mark as read if not from current user
    if (!message.isMine) {
      _chatService.markAsRead(widget.chatId);
    }
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending) return;

    setState(() => _isSending = true);
    _messageController.clear();

    try {
      // Send message via API
      final message = await _chatService.sendMessage(
        widget.chatId,
        text,
      );

      // Add to local list immediately for instant feedback
      setState(() {
        _messages.add(message);
      });

      _scrollToBottom();
    } catch (e) {
      print('Error sending message: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to send message')),
      );
    } finally {
      setState(() => _isSending = false);
    }
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      Future.delayed(Duration(milliseconds: 100), () {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      });
    }
  }

  @override
  void dispose() {
    _pusherSubscription?.cancel();
    _pusherService.unsubscribeFromChat(widget.chatId);
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            if (widget.otherUserImage != null)
              CircleAvatar(
                backgroundImage: NetworkImage(widget.otherUserImage!),
                radius: 16,
              ),
            SizedBox(width: 8),
            Text(widget.otherUserName),
          ],
        ),
      ),
      body: Column(
        children: [
          // Messages list
          Expanded(
            child: _isLoading
                ? Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollController,
                    padding: EdgeInsets.all(16),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final message = _messages[index];
                      return _buildMessageBubble(message);
                    },
                  ),
          ),

          // Message input
          _buildMessageInput(),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(Message message) {
    final isMine = message.isMine;

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.only(bottom: 12),
        padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.7,
        ),
        decoration: BoxDecoration(
          color: isMine ? Colors.blue : Colors.grey[300],
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!isMine && message.senderName != null)
              Text(
                message.senderName!,
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                  color: Colors.black87,
                ),
              ),
            SizedBox(height: 4),
            Text(
              message.message,
              style: TextStyle(
                color: isMine ? Colors.white : Colors.black87,
              ),
            ),
            SizedBox(height: 4),
            Text(
              _formatTime(message.createdAt),
              style: TextStyle(
                fontSize: 10,
                color: isMine ? Colors.white70 : Colors.black54,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageInput() {
    return Container(
      padding: EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 4,
            offset: Offset(0, -2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _messageController,
              decoration: InputDecoration(
                hintText: 'Type a message...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 10,
                ),
              ),
              onSubmitted: (_) => _sendMessage(),
            ),
          ),
          SizedBox(width: 8),
          IconButton(
            icon: _isSending
                ? SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : Icon(Icons.send),
            onPressed: _isSending ? null : _sendMessage,
            color: Colors.blue,
          ),
        ],
      ),
    );
  }

  String _formatTime(DateTime dateTime) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final messageDate = DateTime(dateTime.year, dateTime.month, dateTime.day);

    if (messageDate == today) {
      return '${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
    } else {
      return '${dateTime.day}/${dateTime.month} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
    }
  }
}
```

---

## 📋 Step 4: Update Message Model

Add support for real-time event parsing:

```dart
class Message {
  final int id;
  final int chatId;
  final String message;
  final int senderId;
  final String senderType;
  final String? senderName;
  final String? senderAvatar;
  final String? senderProfileImageUrl;
  final bool isMine;
  final DateTime? readAt;
  final DateTime createdAt;

  Message({
    required this.id,
    required this.chatId,
    required this.message,
    required this.senderId,
    required this.senderType,
    this.senderName,
    this.senderAvatar,
    this.senderProfileImageUrl,
    required this.isMine,
    this.readAt,
    required this.createdAt,
  });

  /// Parse from API response
  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'],
      chatId: json['chat_id'],
      message: json['message'] ?? '',
      senderId: json['sender_id'],
      senderType: json['sender_type'],
      senderName: json['sender']?['name'],
      senderAvatar: json['sender']?['avatar'],
      senderProfileImageUrl: json['sender']?['profile_image_url'],
      isMine: json['is_mine'] ?? false,
      readAt: json['read_at'] != null ? DateTime.parse(json['read_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  /// Parse from Pusher real-time event
  factory Message.fromRealtimeEvent(Map<String, dynamic> data) {
    return Message(
      id: data['message_id'],
      chatId: data['chat_id'],
      message: data['message'] ?? '',
      senderId: data['sender_id'],
      senderType: data['sender_type'],
      senderName: data['sender']?['name'],
      senderAvatar: data['sender']?['avatar'],
      senderProfileImageUrl: data['sender']?['profile_image_url'],
      isMine: false, // Real-time messages are from others
      readAt: data['read_at'] != null ? DateTime.parse(data['read_at']) : null,
      createdAt: DateTime.parse(data['created_at']),
    );
  }
}
```

---

## 🚀 Step 5: Initialize on App Start

In your `main.dart` or authentication flow:

```dart
import 'services/pusher_service.dart';

// After successful login
final authToken = 'user_sanctum_token';
await PusherService().initialize(authToken);

// On logout
await PusherService().disconnect();
```

---

## 🔐 Step 6: Configuration

Update `lib/services/pusher_service.dart` constants:

```dart
static const String apiBaseUrl = 'https://192.168.1.8:8000'; // Your API URL
static const String pusherKey = 'YOUR_PUSHER_APP_KEY';
static const String pusherCluster = 'eu'; // Your Pusher cluster
```

Get these values from:

-   **Pusher Dashboard**: https://dashboard.pusher.com/
-   **Laravel .env**: `PUSHER_APP_KEY` and `PUSHER_APP_CLUSTER`

---

## 📱 Real-Time Event Format

When a message is sent, your Flutter app receives:

```json
{
    "event": "message.sent",
    "data": {
        "message_id": 34,
        "chat_id": 2,
        "message": "Hello there!",
        "sender_id": 2,
        "sender_type": "coach",
        "sender": {
            "id": 2,
            "name": "John Coach",
            "avatar": null,
            "profile_image_url": "https://api.com/storage/profile.jpg"
        },
        "read_at": null,
        "created_at": "2025-12-11T10:30:00.000000Z",
        "updated_at": "2025-12-11T10:30:00.000000Z"
    }
}
```

---

## ✅ Testing Checklist

-   [ ] Add `pusher_channels_flutter` dependency
-   [ ] Create `PusherService` with auth handling
-   [ ] Update chat screen to subscribe to channels
-   [ ] Handle incoming `message.sent` events
-   [ ] Add messages to UI when received
-   [ ] Mark messages as read automatically
-   [ ] Handle connection errors gracefully
-   [ ] Unsubscribe when leaving chat
-   [ ] Disconnect Pusher on logout
-   [ ] Test with multiple devices/users

---

## 🐛 Common Issues

### 1. Authorization Failed (403)

-   Check Bearer token is valid
-   Verify user is participant in chat
-   Check `auth:sanctum` middleware is active

### 2. Connection Failed

-   Verify Pusher credentials
-   Check cluster matches your Pusher app
-   Ensure internet connectivity

### 3. Messages Not Appearing

-   Check channel name format: `private-chat.{id}`
-   Verify event name: `message.sent`
-   Check Pusher debug console

### 4. Double Messages

-   Don't add message both on send AND from Pusher
-   Use `toOthers()` on backend (already implemented)

---

## 🎯 Expected Behavior

1. **User A sends message**:

    - Message saved to database
    - API returns message immediately
    - User A sees message instantly (local)
    - User B receives via Pusher real-time

2. **User B receives message**:

    - Pusher event triggers
    - Message added to UI
    - Auto-scrolls to bottom
    - Marked as read automatically

3. **Offline Users**:
    - Messages stored in database
    - Retrieved on next chat load
    - Real-time resumes when online

---

## 🚀 Your Implementation Is Ready!

Backend already configured with:

-   ✅ `POST /api/broadcasting/auth` endpoint
-   ✅ Private channel authorization
-   ✅ `MessageSent` event with `message.sent`
-   ✅ Channel format: `private-chat.{chatId}`
-   ✅ Profile images included
-   ✅ Sender excluded via `toOthers()`

Just implement the Flutter code above and you're done! 🎉
