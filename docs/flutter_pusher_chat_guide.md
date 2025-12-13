# Flutter Pusher Chat Integration Guide

## Overview

This guide provides complete implementation details for integrating Pusher WebSocket real-time chat functionality in Flutter apps with the SuniorFit backend.

## Dependencies

Add to your `pubspec.yaml`:

```yaml
dependencies:
    pusher_channels_flutter: ^2.0.0
    http: ^1.1.0
    shared_preferences: ^2.2.2
```

## 1. Pusher Configuration

### Environment Configuration

```dart
// lib/config/pusher_config.dart
class PusherConfig {
  static const String appKey = 'f1c2d6addd8373ab72d0';
  static const String cluster = 'eu';
  static const String authEndpoint = 'https://192.168.1.8:8000/api/broadcasting/auth';

  // Channel naming patterns
  static String chatChannel(int chatId) => 'private-chat.$chatId';
  static String userChannel(int userId) => 'private-user.$userId';
  static String workoutChannel(int workoutId) => 'presence-workout.$workoutId';

  // Event naming patterns
  static const String messageCreated = 'message.created';
  static const String messageUpdated = 'message.updated';
  static const String userStatusChanged = 'user.status_changed';
  static const String notificationReceived = 'notification.received';
}
```

## 2. Pusher Service Implementation

```dart
// lib/services/pusher_service.dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/pusher_config.dart';

class PusherService {
  static final PusherService _instance = PusherService._internal();
  factory PusherService() => _instance;
  PusherService._internal();

  PusherChannelsFlutter? _pusher;
  final Map<String, Channel> _channels = {};
  bool _isConnected = false;
  String? _userToken;

  Future<void> initialize(String userToken) async {
    _userToken = userToken;

    _pusher = PusherChannelsFlutter.getInstance();

    await _pusher!.init(
      apiKey: PusherConfig.appKey,
      cluster: PusherConfig.cluster,
      onConnectionStateChange: _handleConnectionStateChange,
      onError: _handleError,
      onSubscriptionSucceeded: _handleSubscriptionSucceeded,
      onEvent: _handleEvent,
      onSubscriptionError: _handleSubscriptionError,
      onDecryptionFailure: _handleDecryptionFailure,
      onMemberAdded: _handleMemberAdded,
      onMemberRemoved: _handleMemberRemoved,
      onAuthorizer: _handleAuthorizer,
    );
  }

  Future<void> connect() async {
    if (_pusher != null && !_isConnected) {
      await _pusher!.connect();
    }
  }

  Future<void> disconnect() async {
    if (_pusher != null && _isConnected) {
      // Unsubscribe from all channels
      for (String channelName in _channels.keys) {
        await unsubscribeFromChannel(channelName);
      }
      await _pusher!.disconnect();
    }
  }

  // Subscribe to chat channel for real-time messages
  Future<Channel?> subscribeToChatChannel(
    int chatId,
    Function(PusherEvent) onMessageCreated,
    {Function(PusherEvent)? onMessageUpdated}
  ) async {
    final channelName = PusherConfig.chatChannel(chatId);

    try {
      final channel = await _pusher!.subscribe(channelName: channelName);
      _channels[channelName] = channel;

      // Bind to message events
      await channel.bind(
        eventName: PusherConfig.messageCreated,
        callback: onMessageCreated,
      );

      if (onMessageUpdated != null) {
        await channel.bind(
          eventName: PusherConfig.messageUpdated,
          callback: onMessageUpdated,
        );
      }

      print('✅ Subscribed to chat channel: $channelName');
      return channel;
    } catch (e) {
      print('❌ Failed to subscribe to chat channel: $e');
      return null;
    }
  }

  // Subscribe to user channel for notifications
  Future<Channel?> subscribeToUserChannel(
    int userId,
    Function(PusherEvent) onNotification,
    {Function(PusherEvent)? onStatusChanged}
  ) async {
    final channelName = PusherConfig.userChannel(userId);

    try {
      final channel = await _pusher!.subscribe(channelName: channelName);
      _channels[channelName] = channel;

      await channel.bind(
        eventName: PusherConfig.notificationReceived,
        callback: onNotification,
      );

      if (onStatusChanged != null) {
        await channel.bind(
          eventName: PusherConfig.userStatusChanged,
          callback: onStatusChanged,
        );
      }

      print('✅ Subscribed to user channel: $channelName');
      return channel;
    } catch (e) {
      print('❌ Failed to subscribe to user channel: $e');
      return null;
    }
  }

  Future<void> unsubscribeFromChannel(String channelName) async {
    try {
      await _pusher!.unsubscribe(channelName: channelName);
      _channels.remove(channelName);
      print('✅ Unsubscribed from channel: $channelName');
    } catch (e) {
      print('❌ Failed to unsubscribe from channel: $e');
    }
  }

  // Event Handlers
  void _handleConnectionStateChange(String currentState, String previousState) {
    print('Pusher connection: $previousState -> $currentState');
    _isConnected = currentState == 'CONNECTED';
  }

  void _handleError(String message, int? code, dynamic e) {
    print('Pusher error: $message (Code: $code)');
  }

  void _handleEvent(PusherEvent event) {
    print('Pusher event received: ${event.eventName} on ${event.channelName}');
  }

  void _handleSubscriptionSucceeded(String channelName, dynamic data) {
    print('✅ Subscription succeeded: $channelName');
  }

  void _handleSubscriptionError(String message, dynamic e) {
    print('❌ Subscription error: $message');
  }

  void _handleDecryptionFailure(String event, String reason) {
    print('❌ Decryption failed: $event - $reason');
  }

  void _handleMemberAdded(String channelName, PusherMember member) {
    print('👤 Member added to $channelName: ${member.userInfo}');
  }

  void _handleMemberRemoved(String channelName, PusherMember member) {
    print('👤 Member removed from $channelName: ${member.userInfo}');
  }

  // Authorization for private channels
  dynamic _handleAuthorizer(String channelName, String socketId, dynamic options) {
    return {
      'Authorization': 'Bearer $_userToken',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  bool get isConnected => _isConnected;
  List<String> get subscribedChannels => _channels.keys.toList();
}
```

