<?php

namespace App\Services;

use App\Models\{
    Challenge,
    ChallengeJoin,
    ChallengeDayCompletion,
    ChallengeLeaderboard
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ChallengeService
{
    public function getAll(): array
    {
        $challenges = Challenge::with(['badge', 'media'])
            ->get()
            ->map(fn($challenge) => $this->formatChallenge($challenge));

        return ['challenges' => $challenges];
    }

    public function show(Challenge $challenge): array
    {
        $user = Auth::user();
        $trainee = $user->traineeProfile;

        $joined = $trainee
            ? ChallengeJoin::where('trainee_id', $trainee->id)
            ->where('challenge_id', $challenge->id)
            ->exists()
            : false;

        $completedDays = ChallengeDayCompletion::where('challenge_id', $challenge->id)
            ->where('trainee_id', $trainee->id)
            ->pluck('day_number');

        return [
            'challenge' => $this->formatChallenge($challenge),
            'joined' => $joined,
            'completed_days' => $completedDays,
        ];
    }

    public function join(Challenge $challenge): array
    {
        $user = Auth::user();
        $trainee = $user->traineeProfile;

        if (!$trainee) {
            throw new AuthorizationException('Only trainees can join challenges.');
        }

        if (!$challenge->isJoinable()) {
            throw new AuthorizationException('Challenge is not open for joining.');
        }

        if (ChallengeJoin::where('trainee_id', $trainee->id)->where('challenge_id', $challenge->id)->exists()) {
            throw ValidationException::withMessages(['challenge' => 'You have already joined this challenge.']);
        }

        $join = ChallengeJoin::create([
            'trainee_id' => $trainee->id,
            'challenge_id' => $challenge->id,
            'joined_at' => now(),
        ]);

        ChallengeLeaderboard::firstOrCreate([
            'challenge_id' => $challenge->id,
            'trainee_id' => $trainee->id,
        ]);

        $status = now()->lt($challenge->start_date) ? 'upcoming' : 'ongoing';

        return [
            'message' => "Joined successfully — challenge is {$status}.",
            'joined_at' => $join->joined_at,
        ];
    }

    public function markDayCompleted(Challenge $challenge, array $data): array
    {
        $user = Auth::user();
        $trainee = $user->traineeProfile;

        if (!$trainee) {
            throw new AuthorizationException('Only trainees can mark challenge days.');
        }

        $dayNumber = (int) $data['day_number'];
        $startDate = Carbon::parse($challenge->start_date);
        $endDate = Carbon::parse($challenge->end_date);
        $today = Carbon::today();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        if ($dayNumber < 1 || $dayNumber > $totalDays) {
            throw ValidationException::withMessages(['day_number' => 'Invalid day number.']);
        }

        $join = ChallengeJoin::where('trainee_id', $trainee->id)
            ->where('challenge_id', $challenge->id)
            ->first();

        if (!$join) {
            throw new AuthorizationException('You are not part of this challenge.');
        }

        $joinedAt = Carbon::parse($join->joined_at);
        $challengeDayDate = $startDate->copy()->addDays($dayNumber - 1);

        if ($challengeDayDate->isBefore($joinedAt->startOfDay())) {
            throw new AuthorizationException('You cannot mark days before you joined.');
        }

        if ($challengeDayDate->gt($today)) {
            throw new AuthorizationException('You cannot mark future days.');
        }

        if (ChallengeDayCompletion::where([
            'challenge_id' => $challenge->id,
            'trainee_id' => $trainee->id,
            'day_number' => $dayNumber,
        ])->exists()) {
            throw ValidationException::withMessages(['day' => 'Already marked as completed.']);
        }

        ChallengeDayCompletion::create([
            'challenge_id' => $challenge->id,
            'trainee_id' => $trainee->id,
            'day_number' => $dayNumber,
        ]);

        $completedCount = ChallengeDayCompletion::where('challenge_id', $challenge->id)
            ->where('trainee_id', $trainee->id)
            ->count();

        $leaderboard = ChallengeLeaderboard::firstOrCreate([
            'challenge_id' => $challenge->id,
            'trainee_id' => $trainee->id,
        ]);

        $leaderboard->update([
            'completed_days' => $completedCount,
            'badge_earned' => $completedCount === $totalDays,
        ]);

        if ($completedCount === $totalDays && $challenge->badge_id) {
            if (!$trainee->badges->contains($challenge->badge_id)) {
                $trainee->badges()->attach($challenge->badge_id);
            }
        }

        return ['message' => "Day {$dayNumber} marked as completed."];
    }

    public function leaderboard(Challenge $challenge): array
    {
        $board = ChallengeLeaderboard::with(['trainee.user'])
            ->where('challenge_id', $challenge->id)
            ->orderByDesc('completed_days')
            ->get()
            ->map(fn($row) => [
                'trainee_id' => $row->trainee_id,
                'name' => $row->trainee->user->name,
                'completed_days' => $row->completed_days,
                'badge_earned' => $row->badge_earned,
            ]);

        return ['leaderboard' => $board];
    }

    private function formatChallenge(Challenge $challenge): array
    {
        $durationDays = Carbon::parse($challenge->start_date)
            ->diffInDays(Carbon::parse($challenge->end_date)) + 1;

        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'description' => $challenge->description,
            'image_url' => $challenge->getFirstMediaUrl('challenges'),
            'badge' => $challenge->badge?->name,
            'duration_days' => $durationDays,
            'start_date' => $challenge->start_date,
            'status' => now()->lt($challenge->start_date)
                ? 'upcoming'
                : (now()->gt($challenge->end_date) ? 'completed' : 'ongoing'),
            'remaining' => $challenge->remainingTime(),
        ];
    }
}
