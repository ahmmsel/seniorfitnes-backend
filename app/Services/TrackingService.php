<?php

namespace App\Services;

use App\Models\TrackingSession;
use App\Models\ProgressPost;
use App\Models\TraineeProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class TrackingService
{
    /**
     * Start a new walking session
     */
    public function startWalking(): TrackingSession
    {
        $trainee = $this->getTrainee();

        return TrackingSession::create([
            'trainee_id' => $trainee->id,
            'type' => 'walking',
            'status' => 'ongoing',
            'started_at' => now(),
        ]);
    }

    /**
     * Start a new running session
     */
    public function startRunning(): TrackingSession
    {
        $trainee = $this->getTrainee();

        return TrackingSession::create([
            'trainee_id' => $trainee->id,
            'type' => 'running',
            'status' => 'ongoing',
            'started_at' => now(),
        ]);
    }

    /**
     * Finish a tracking session with metrics
     */
    public function finishSession(int $sessionId, array $data): TrackingSession
    {
        $trainee = $this->getTrainee();

        $session = TrackingSession::where('id', $sessionId)
            ->where('trainee_id', $trainee->id)
            ->where('status', 'ongoing')
            ->first();

        if (!$session) {
            throw new ModelNotFoundException('Session not found or already finished.');
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

        return $session->fresh();
    }

    /**
     * Get tracking history for authenticated trainee
     */
    public function getHistory(?string $type = null, int $perPage = 15)
    {
        $trainee = $this->getTrainee();

        $query = TrackingSession::where('trainee_id', $trainee->id);

        if ($type && in_array($type, ['walking', 'running'])) {
            $query->where('type', $type);
        }

        return $query->orderBy('started_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Share a finished session to community
     */
    public function shareToCommnity(int $sessionId, ?string $description = null): ProgressPost
    {
        $trainee = $this->getTrainee();

        $session = TrackingSession::where('id', $sessionId)
            ->where('trainee_id', $trainee->id)
            ->where('status', 'finished')
            ->first();

        if (!$session) {
            throw new ModelNotFoundException('Session not found, does not belong to you, or is not finished.');
        }

        // Check if already shared
        $existingPost = ProgressPost::where('session_id', $sessionId)->first();
        if ($existingPost) {
            throw ValidationException::withMessages([
                'session' => 'This session has already been shared.',
            ]);
        }

        $post = ProgressPost::create([
            'trainee_id' => $trainee->id,
            'session_id' => $session->id,
            'description' => $description,
        ]);

        return $post->load(['trainee.user', 'session', 'likes', 'comments']);
    }

    /**
     * Get authenticated trainee profile
     */
    private function getTrainee(): TraineeProfile
    {
        $trainee = Auth::user()?->traineeProfile;

        if (!$trainee) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        return $trainee;
    }
}
