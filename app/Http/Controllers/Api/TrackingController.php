<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\FinishSessionRequest;
use App\Http\Requests\Tracking\ShareProgressRequest;
use App\Http\Resources\TrackingSessionResource;
use App\Http\Resources\ProgressPostResource;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(protected TrackingService $trackingService) {}

    /**
     * Start a new walking session
     * POST /api/tracking/walking/start
     */
    public function startWalking(): JsonResponse
    {
        $session = $this->trackingService->startWalking();

        return response()->json([
            'message' => 'Walking session started',
            'session' => new TrackingSessionResource($session),
        ], 201);
    }

    /**
     * Start a new running session
     * POST /api/tracking/running/start
     */
    public function startRunning(): JsonResponse
    {
        $session = $this->trackingService->startRunning();

        return response()->json([
            'message' => 'Running session started',
            'session' => new TrackingSessionResource($session),
        ], 201);
    }

    /**
     * Finish a tracking session with calculated data
     * POST /api/tracking/finish
     */
    public function finish(FinishSessionRequest $request): JsonResponse
    {
        $session = $this->trackingService->finishSession(
            $request->validated()['session_id'],
            $request->validated()
        );

        return response()->json([
            'message' => 'Session finished successfully',
            'session' => new TrackingSessionResource($session),
        ]);
    }

    /**
     * Get tracking history for authenticated trainee
     * GET /api/tracking/history?type=walking|running
     */
    public function history(Request $request): JsonResponse
    {
        $sessions = $this->trackingService->getHistory(
            $request->get('type'),
            (int) $request->get('per_page', 15)
        );

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
        $post = $this->trackingService->shareToCommnity(
            $sessionId,
            $request->validated()['description'] ?? null
        );

        return response()->json([
            'message' => 'Progress shared to community',
            'post' => new ProgressPostResource($post),
        ], 201);
    }
}