## 3. Chat Message Model

```dart
// lib/models/chat_message.dart
class ChatMessage {
  final int id;
  final String body;
  final Map<String, dynamic>? data;
  final ChatSender sender;
  final bool isMine;
  final DateTime? readAt;
  final DateTime createdAt;

  ChatMessage({
    required this.id,
    required this.body,
    this.data,
    required this.sender,
    required this.isMine,
    this.readAt,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'],
      body: json['body'] ?? '',
      data: json['data'],
      sender: ChatSender.fromJson(json['sender']),
      isMine: json['is_mine'] ?? false,
      readAt: json['read_at'] != null ? DateTime.parse(json['read_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  // Create from Pusher event data
  factory ChatMessage.fromPusherEvent(Map<String, dynamic> eventData) {
    final messageData = eventData['data']['message'];
    final senderData = eventData['data']['sender'];

    return ChatMessage(
      id: messageData['id'],
      body: messageData['body'] ?? '',
      data: messageData['data'],
      sender: ChatSender.fromJson(senderData),
      isMine: false, // Will be determined by comparing with current user
      readAt: messageData['read_at'] != null ? DateTime.parse(messageData['read_at']) : null,
      createdAt: DateTime.parse(messageData['created_at']),
    );
  }
}

class ChatSender {
  final int id;
  final String name;
  final String type;

  ChatSender({
    required this.id,
    required this.name,
    required this.type,
  });

  factory ChatSender.fromJson(Map<String, dynamic> json) {
    return ChatSender(
      id: json['id'],
      name: json['name'],
      type: json['type'],
    );
  }
}
```

## 4. Chat Screen Implementation

