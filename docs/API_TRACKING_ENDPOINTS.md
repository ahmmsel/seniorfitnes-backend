# API Documentation: Tracking Endpoints

## Base URL
```
https://your-api.com/api
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {access_token}
```

---

## 1. Start Walking Session

### Endpoint
```
POST /api/tracking/walking/start
```

### Request Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer {access_token}"
}
```

### Request Body
```json
No body required
```

### Success Response (201 Created)
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
    "started_at": "2026-01-21T11:30:00+00:00",
    "ended_at": null,
    "created_at": "2026-01-21T11:30:00+00:00"
  }
}
```

### Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 404 Not Found
```json
{
  "message": "Trainee profile not found"
}
```

### cURL Example
```bash
curl -X POST https://your-api.com/api/tracking/walking/start \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}"
```

---

## 2. Start Running Session

### Endpoint
```
POST /api/tracking/running/start
```

### Request Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer {access_token}"
}
```

### Request Body
```json
No body required
```

### Success Response (201 Created)
```json
{
  "message": "Running session started",
  "session": {
    "id": 2,
    "type": "running",
    "status": "ongoing",
    "distance": null,
    "time_seconds": null,
    "bpm": null,
    "steps": null,
    "pace": null,
    "calories": null,
    "started_at": "2026-01-21T11:45:00+00:00",
    "ended_at": null,
    "created_at": "2026-01-21T11:45:00+00:00"
  }
}
```

### Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 404 Not Found
```json
{
  "message": "Trainee profile not found"
}
```

### cURL Example
```bash
curl -X POST https://your-api.com/api/tracking/running/start \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}"
```

---

## 3. Finish Session

### Endpoint
```
POST /api/tracking/finish
```

### Request Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer {access_token}"
}
```

### Request Body
```json
{
  "session_id": 1,
  "distance": 5.2,
  "time_seconds": 1800,
  "bpm": 120,
  "steps": 6500,
  "pace": 5.77,
  "calories": 320.5
}
```

### Field Descriptions
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `session_id` | integer | Yes | The ID of the ongoing session to finish |
| `distance` | float | Yes | Total distance in kilometers (must be >= 0) |
| `time_seconds` | integer | Yes | Total time in seconds (must be >= 0) |
| `bpm` | integer | No | Average heart rate (0-250) |
| `steps` | integer | No | Total steps count (must be >= 0) |
| `pace` | float | No | Average pace in minutes per kilometer (must be >= 0) |
| `calories` | float | No | Calories burned (must be >= 0) |

### Validation Rules
```php
'session_id' => 'required|exists:tracking_sessions,id',
'distance' => 'required|numeric|min:0',
'time_seconds' => 'required|integer|min:0',
'bpm' => 'nullable|integer|min:0|max:250',
'steps' => 'nullable|integer|min:0',
'pace' => 'nullable|numeric|min:0',
'calories' => 'nullable|numeric|min:0'
```

### Success Response (200 OK)
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
    "calories": 320.5,
    "started_at": "2026-01-21T11:30:00+00:00",
    "ended_at": "2026-01-21T12:00:00+00:00",
    "created_at": "2026-01-21T11:30:00+00:00"
  }
}
```

### Error Responses

#### 404 Not Found
```json
{
  "message": "Session not found or already finished"
}
```

#### 422 Unprocessable Entity (Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "session_id": [
      "The selected session id is invalid."
    ],
    "distance": [
      "The distance must be at least 0."
    ],
    "bpm": [
      "The bpm must not be greater than 250."
    ]
  }
}
```

### cURL Example
```bash
curl -X POST https://your-api.com/api/tracking/finish \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "session_id": 1,
    "distance": 5.2,
    "time_seconds": 1800,
    "bpm": 120,
    "steps": 6500,
    "pace": 5.77,
    "calories": 320.5
  }'
```

---

## 4. Get Tracking History

### Endpoint
```
GET /api/tracking/history
```

### Request Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer {access_token}"
}
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `type` | string | No | null | Filter by activity type: `walking` or `running` |
| `per_page` | integer | No | 15 | Number of items per page |
| `page` | integer | No | 1 | Page number |

### Request Examples

#### Get all sessions
```
GET /api/tracking/history
```

