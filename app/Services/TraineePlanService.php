<?php

namespace App\Services;

use App\Models\TraineePlan;
use App\Models\CoachProfile;

class TraineePlanService
{
    /**
     * Return pending TraineePlans for a coach profile.
     *
     * @param CoachProfile $coach
     * @return \Illuminate\Database\Eloquent\Collection|TraineePlan[]
     */
    public function pendingForCoach(CoachProfile $coach)
    {
        return $this->baseQuery($coach)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Return completed TraineePlans for a coach profile.
     *
     * @param CoachProfile $coach
     * @return \Illuminate\Database\Eloquent\Collection|TraineePlan[]
     */
    public function completedForCoach(CoachProfile $coach)
    {
        return $this->baseQuery($coach)
            ->where('status', 'completed')
            ->orderByDesc('purchased_at')
            ->get();
    }

    private function baseQuery(CoachProfile $coach)
    {
        return TraineePlan::with([
            'trainee.user',
            'trainee.badges',
            'trainee.target',
            'coachProfile.user',
            'plan.workouts',
            'plan.meals',
        ])->where('coach_profile_id', $coach->id);
    }
}