```dart
// lib/screens/chat_screen.dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import '../services/pusher_service.dart';
import '../models/chat_message.dart';
import '../services/api_service.dart';

class ChatScreen extends StatefulWidget {
  final int chatId;
  final int currentUserId;
  final String authToken;

  const ChatScreen({
    Key? key,
    required this.chatId,
    required this.currentUserId,
    required this.authToken,
  }) : super(key: key);

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final PusherService _pusherService = PusherService();
  final TextEditingController _messageController = TextEditingController();
  final List<ChatMessage> _messages = [];
  final ScrollController _scrollController = ScrollController();

  bool _isLoading = true;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    _initializeChat();
  }

  Future<void> _initializeChat() async {
    try {
      // Initialize Pusher if not already done
      if (!_pusherService.isConnected) {
        await _pusherService.initialize(widget.authToken);
        await _pusherService.connect();
      }

      // Load existing messages
      await _loadMessages();

      // Subscribe to real-time updates
      await _subscribeToChat();

      setState(() {
        _isLoading = false;
      });
    } catch (e) {
      print('Error initializing chat: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _loadMessages() async {
    try {
      final response = await ApiService.getChatMessages(
        widget.chatId,
        token: widget.authToken,
      );

      if (response['status'] == 'success') {
        final messagesList = response['data']['messages'] as List;
        final messages = messagesList
            .map((json) => ChatMessage.fromJson(json))
            .toList();

        setState(() {
          _messages.clear();
          _messages.addAll(messages.reversed); // Reverse for correct order
        });

        _scrollToBottom();
      }
    } catch (e) {
      print('Error loading messages: $e');
    }
  }

  Future<void> _subscribeToChat() async {
    await _pusherService.subscribeToChatChannel(
      widget.chatId,
      _handleNewMessage,
      onMessageUpdated: _handleMessageUpdated,
    );
  }

  void _handleNewMessage(PusherEvent event) {
    try {
      final eventData = jsonDecode(event.data);
      final message = ChatMessage.fromPusherEvent(eventData);

      // Determine if message is mine
      final isMyMessage = message.sender.id == widget.currentUserId;
      final updatedMessage = ChatMessage(
        id: message.id,
        body: message.body,
        data: message.data,
        sender: message.sender,
        isMine: isMyMessage,
        readAt: message.readAt,
        createdAt: message.createdAt,
      );

      setState(() {
        _messages.add(updatedMessage);
      });

      _scrollToBottom();

      // Mark as read if not my message
      if (!isMyMessage) {
        _markChatAsRead();
      }

      print('✅ New message received: ${message.body}');
    } catch (e) {
      print('❌ Error handling new message: $e');
    }
  }

  void _handleMessageUpdated(PusherEvent event) {
    try {
      final eventData = jsonDecode(event.data);
      final updatedMessage = ChatMessage.fromPusherEvent(eventData);

      setState(() {
        final index = _messages.indexWhere((msg) => msg.id == updatedMessage.id);
        if (index != -1) {
          _messages[index] = updatedMessage;
        }
      });

      print('✅ Message updated: ${updatedMessage.id}');
    } catch (e) {
      print('❌ Error handling message update: $e');
    }
  }

  Future<void> _sendMessage() async {
    final messageText = _messageController.text.trim();
    if (messageText.isEmpty || _isSending) return;

    setState(() {
      _isSending = true;
    });

    try {
      final response = await ApiService.sendMessage(
        widget.chatId,
        messageText,
        token: widget.authToken,
      );

      if (response['status'] == 'success') {
        _messageController.clear();
        // Message will be added via Pusher event
      } else {
        _showErrorSnackBar('Failed to send message');
      }
    } catch (e) {
      print('Error sending message: $e');
      _showErrorSnackBar('Failed to send message');
    } finally {
      setState(() {
        _isSending = false;
      });
    }
  }

  Future<void> _markChatAsRead() async {
    try {
      await ApiService.markChatAsRead(
        widget.chatId,
        token: widget.authToken,
      );
    } catch (e) {
      print('Error marking chat as read: $e');
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  void dispose() {
    // Unsubscribe from chat channel
    final channelName = PusherConfig.chatChannel(widget.chatId);
    _pusherService.unsubscribeFromChannel(channelName);

    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Chat'),
        actions: [
          IconButton(
            icon: Icon(
              _pusherService.isConnected
                ? Icons.wifi
                : Icons.wifi_off,
              color: _pusherService.isConnected
                ? Colors.green
                : Colors.red,
            ),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    _pusherService.isConnected
                      ? 'Connected to real-time chat'
                      : 'Disconnected from real-time chat',
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                return MessageBubble(
                  message: _messages[index],
                );
              },
            ),
          ),
          MessageInput(
            controller: _messageController,
            onSend: _sendMessage,
            isSending: _isSending,
          ),
        ],
      ),
    );
  }
}

// Message Bubble Widget
class MessageBubble extends StatelessWidget {
  final ChatMessage message;

  const MessageBubble({Key? key, required this.message}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: message.isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: message.isMine ? Colors.blue : Colors.grey[300],
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!message.isMine)
              Text(
                message.sender.name,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                  fontWeight: FontWeight.bold,
                ),
              ),
            Text(
              message.body,
              style: TextStyle(
                color: message.isMine ? Colors.white : Colors.black87,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _formatTime(message.createdAt),
              style: TextStyle(
                fontSize: 10,
                color: message.isMine ? Colors.white70 : Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime dateTime) {
    return '${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}

// Message Input Widget
class MessageInput extends StatelessWidget {
  final TextEditingController controller;
  final VoidCallback onSend;
  final bool isSending;

  const MessageInput({
    Key? key,
    required this.controller,
    required this.onSend,
    required this.isSending,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 3,
            offset: const Offset(0, -1),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: controller,
              decoration: const InputDecoration(
                hintText: 'Type a message...',
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              onSubmitted: (_) => onSend(),
            ),
          ),
          const SizedBox(width: 8),
          IconButton(
            onPressed: isSending ? null : onSend,
            icon: isSending
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : const Icon(Icons.send),
          ),
        ],
      ),
    );
  }
}
```