#### Filter by walking only
```
GET /api/tracking/history?type=walking
```

#### Filter by running with pagination
```
GET /api/tracking/history?type=running&per_page=10&page=2
```

### Success Response (200 OK)
```json
{
  "sessions": [
    {
      "id": 5,
      "type": "running",
      "status": "finished",
      "distance": 8.5,
      "time_seconds": 2700,
      "bpm": 145,
      "steps": 9800,
      "pace": 5.29,
      "calories": 580.2,
      "started_at": "2026-01-21T10:00:00+00:00",
      "ended_at": "2026-01-21T10:45:00+00:00",
      "created_at": "2026-01-21T10:00:00+00:00"
    },
    {
      "id": 4,
      "type": "walking",
      "status": "finished",
      "distance": 3.2,
      "time_seconds": 1200,
      "bpm": 95,
      "steps": 4200,
      "pace": 6.25,
      "calories": 180.0,
      "started_at": "2026-01-20T17:30:00+00:00",
      "ended_at": "2026-01-20T17:50:00+00:00",
      "created_at": "2026-01-20T17:30:00+00:00"
    },
    {
      "id": 3,
      "type": "running",
      "status": "finished",
      "distance": 5.0,
      "time_seconds": 1500,
      "bpm": 138,
      "steps": 6500,
      "pace": 5.0,
      "calories": 420.0,
      "started_at": "2026-01-20T08:00:00+00:00",
      "ended_at": "2026-01-20T08:25:00+00:00",
      "created_at": "2026-01-20T08:00:00+00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

### Empty Response (200 OK)
```json
{
  "sessions": [],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  }
}
```

### Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 404 Not Found
```json
{
  "message": "Trainee profile not found"
}
```

### cURL Example
```bash
# Get all sessions
curl -X GET "https://your-api.com/api/tracking/history" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}"

# Filter by walking
curl -X GET "https://your-api.com/api/tracking/history?type=walking&per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}"
```

---

## 5. Share Session to Community

### Endpoint
```
POST /api/progress/share/{session_id}
```

### Request Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer {access_token}"
}
```

### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `session_id` | integer | Yes | The ID of the finished session to share |

### Request Body
```json
{
  "description": "Great morning run! 🏃 Feeling energized!"
}
```

### Field Descriptions
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `description` | string | No | Optional description/caption for the post |

### Validation Rules
```php
'description' => 'nullable|string|max:500'
```

### Success Response (201 Created)
```json
{
  "message": "Progress shared to community",
  "post": {
    "id": 123,
    "trainee": {
      "id": 45,
      "name": "Ahmed Ali",
      "avatar": "https://your-api.com/storage/avatars/45.webp"
    },
    "description": "Great morning run! 🏃 Feeling energized!",
    "distance": 8.5,
    "time_seconds": 2700,
    "pace": 5.29,
    "calories": 580.2,
    "steps": 9800,
    "likes_count": 0,
    "comments_count": 0,
    "is_liked": false,
    "created_at": "2026-01-21T11:00:00+00:00",
    "time_ago": "just now"
  }
}
```

### Error Responses

#### 404 Not Found
```json
{
  "message": "Session not found, does not belong to you, or is not finished"
}
```

#### 409 Conflict (Already Shared)
```json
{
  "message": "This session has already been shared",
  "post": {
    "id": 123,
    "trainee": {
      "id": 45,
      "name": "Ahmed Ali",
      "avatar": "https://your-api.com/storage/avatars/45.webp"
    },
    "description": "Great morning run! 🏃 Feeling energized!",
    "distance": 8.5,
    "time_seconds": 2700,
    "pace": 5.29,
    "calories": 580.2,
    "steps": 9800,
    "likes_count": 12,
    "comments_count": 3,
    "is_liked": true,
    "created_at": "2026-01-21T11:00:00+00:00",
    "time_ago": "2 hours ago"
  }
}
```

#### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "description": [
      "The description must not be greater than 500 characters."
    ]
  }
}
```

### cURL Example
```bash
# Share with description
curl -X POST https://your-api.com/api/progress/share/5 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "description": "Great morning run! 🏃 Feeling energized!"
  }'

