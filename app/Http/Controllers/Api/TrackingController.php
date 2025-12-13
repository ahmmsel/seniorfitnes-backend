<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\FinishSessionRequest;
use App\Http\Requests\Tracking\ShareProgressRequest;
use App\Http\Resources\TrackingSessionResource;
use App\Http\Resources\ProgressPostResource;
use App\Models\TrackingSession;
use App\Models\ProgressPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /**
     * Start a new tracking session
     * POST /api/tracking/start
     */
    public function start(Request $request): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;

        if (!$trainee) {
            return response()->json(['message' => 'Trainee profile not found'], 404);
        }

        $session = TrackingSession::create([
            'trainee_id' => $trainee->id,
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tracking session started',
            'session' => new TrackingSessionResource($session),
        ], 201);
    }

    /**
     * Finish a tracking session with calculated data
     * POST /api/tracking/finish
     */
    public function finish(FinishSessionRequest $request): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;
        $data = $request->validated();

        $session = TrackingSession::where('id', $data['session_id'])
            ->where('trainee_id', $trainee->id)
            ->where('status', 'ongoing')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Session not found or already finished'
            ], 404);
        }

        $session->update([
            'status' => 'finished',
            'distance' => $data['distance'],
            'time_seconds' => $data['time_seconds'],
            'bpm' => $data['bpm'] ?? null,
            'steps' => $data['steps'] ?? null,
            'pace' => $data['pace'] ?? null,
            'calories' => $data['calories'] ?? null,
            'ended_at' => now(),
        ]);

        return response()->json([
            'message' => 'Session finished successfully',
            'session' => new TrackingSessionResource($session),
        ]);
    }

    /**
     * Get tracking history for authenticated trainee
     * GET /api/tracking/history
     */
    public function history(Request $request): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;

        if (!$trainee) {
            return response()->json(['message' => 'Trainee profile not found'], 404);
        }

        $sessions = TrackingSession::where('trainee_id', $trainee->id)
            ->orderBy('started_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'sessions' => TrackingSessionResource::collection($sessions->items()),
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    /**
     * Share a finished session to community
     * POST /api/progress/share/{session_id}
     */
    public function share(ShareProgressRequest $request, $sessionId): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;

        $session = TrackingSession::where('id', $sessionId)
            ->where('trainee_id', $trainee->id)
            ->where('status', 'finished')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Session not found, does not belong to you, or is not finished'
            ], 404);
        }

        // Check if already shared
        $existingPost = ProgressPost::where('session_id', $sessionId)->first();
        if ($existingPost) {
            return response()->json([
                'message' => 'This session has already been shared',
                'post' => new ProgressPostResource($existingPost),
            ], 409);
        }

        $post = ProgressPost::create([
            'trainee_id' => $trainee->id,
            'session_id' => $session->id,
            'description' => $request->validated()['description'] ?? null,
        ]);

        $post->load(['trainee.user', 'session', 'likes', 'comments']);

        return response()->json([
            'message' => 'Progress shared to community',
            'post' => new ProgressPostResource($post),
        ], 201);
    }
}
