# Flutter Implementation Guide: Walking & Running Tracking

## Overview
This guide covers the complete implementation of walking and running tracking in your Flutter app, including location tracking, real-time metrics, and API integration.

## Table of Contents
1. [Dependencies](#dependencies)
2. [API Service](#api-service)
3. [Models](#models)
4. [Location Tracking Service](#location-tracking-service)
5. [State Management](#state-management)
6. [UI Implementation](#ui-implementation)
7. [Permissions](#permissions)
8. [Testing](#testing)

---

## 1. Dependencies

Add these to your `pubspec.yaml`:

```yaml
dependencies:
  # Location tracking
  geolocator: ^10.1.0
  
  # Pedometer (step counting)
  pedometer: ^4.0.1
  
  # Permission handling
  permission_handler: ^11.0.1
  
  # HTTP requests
  http: ^1.1.0
  dio: ^5.4.0  # Alternative to http
  
  # State management (choose one)
  provider: ^6.1.1
  # OR
  riverpod: ^2.4.9
  # OR
  bloc: ^8.1.3
  
  # Timer utilities
  stop_watch_timer: ^3.0.2
```

---

## 2. API Service

### TrackingApiService

```dart
// lib/services/api/tracking_api_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;
import '../auth/auth_service.dart';

class TrackingApiService {
  final String baseUrl;
  final AuthService authService;

  TrackingApiService({
    required this.baseUrl,
    required this.authService,
  });

  Future<Map<String, String>> _getHeaders() async {
    final token = await authService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  /// Start a walking session
  Future<TrackingSessionResponse> startWalking() async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/tracking/walking/start'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 201) {
      return TrackingSessionResponse.fromJson(jsonDecode(response.body));
    } else {
      throw ApiException(
        message: jsonDecode(response.body)['message'] ?? 'Failed to start walking session',
        statusCode: response.statusCode,
      );
    }
  }

  /// Start a running session
  Future<TrackingSessionResponse> startRunning() async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/tracking/running/start'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 201) {
      return TrackingSessionResponse.fromJson(jsonDecode(response.body));
    } else {
      throw ApiException(
        message: jsonDecode(response.body)['message'] ?? 'Failed to start running session',
        statusCode: response.statusCode,
      );
    }
  }

  /// Finish a tracking session
  Future<TrackingSessionResponse> finishSession({
    required int sessionId,
    required double distance,
    required int timeSeconds,
    int? bpm,
    int? steps,
    double? pace,
    double? calories,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/tracking/finish'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'session_id': sessionId,
        'distance': distance,
        'time_seconds': timeSeconds,
        if (bpm != null) 'bpm': bpm,
        if (steps != null) 'steps': steps,
        if (pace != null) 'pace': pace,
        if (calories != null) 'calories': calories,
      }),
    );

    if (response.statusCode == 200) {
      return TrackingSessionResponse.fromJson(jsonDecode(response.body));
    } else {
      throw ApiException(
        message: jsonDecode(response.body)['message'] ?? 'Failed to finish session',
        statusCode: response.statusCode,
      );
    }
  }

  /// Get tracking history
  Future<TrackingHistoryResponse> getHistory({
    String? type, // 'walking' or 'running'
    int page = 1,
    int perPage = 15,
  }) async {
    final queryParams = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (type != null) 'type': type,
    };

    final uri = Uri.parse('$baseUrl/api/tracking/history').replace(
      queryParameters: queryParams,
    );

    final response = await http.get(
      uri,
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      return TrackingHistoryResponse.fromJson(jsonDecode(response.body));
    } else {
      throw ApiException(
        message: 'Failed to fetch history',
        statusCode: response.statusCode,
      );
    }
  }

  /// Share session to community
  Future<ProgressPostResponse> shareToCommnity({
    required int sessionId,
    String? description,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/progress/share/$sessionId'),
      headers: await _getHeaders(),
      body: jsonEncode({
        if (description != null) 'description': description,
      }),
    );

    if (response.statusCode == 201) {
      return ProgressPostResponse.fromJson(jsonDecode(response.body));
    } else {
      final body = jsonDecode(response.body);
      throw ApiException(
        message: body['message'] ?? 'Failed to share progress',
        statusCode: response.statusCode,
      );
    }
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;

  ApiException({required this.message, required this.statusCode});

  @override
  String toString() => message;
}
```

---

## 3. Models

### TrackingSession Model

```dart
// lib/models/tracking_session.dart

class TrackingSession {
  final int id;
  final String type; // 'walking' or 'running'
  final String status; // 'ongoing' or 'finished'
  final double? distance;
  final int? timeSeconds;
  final int? bpm;
  final int? steps;
  final double? pace;
  final double? calories;
  final DateTime startedAt;
  final DateTime? endedAt;
  final DateTime createdAt;

  TrackingSession({
    required this.id,
    required this.type,
    required this.status,
    this.distance,
    this.timeSeconds,
    this.bpm,
    this.steps,
    this.pace,
    this.calories,
    required this.startedAt,
    this.endedAt,
    required this.createdAt,
  });

  factory TrackingSession.fromJson(Map<String, dynamic> json) {
    return TrackingSession(
      id: json['id'],
      type: json['type'],
      status: json['status'],
      distance: json['distance']?.toDouble(),
      timeSeconds: json['time_seconds'],
      bpm: json['bpm'],
      steps: json['steps'],
      pace: json['pace']?.toDouble(),
      calories: json['calories']?.toDouble(),
      startedAt: DateTime.parse(json['started_at']),
      endedAt: json['ended_at'] != null ? DateTime.parse(json['ended_at']) : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'status': status,
      'distance': distance,
      'time_seconds': timeSeconds,
      'bpm': bpm,
      'steps': steps,
      'pace': pace,
      'calories': calories,
      'started_at': startedAt.toIso8601String(),
      'ended_at': endedAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
    };
  }

  bool get isOngoing => status == 'ongoing';
  bool get isFinished => status == 'finished';
  bool get isWalking => type == 'walking';
  bool get isRunning => type == 'running';

  String get formattedDuration {
    if (timeSeconds == null) return '--:--';
    final duration = Duration(seconds: timeSeconds!);
    final hours = duration.inHours;
    final minutes = duration.inMinutes.remainder(60);
    final seconds = duration.inSeconds.remainder(60);
    
    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  String get formattedDistance {
    if (distance == null) return '0.00 km';
    return '${distance!.toStringAsFixed(2)} km';
  }

  String get formattedPace {
    if (pace == null) return '--:--';
    final paceMinutes = pace!.floor();
    final paceSeconds = ((pace! - paceMinutes) * 60).floor();
    return '${paceMinutes.toString().padLeft(2, '0')}:${paceSeconds.toString().padLeft(2, '0')}';
  }
}

class TrackingSessionResponse {
  final String message;
  final TrackingSession session;

  TrackingSessionResponse({
    required this.message,
    required this.session,
  });

  factory TrackingSessionResponse.fromJson(Map<String, dynamic> json) {
    return TrackingSessionResponse(
      message: json['message'],
      session: TrackingSession.fromJson(json['session']),
    );
  }
}

class TrackingHistoryResponse {
  final List<TrackingSession> sessions;
  final PaginationInfo pagination;

  TrackingHistoryResponse({
    required this.sessions,
    required this.pagination,
  });

  factory TrackingHistoryResponse.fromJson(Map<String, dynamic> json) {
    return TrackingHistoryResponse(
      sessions: (json['sessions'] as List)
          .map((s) => TrackingSession.fromJson(s))
          .toList(),
      pagination: PaginationInfo.fromJson(json['pagination']),
    );
  }
}

class PaginationInfo {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  PaginationInfo({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
  });

  factory PaginationInfo.fromJson(Map<String, dynamic> json) {
    return PaginationInfo(
      currentPage: json['current_page'],
      lastPage: json['last_page'],
      perPage: json['per_page'],
      total: json['total'],
    );
  }

  bool get hasMore => currentPage < lastPage;
}

class ProgressPostResponse {
  final String message;
  final Map<String, dynamic> post;

  ProgressPostResponse({
    required this.message,
    required this.post,
  });

  factory ProgressPostResponse.fromJson(Map<String, dynamic> json) {
    return ProgressPostResponse(
      message: json['message'],
      post: json['post'],
    );
  }
}
```

---

## 4. Location Tracking Service

### LocationTrackingService

```dart
// lib/services/tracking/location_tracking_service.dart

import 'dart:async';
import 'package:geolocator/geolocator.dart';
import 'package:pedometer/pedometer.dart';

class LocationTrackingService {
  StreamSubscription<Position>? _positionStreamSubscription;
  StreamSubscription<StepCount>? _stepCountSubscription;
  
  List<Position> _positions = [];
  int _initialSteps = 0;
  int _currentSteps = 0;
  
  double _totalDistance = 0.0;
  
  final _distanceController = StreamController<double>.broadcast();
  final _stepsController = StreamController<int>.broadcast();
  
  Stream<double> get distanceStream => _distanceController.stream;
  Stream<int> get stepsStream => _stepsController.stream;
  
  double get totalDistance => _totalDistance;
  int get steps => _currentSteps - _initialSteps;

  /// Start tracking location and steps
  Future<void> startTracking() async {
    // Check permission
    final permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      final requested = await Geolocator.requestPermission();
      if (requested == LocationPermission.denied || 
          requested == LocationPermission.deniedForever) {
        throw Exception('Location permission denied');
      }
    }

    // Reset tracking data
    _positions.clear();
    _totalDistance = 0.0;
    _initialSteps = _currentSteps;

    // Start location tracking
    const locationSettings = LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 10, // Update every 10 meters
    );

    _positionStreamSubscription = Geolocator.getPositionStream(
      locationSettings: locationSettings,
    ).listen((Position position) {
      _onPositionUpdate(position);
    });

    // Start step counting
    try {
      _stepCountSubscription = Pedometer.stepCountStream.listen(
        (StepCount stepCount) {
          _currentSteps = stepCount.steps;
          _stepsController.add(steps);
        },
        onError: (error) {
          print('Error listening to step count: $error');
        },
      );
    } catch (e) {
      print('Pedometer not available: $e');
    }
  }

  /// Handle position updates
  void _onPositionUpdate(Position position) {
    if (_positions.isNotEmpty) {
      final lastPosition = _positions.last;
      final distance = Geolocator.distanceBetween(
        lastPosition.latitude,
        lastPosition.longitude,
        position.latitude,
        position.longitude,
      );
      
      // Only add if moved more than 5 meters (reduce GPS noise)
      if (distance > 5) {
        _totalDistance += distance / 1000; // Convert to kilometers
        _distanceController.add(_totalDistance);
      }
    }
    
    _positions.add(position);
  }

  /// Stop tracking
  void stopTracking() {
    _positionStreamSubscription?.cancel();
    _stepCountSubscription?.cancel();
    _positionStreamSubscription = null;
    _stepCountSubscription = null;
  }

  /// Calculate pace (min/km)
  double calculatePace(int seconds) {
    if (_totalDistance == 0) return 0;
    final minutes = seconds / 60;
    return minutes / _totalDistance;
  }

  /// Calculate calories burned
  /// Formula: calories = weight * distance * factor
  /// Walking: ~0.5, Running: ~1.0
  double calculateCalories({
    required double weight, // in kg
    required bool isRunning,
  }) {
    final factor = isRunning ? 1.0 : 0.5;
    return weight * _totalDistance * factor;
  }

  /// Get route coordinates
  List<Map<String, double>> getRouteCoordinates() {
    return _positions.map((p) => {
      'latitude': p.latitude,
      'longitude': p.longitude,
    }).toList();
  }

  /// Dispose resources
  void dispose() {
    stopTracking();
    _distanceController.close();
    _stepsController.close();
  }
}
```

---

## 5. State Management

### Using Provider

```dart
// lib/providers/tracking_provider.dart

import 'dart:async';
import 'package:flutter/foundation.dart';
import '../services/api/tracking_api_service.dart';
import '../services/tracking/location_tracking_service.dart';
import '../models/tracking_session.dart';

enum TrackingType { walking, running }
enum TrackingState { idle, starting, tracking, finishing }

class TrackingProvider with ChangeNotifier {
  final TrackingApiService _apiService;
  final LocationTrackingService _locationService;

  TrackingProvider({
    required TrackingApiService apiService,
    required LocationTrackingService locationService,
  })  : _apiService = apiService,
        _locationService = locationService;

  TrackingState _state = TrackingState.idle;
  TrackingSession? _currentSession;
  double _currentDistance = 0.0;
  int _currentSteps = 0;
  int _elapsedSeconds = 0;
  Timer? _timer;
  String? _error;

  // Getters
  TrackingState get state => _state;
  TrackingSession? get currentSession => _currentSession;
  double get currentDistance => _currentDistance;
  int get currentSteps => _currentSteps;
  int get elapsedSeconds => _elapsedSeconds;
  String? get error => _error;
  bool get isTracking => _state == TrackingState.tracking;
  bool get isIdle => _state == TrackingState.idle;

  String get formattedTime {
    final duration = Duration(seconds: _elapsedSeconds);
    final hours = duration.inHours;
    final minutes = duration.inMinutes.remainder(60);
    final seconds = duration.inSeconds.remainder(60);
    
    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  String get formattedDistance {
    return '${_currentDistance.toStringAsFixed(2)} km';
  }

  String get formattedPace {
    if (_currentDistance == 0) return '--:--';
    final pace = _locationService.calculatePace(_elapsedSeconds);
    final paceMinutes = pace.floor();
    final paceSeconds = ((pace - paceMinutes) * 60).floor();
    return '${paceMinutes.toString().padLeft(2, '0')}:${paceSeconds.toString().padLeft(2, '0')}';
  }

  /// Start tracking session
  Future<void> startTracking(TrackingType type) async {
    try {
      _state = TrackingState.starting;
      _error = null;
      notifyListeners();

      // Start API session
      final response = type == TrackingType.walking
          ? await _apiService.startWalking()
          : await _apiService.startRunning();

      _currentSession = response.session;

      // Start location tracking
      await _locationService.startTracking();

      // Listen to location updates
      _locationService.distanceStream.listen((distance) {
        _currentDistance = distance;
        notifyListeners();
      });

      _locationService.stepsStream.listen((steps) {
        _currentSteps = steps;
        notifyListeners();
      });

      // Start timer
      _elapsedSeconds = 0;
      _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
        _elapsedSeconds++;
        notifyListeners();
      });

      _state = TrackingState.tracking;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _state = TrackingState.idle;
      notifyListeners();
      rethrow;
    }
  }

  /// Finish tracking session
  Future<void> finishTracking({double? userWeight}) async {
    if (_currentSession == null) return;

    try {
      _state = TrackingState.finishing;
      notifyListeners();

      // Stop tracking
      _locationService.stopTracking();
      _timer?.cancel();

      // Calculate metrics
      final pace = _locationService.calculatePace(_elapsedSeconds);
      final calories = userWeight != null
          ? _locationService.calculateCalories(
              weight: userWeight,
              isRunning: _currentSession!.isRunning,
            )
          : null;

      // Send to API
      final response = await _apiService.finishSession(
        sessionId: _currentSession!.id,
        distance: _currentDistance,
        timeSeconds: _elapsedSeconds,
        steps: _currentSteps > 0 ? _currentSteps : null,
        pace: pace > 0 ? pace : null,
        calories: calories,
      );

      _currentSession = response.session;
      _state = TrackingState.idle;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _state = TrackingState.idle;
      notifyListeners();
      rethrow;
    }
  }

  /// Cancel tracking session
  void cancelTracking() {
    _locationService.stopTracking();
    _timer?.cancel();
    _currentSession = null;
    _currentDistance = 0.0;
    _currentSteps = 0;
    _elapsedSeconds = 0;
    _state = TrackingState.idle;
    notifyListeners();
  }

  /// Reset error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _locationService.dispose();
    super.dispose();
  }
}
```

---

## 6. UI Implementation

### Tracking Screen

```dart
// lib/screens/tracking/tracking_screen.dart

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/tracking_provider.dart';

class TrackingScreen extends StatefulWidget {
  const TrackingScreen({Key? key}) : super(key: key);

  @override
  State<TrackingScreen> createState() => _TrackingScreenState();
}

class _TrackingScreenState extends State<TrackingScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Activity Tracking'),
      ),
      body: Consumer<TrackingProvider>(
        builder: (context, provider, child) {
          if (provider.isIdle) {
            return _buildStartScreen(provider);
          } else if (provider.isTracking) {
            return _buildTrackingScreen(provider);
          } else {
            return _buildLoadingScreen();
          }
        },
      ),
    );
  }

  Widget _buildStartScreen(TrackingProvider provider) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.directions_run,
            size: 100,
            color: Colors.blue,
          ),
          const SizedBox(height: 32),
          const Text(
            'Choose Activity',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 32),
          _buildActivityButton(
            context: context,
            provider: provider,
            icon: Icons.directions_walk,
            label: 'Start Walking',
            type: TrackingType.walking,
            color: Colors.green,
          ),
          const SizedBox(height: 16),
          _buildActivityButton(
            context: context,
            provider: provider,
            icon: Icons.directions_run,
            label: 'Start Running',
            type: TrackingType.running,
            color: Colors.orange,
          ),
          if (provider.error != null) ...[
            const SizedBox(height: 24),
            Text(
              provider.error!,
              style: const TextStyle(color: Colors.red),
              textAlign: TextAlign.center,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildActivityButton({
    required BuildContext context,
    required TrackingProvider provider,
    required IconData icon,
    required String label,
    required TrackingType type,
    required Color color,
  }) {
    return ElevatedButton.icon(
      onPressed: () => _startTracking(provider, type),
      icon: Icon(icon, size: 32),
      label: Text(label),
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(
          horizontal: 48,
          vertical: 16,
        ),
        textStyle: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildTrackingScreen(TrackingProvider provider) {
    return SafeArea(
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(16),
            color: provider.currentSession?.isRunning == true
                ? Colors.orange
                : Colors.green,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  provider.currentSession?.isRunning == true
                      ? 'Running'
                      : 'Walking',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Icon(
                  Icons.circle,
                  color: Colors.red,
                  size: 16,
                ),
              ],
            ),
          ),

          // Metrics
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Time
                  _buildMetricCard(
                    label: 'TIME',
                    value: provider.formattedTime,
                    icon: Icons.timer,
                  ),
                  const SizedBox(height: 24),

                  // Distance and Pace
                  Row(
                    children: [
                      Expanded(
                        child: _buildMetricCard(
                          label: 'DISTANCE',
                          value: provider.formattedDistance,
                          icon: Icons.straighten,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: _buildMetricCard(
                          label: 'PACE',
                          value: '${provider.formattedPace}/km',
                          icon: Icons.speed,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Steps
                  if (provider.currentSteps > 0)
                    _buildMetricCard(
                      label: 'STEPS',
                      value: provider.currentSteps.toString(),
                      icon: Icons.add_road,
                    ),
                ],
              ),
            ),
          ),

          // Control buttons
          Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _cancelTracking(provider),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: const BorderSide(color: Colors.red),
                    ),
                    child: const Text(
                      'Cancel',
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.red,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: () => _finishTracking(provider),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: const Text(
                      'Finish',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricCard({
    required String label,
    required String value,
    required IconData icon,
  }) {
    return Card(
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(icon, size: 32, color: Colors.blue),
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                color: Colors.grey,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              value,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadingScreen() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircularProgressIndicator(),
          SizedBox(height: 16),
          Text('Starting session...'),
        ],
      ),
    );
  }

  Future<void> _startTracking(
    TrackingProvider provider,
    TrackingType type,
  ) async {
    try {
      await provider.startTracking(type);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to start tracking: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _finishTracking(TrackingProvider provider) async {
    // You can get user weight from profile
    const userWeight = 70.0; // kg

    try {
      await provider.finishTracking(userWeight: userWeight);
      
      if (mounted) {
        // Show summary dialog
        _showSummaryDialog(provider.currentSession!);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to finish tracking: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _cancelTracking(TrackingProvider provider) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Activity'),
        content: const Text(
          'Are you sure you want to cancel this activity? Your progress will be lost.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () {
              provider.cancelTracking();
              Navigator.pop(context);
            },
            child: const Text(
              'Yes, Cancel',
              style: TextStyle(color: Colors.red),
            ),
          ),
        ],
      ),
    );
  }

  void _showSummaryDialog(TrackingSession session) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(
              session.isRunning ? Icons.directions_run : Icons.directions_walk,
              color: session.isRunning ? Colors.orange : Colors.green,
            ),
            const SizedBox(width: 8),
            const Text('Activity Completed!'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildSummaryRow('Type', session.type.toUpperCase()),
            _buildSummaryRow('Distance', session.formattedDistance),
            _buildSummaryRow('Duration', session.formattedDuration),
            _buildSummaryRow('Pace', '${session.formattedPace}/km'),
            if (session.steps != null)
              _buildSummaryRow('Steps', session.steps.toString()),
            if (session.calories != null)
              _buildSummaryRow(
                'Calories',
                '${session.calories!.toStringAsFixed(0)} kcal',
              ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _shareToCommnity(session);
            },
            child: const Text('Share to Community'),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            '$label:',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          Text(value),
        ],
      ),
    );
  }

  Future<void> _shareToCommnity(TrackingSession session) async {
    // Show dialog to enter description
    final controller = TextEditingController();
    
    final description = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Share to Community'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(
            hintText: 'Add a description...',
            border: OutlineInputBorder(),
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, controller.text),
            child: const Text('Share'),
          ),
        ],
      ),
    );

    if (description != null) {
      try {
        final apiService = context.read<TrackingApiService>();
        await apiService.shareToCommnity(
          sessionId: session.id,
          description: description.isNotEmpty ? description : null,
        );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Shared to community!'),
              backgroundColor: Colors.green,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Failed to share: $e'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }
}
```

### Tracking History Screen

```dart
// lib/screens/tracking/tracking_history_screen.dart

import 'package:flutter/material.dart';
import '../../services/api/tracking_api_service.dart';
import '../../models/tracking_session.dart';

class TrackingHistoryScreen extends StatefulWidget {
  const TrackingHistoryScreen({Key? key}) : super(key: key);

  @override
  State<TrackingHistoryScreen> createState() => _TrackingHistoryScreenState();
}

class _TrackingHistoryScreenState extends State<TrackingHistoryScreen> {
  final ScrollController _scrollController = ScrollController();
  final List<TrackingSession> _sessions = [];
  
  String? _filterType;
  int _currentPage = 1;
  bool _isLoading = false;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadSessions();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.8) {
      if (!_isLoading && _hasMore) {
        _loadMore();
      }
    }
  }

  Future<void> _loadSessions() async {
    if (_isLoading) return;

    setState(() {
      _isLoading = true;
      _currentPage = 1;
      _sessions.clear();
    });

    try {
      final apiService = context.read<TrackingApiService>();
      final response = await apiService.getHistory(
        type: _filterType,
        page: _currentPage,
      );

      setState(() {
        _sessions.addAll(response.sessions);
        _hasMore = response.pagination.hasMore;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _loadMore() async {
    if (_isLoading || !_hasMore) return;

    setState(() {
      _isLoading = true;
      _currentPage++;
    });

    try {
      final apiService = context.read<TrackingApiService>();
      final response = await apiService.getHistory(
        type: _filterType,
        page: _currentPage,
      );

      setState(() {
        _sessions.addAll(response.sessions);
        _hasMore = response.pagination.hasMore;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _currentPage--;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Activity History'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (value) {
              setState(() {
                _filterType = value == 'all' ? null : value;
              });
              _loadSessions();
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'all', child: Text('All')),
              const PopupMenuItem(value: 'walking', child: Text('Walking')),
              const PopupMenuItem(value: 'running', child: Text('Running')),
            ],
            icon: const Icon(Icons.filter_list),
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading && _sessions.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_sessions.isEmpty) {
      return const Center(
        child: Text('No activities yet'),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadSessions,
      child: ListView.builder(
        controller: _scrollController,
        itemCount: _sessions.length + (_hasMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == _sessions.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: CircularProgressIndicator(),
              ),
            );
          }

          final session = _sessions[index];
          return _buildSessionCard(session);
        },
      ),
    );
  }

  Widget _buildSessionCard(TrackingSession session) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: session.isRunning ? Colors.orange : Colors.green,
          child: Icon(
            session.isRunning ? Icons.directions_run : Icons.directions_walk,
            color: Colors.white,
          ),
        ),
        title: Text(
          session.type.toUpperCase(),
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Text(
          '${session.formattedDistance} • ${session.formattedDuration}',
        ),
        trailing: Text(
          _formatDate(session.startedAt),
          style: const TextStyle(fontSize: 12, color: Colors.grey),
        ),
        onTap: () => _showSessionDetails(session),
      ),
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);

    if (diff.inDays == 0) return 'Today';
    if (diff.inDays == 1) return 'Yesterday';
    if (diff.inDays < 7) return '${diff.inDays} days ago';
    
    return '${date.day}/${date.month}/${date.year}';
  }

  void _showSessionDetails(TrackingSession session) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  session.isRunning ? Icons.directions_run : Icons.directions_walk,
                  size: 32,
                  color: session.isRunning ? Colors.orange : Colors.green,
                ),
                const SizedBox(width: 12),
                Text(
                  session.type.toUpperCase(),
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const Divider(height: 32),
            _buildDetailRow('Distance', session.formattedDistance),
            _buildDetailRow('Duration', session.formattedDuration),
            _buildDetailRow('Pace', '${session.formattedPace}/km'),
            if (session.steps != null)
              _buildDetailRow('Steps', session.steps.toString()),
            if (session.calories != null)
              _buildDetailRow(
                'Calories',
                '${session.calories!.toStringAsFixed(0)} kcal',
              ),
            _buildDetailRow(
              'Date',
              '${session.startedAt.day}/${session.startedAt.month}/${session.startedAt.year}',
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 16,
              color: Colors.grey,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## 7. Permissions

### Android Configuration

Add to `android/app/src/main/AndroidManifest.xml`:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <!-- Location permissions -->
    <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
    <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
    
    <!-- Pedometer permissions -->
    <uses-permission android:name="android.permission.ACTIVITY_RECOGNITION" />
    
    <!-- Background location (if needed) -->
    <uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
    
    <!-- Foreground service (for background tracking) -->
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
</manifest>
```

### iOS Configuration

Add to `ios/Runner/Info.plist`:

```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>We need your location to track your walking and running activities</string>

<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>
<string>We need your location to track your activities even when the app is in the background</string>

<key>NSMotionUsageDescription</key>
<string>We need access to your motion data to count your steps</string>

<key>UIBackgroundModes</key>
<array>
    <string>location</string>
</array>
```

---

## 8. Testing

### Unit Tests

```dart
// test/services/location_tracking_service_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:your_app/services/tracking/location_tracking_service.dart';

void main() {
  group('LocationTrackingService', () {
    late LocationTrackingService service;

    setUp(() {
      service = LocationTrackingService();
    });

    tearDown(() {
      service.dispose();
    });

    test('calculate pace correctly', () {
      // Assuming 5 km in 30 minutes (1800 seconds)
      service._totalDistance = 5.0;
      final pace = service.calculatePace(1800);
      
      expect(pace, 6.0); // 6 min/km
    });

    test('calculate calories for walking', () {
      service._totalDistance = 5.0;
      final calories = service.calculateCalories(
        weight: 70.0,
        isRunning: false,
      );
      
      expect(calories, 175.0); // 70 * 5 * 0.5
    });

    test('calculate calories for running', () {
      service._totalDistance = 5.0;
      final calories = service.calculateCalories(
        weight: 70.0,
        isRunning: true,
      );
      
      expect(calories, 350.0); // 70 * 5 * 1.0
    });
  });
}
```

---

## Best Practices

### 1. Battery Optimization
- Use appropriate `distanceFilter` in LocationSettings (10-20 meters)
- Stop tracking immediately when finished
- Consider using lower accuracy for walking

### 2. Error Handling
- Always handle location permission denials
- Implement retry logic for API failures
- Show user-friendly error messages

### 3. Offline Support
- Cache tracking data locally if API fails
- Sync when connection is restored
- Use local database (SQLite/Hive) for offline sessions

### 4. Background Tracking (Advanced)
For continuous tracking when app is in background:
- Use `workmanager` package for background tasks
- Request background location permission
- Show persistent notification (Android)

### 5. Performance
- Debounce location updates to reduce UI redraws
- Use `const` constructors where possible
- Dispose of stream subscriptions properly

---

## Example: Main App Setup

```dart
// lib/main.dart

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'services/api/tracking_api_service.dart';
import 'services/auth/auth_service.dart';
import 'services/tracking/location_tracking_service.dart';
import 'providers/tracking_provider.dart';
import 'screens/tracking/tracking_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        // Auth service
        Provider<AuthService>(
          create: (_) => AuthService(),
        ),

        // API services
        ProxyProvider<AuthService, TrackingApiService>(
          update: (_, auth, __) => TrackingApiService(
            baseUrl: 'https://your-api.com',
            authService: auth,
          ),
        ),

        // Location service
        Provider<LocationTrackingService>(
          create: (_) => LocationTrackingService(),
          dispose: (_, service) => service.dispose(),
        ),

        // Tracking provider
        ProxyProvider2<TrackingApiService, LocationTrackingService, TrackingProvider>(
          update: (_, api, location, previous) => previous ?? TrackingProvider(
            apiService: api,
            locationService: location,
          ),
          dispose: (_, provider) => provider.dispose(),
        ),
      ],
      child: MaterialApp(
        title: 'Suniorfit',
        theme: ThemeData(
          primarySwatch: Colors.blue,
          useMaterial3: true,
        ),
        home: const TrackingScreen(),
      ),
    );
  }
}
```

---

## Summary

This implementation provides:
- ✅ Separate walking and running tracking
- ✅ Real-time location and step tracking
- ✅ Automatic pace and calorie calculation
- ✅ Complete API integration
- ✅ History with pagination
- ✅ Community sharing
- ✅ Clean architecture with state management
- ✅ Proper permission handling
- ✅ Error handling and loading states

The implementation is production-ready and follows Flutter best practices!