# Share without description
curl -X POST https://your-api.com/api/progress/share/5 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{}'
```

---

## Complete Usage Flow Example

### Step 1: Start a Walking Session
```bash
POST /api/tracking/walking/start
Authorization: Bearer {token}

# Response
{
  "message": "Walking session started",
  "session": {
    "id": 10,
    "type": "walking",
    "status": "ongoing",
    ...
  }
}
```

### Step 2: Track Location & Metrics
```
Client tracks GPS location, steps, heart rate during activity
- Calculate distance from GPS coordinates
- Count steps from device pedometer
- Monitor heart rate from wearable
- Track duration with timer
```

### Step 3: Finish the Session
```bash
POST /api/tracking/finish
Authorization: Bearer {token}
Content-Type: application/json

{
  "session_id": 10,
  "distance": 3.5,
  "time_seconds": 1320,
  "bpm": 110,
  "steps": 4800,
  "pace": 6.29,
  "calories": 245.0
}

# Response
{
  "message": "Session finished successfully",
  "session": {
    "id": 10,
    "type": "walking",
    "status": "finished",
    "distance": 3.5,
    ...
  }
}
```

### Step 4: Share to Community (Optional)
```bash
POST /api/progress/share/10
Authorization: Bearer {token}
Content-Type: application/json

{
  "description": "Nice evening walk in the park 🌳"
}

# Response
{
  "message": "Progress shared to community",
  "post": { ... }
}
```

### Step 5: View History
```bash
GET /api/tracking/history?type=walking&per_page=10
Authorization: Bearer {token}

# Response
{
  "sessions": [ ... ],
  "pagination": { ... }
}
```

---

## Response Field Types

### TrackingSession Object
```typescript
interface TrackingSession {
  id: number;
  type: "walking" | "running";
  status: "ongoing" | "finished";
  distance: number | null;          // kilometers
  time_seconds: number | null;       // total seconds
  bpm: number | null;                // beats per minute (0-250)
  steps: number | null;              // step count
  pace: number | null;               // minutes per kilometer
  calories: number | null;           // kcal burned
  started_at: string;                // ISO 8601 datetime
  ended_at: string | null;           // ISO 8601 datetime
  created_at: string;                // ISO 8601 datetime
}
```

### Pagination Object
```typescript
interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
```

---

## Common Error Codes

| Code | Description | Common Causes |
|------|-------------|---------------|
| 400 | Bad Request | Invalid JSON format |
| 401 | Unauthorized | Missing or invalid token |
| 404 | Not Found | Session/Profile not found, wrong session owner |
| 409 | Conflict | Session already shared |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side issues |

---

## Rate Limiting

Current implementation has no specific rate limits, but general Laravel rate limiting applies:
- **Default**: 60 requests per minute per user
- **Burst**: Up to 120 requests in a short time

Headers in response:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Best Practices

### 1. Session Management
- Always finish sessions when user stops tracking
- Handle app termination gracefully (save state locally)
- Don't start multiple sessions simultaneously

### 2. Data Accuracy
- Filter GPS noise (ignore positions < 5 meters apart)
- Validate metrics on client side before sending
- Round values appropriately (2 decimals for distance, pace)

### 3. Error Handling
- Implement retry logic for network failures
- Cache session data locally before sending
- Show user-friendly error messages

### 4. Performance
- Don't poll history endpoint frequently
- Use pagination for large datasets
- Cache history locally and refresh on pull-to-refresh

---

## Testing with Postman

### Environment Variables
```json
{
  "base_url": "https://your-api.com",
  "access_token": "your-bearer-token-here"
}
```

### Collection Structure
```
Tracking API/
├── Start Walking Session
├── Start Running Session
├── Finish Session
├── Get History (All)
├── Get History (Walking)
├── Get History (Running)
└── Share to Community
```

---

## Changelog

### Version 1.0 (January 21, 2026)
- Initial release
- Separated walking and running endpoints
- Added type field to sessions
- Implemented TrackingService for business logic
- Added history filtering by type
- Community sharing integration

---

## Support

For issues or questions:
- Backend Repository: [GitHub](https://github.com/your-repo)
- API Documentation: [OpenAPI Spec](docs/api_openapi.yaml)
- Contact: support@suniorfit.com