## 5. API Service Helper

```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'https://192.168.1.8:8000/api';

  static Future<Map<String, dynamic>> getChatMessages(
    int chatId, {
    required String token,
    int page = 1,
    int perPage = 50,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl/chats/$chatId?page=$page&per_page=$perPage'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    );

    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> sendMessage(
    int chatId,
    String message, {
    required String token,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/chats/$chatId/message'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'message': message,
      }),
    );

    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> markChatAsRead(
    int chatId, {
    required String token,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/chats/$chatId/read'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    );

    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getChats({
    required String token,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl/chats'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    );

    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> startChat(
    int participantId,
    String participantType, {
    required String token,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/chats/start'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'participant_id': participantId,
        'participant_type': participantType,
      }),
    );

    return jsonDecode(response.body);
  }
}
```

## 6. Usage in Main App

```dart
// lib/main.dart
void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: ChatListScreen(),
    );
  }
}

// Initialize Pusher when user logs in
class AuthService {
  static Future<void> initializePusherAfterLogin(String token) async {
    final pusherService = PusherService();
    await pusherService.initialize(token);
    await pusherService.connect();
  }

  static Future<void> disconnectPusher() async {
    final pusherService = PusherService();
    await pusherService.disconnect();
  }
}
```

## 7. Error Handling & Best Practices

### Connection Management

```dart
// Monitor connection status
class ConnectionMonitor {
  static void handleConnectionChange(String state) {
    switch (state) {
      case 'CONNECTED':
        // Show success indicator
        print('✅ Real-time chat connected');
        break;
      case 'DISCONNECTED':
        // Show offline indicator
        print('❌ Real-time chat disconnected');
        break;
      case 'RECONNECTING':
        // Show reconnecting indicator
        print('🔄 Reconnecting to chat...');
        break;
    }
  }
}
```

### Memory Management

-   Always unsubscribe from channels when leaving screens
-   Disconnect Pusher when user logs out
-   Use singleton pattern for PusherService to avoid multiple instances

### Error Recovery

```dart
// Retry mechanism for failed subscriptions
Future<void> retrySubscription(int chatId) async {
  int attempts = 0;
  const maxAttempts = 3;

  while (attempts < maxAttempts) {
    try {
      await _pusherService.subscribeToChatChannel(chatId, _handleNewMessage);
      break;
    } catch (e) {
      attempts++;
      if (attempts < maxAttempts) {
        await Future.delayed(Duration(seconds: attempts * 2));
      } else {
        // Show error to user
        print('Failed to connect to real-time chat after $maxAttempts attempts');
      }
    }
  }
}
```

## 8. Testing

### Test Pusher Connection

```dart
Future<void> testPusherConnection() async {
  try {
    final response = await http.get(
      Uri.parse('$baseUrl/broadcast/test'),
      headers: {'Authorization': 'Bearer $token'},
    );

    if (response.statusCode == 200) {
      print('✅ Pusher test endpoint working');
    }
  } catch (e) {
    print('❌ Pusher test failed: $e');
  }
}
```

This complete implementation provides:

-   ✅ **Real-time message delivery** via Pusher WebSocket
-   ✅ **Automatic reconnection** handling
-   ✅ **Proper channel management** with subscribe/unsubscribe
-   ✅ **Message ordering** and display
-   ✅ **Read status** tracking
-   ✅ **Error handling** and recovery
-   ✅ **Memory management** and cleanup
-   ✅ **Connection status** indicators
-   ✅ **API integration** with SuniorFit backend

The implementation follows Flutter best practices and provides a smooth real-time chat experience for users.
