<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    /**
     * Determine whether the user can view any plans.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the plan.
     */
    public function view(User $user, Plan $plan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create plans.
     */
    public function create(User $user): bool
    {
        return method_exists($user, 'coachProfile') && ! is_null($user->coachProfile);
    }

    /**
     * Determine whether the user can update the plan.
     */
    public function update(User $user, Plan $plan): bool
    {
        return method_exists($user, 'coachProfile') && ! is_null($user->coachProfile)
            && $plan->coach_profile_id === $user->coachProfile->id;
    }

    /**
     * Determine whether the user can delete the plan.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $this->update($user, $plan);
    }

    /**
     * Determine whether the user can assign this plan to a trainee.
     */
    public function assign(User $user, Plan $plan): bool
    {
        return $this->update($user, $plan);
    }
}
