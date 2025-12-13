<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\TraineeProfile;

class WorkoutResource extends JsonResource
{
    public function toArray($request)
    {
        $user = Auth::user();
        $trainee = $user?->traineeProfile;

        // Determine if this workout is started for a trainee.
        // If the authenticated user is a trainee, use their profile.
        // If the authenticated user is a coach and provides a `trainee_id` query
        // parameter, check the specified trainee's workout status so coaches can
        // view trainee-specific progress.
        $isStarted = false;

        if ($trainee) {
            $isStarted = $this->workoutLogs()
                ->where('trainee_id', $trainee->id)
                ->where('status', 'in_progress')
                ->exists();
        } elseif ($user?->coachProfile && $request->get('trainee_id')) {
            $traineeId = (int) $request->get('trainee_id');
            $isStarted = $this->workoutLogs()
                ->where('trainee_id', $traineeId)
                ->where('status', 'in_progress')
                ->exists();
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'is_started' => $isStarted,
            'exercises' => $this->exercises->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'instructions' => $exercise->instructions,
                    'image_url' => $exercise->image_url,
                    'pivot' => [
                        'sets' => $exercise->pivot->sets,
                        'reps' => $exercise->pivot->reps,
                    ],
                ];
            }),
        ];
    }
}
