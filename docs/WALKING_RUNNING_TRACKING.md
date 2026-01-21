# Walking & Running Tracking Service

## Overview
This feature provides separate tracking for walking and running activities. It includes a dedicated `TrackingService` that handles all business logic, following clean architecture principles.

## Architecture

### Files Created/Modified
- **Service**: `app/Services/TrackingService.php` (NEW)
- **Controller**: `app/Http/Controllers/Api/TrackingController.php` (REFACTORED)
- **Model**: `app/Models/TrackingSession.php` (UPDATED)
- **Resource**: `app/Http/Resources/TrackingSessionResource.php` (UPDATED)
- **Migration**: `database/migrations/2026_01_21_112957_add_type_to_tracking_sessions_table.php` (NEW)

### Database Schema
The `tracking_sessions` table now includes:
- `type`: enum('walking', 'running') - distinguishes between walking and running sessions
- Other fields: trainee_id, status, distance, time_seconds, bpm, steps, pace, calories, started_at, ended_at

## API Endpoints

### 1. Start Walking Session
```
POST /api/tracking/walking/start
Authorization: Bearer {token}
```

**Response**:
```json
{
  "message": "Walking session started",
  "session": {
    "id": 1,
    "type": "walking",
    "status": "ongoing",
    "distance": null,
    "time_seconds": null,
    "bpm": null,
    "steps": null,
    "pace": null,
    "calories": null,
    "started_at": "2026-01-21T11:30:00Z",
    "ended_at": null,
    "created_at": "2026-01-21T11:30:00Z"
  }
}
```

### 2. Start Running Session
```
POST /api/tracking/running/start
Authorization: Bearer {token}
```

**Response**:
```json
{
  "message": "Running session started",
  "session": {
    "id": 2,
    "type": "running",
    "status": "ongoing",
    ...
  }
}
```

### 3. Finish Session
```
POST /api/tracking/finish
Authorization: Bearer {token}

{
  "session_id": 1,
  "distance": 5.2,
  "time_seconds": 1800,
  "bpm": 120,
  "steps": 6500,
  "pace": 5.77,
  "calories": 320
}
```

**Response**:
```json
{
  "message": "Session finished successfully",
  "session": {
    "id": 1,
    "type": "walking",
    "status": "finished",
    "distance": 5.2,
    "time_seconds": 1800,
    "bpm": 120,
    "steps": 6500,
    "pace": 5.77,
    "calories": 320,
    "started_at": "2026-01-21T11:30:00Z",
    "ended_at": "2026-01-21T12:00:00Z",
    "created_at": "2026-01-21T11:30:00Z"
  }
}
```

### 4. Get History
```
GET /api/tracking/history?type=walking&per_page=15
Authorization: Bearer {token}
```

**Query Parameters**:
- `type` (optional): Filter by 'walking' or 'running'
- `per_page` (optional): Number of results per page (default: 15)

**Response**:
```json
{
  "sessions": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

### 5. Share to Community
```
POST /api/progress/share/{session_id}
Authorization: Bearer {token}

{
  "description": "Great morning run! 🏃"
}
```

**Response**:
```json
{
  "message": "Progress shared to community",
  "post": {...}
}
```

## Service Methods

### TrackingService

#### `startWalking(): TrackingSession`
Creates a new walking session for the authenticated trainee.

#### `startRunning(): TrackingSession`
Creates a new running session for the authenticated trainee.

#### `finishSession(int $sessionId, array $data): TrackingSession`
Finishes an ongoing session with the provided metrics.

**Parameters**:
- `$sessionId`: The session ID to finish
- `$data`: Array containing distance, time_seconds, bpm, steps, pace, calories

**Throws**:
- `ModelNotFoundException`: If session not found or already finished

#### `getHistory(?string $type = null, int $perPage = 15)`
Retrieves paginated tracking history for the authenticated trainee.

**Parameters**:
- `$type`: Optional filter ('walking' or 'running')
- `$perPage`: Results per page

#### `shareToCommnity(int $sessionId, ?string $description = null): ProgressPost`
Shares a finished session to the community feed.

**Throws**:
- `ModelNotFoundException`: If session not found or not finished
- `ValidationException`: If session already shared

## Form Requests

### FinishSessionRequest
Located at: `app/Http/Requests/Tracking/FinishSessionRequest.php`

**Validation Rules**:
```php
'session_id' => 'required|exists:tracking_sessions,id',
'distance' => 'required|numeric|min:0',
'time_seconds' => 'required|integer|min:0',
'bpm' => 'nullable|integer|min:0|max:250',
'steps' => 'nullable|integer|min:0',
'pace' => 'nullable|numeric|min:0',
'calories' => 'nullable|numeric|min:0',
```

### ShareProgressRequest
Located at: `app/Http/Requests/Tracking/ShareProgressRequest.php`

## Error Handling

All service methods throw appropriate exceptions:
- `ModelNotFoundException`: For missing resources
- `ValidationException`: For business logic violations

The controller handles these exceptions and returns appropriate HTTP responses.

## Migration Notes

The type column was added via migration `2026_01_21_112957_add_type_to_tracking_sessions_table.php`.

**Important**: All existing tracking_sessions records will need the type field populated. Consider running a data migration if you have existing records.

## Testing

Test scenarios to cover:
1. Start walking session
2. Start running session
3. Finish session with valid data
4. Attempt to finish non-existent session
5. Attempt to finish already finished session
6. Get history filtered by type
7. Share session to community
8. Attempt to share already shared session

## Future Enhancements

Potential improvements:
- Add GPS route tracking
- Include elevation data
- Add real-time location updates
- Implement session pause/resume functionality
- Add achievements/badges for milestones
- Social features (compare with friends, challenges)
