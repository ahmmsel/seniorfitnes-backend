<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\TraineePlan;
use App\Models\CoachProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PlanService
{
    /** List all plans with relations */
    public function index()
    {
        return Plan::with(['coach', 'workouts', 'meals'])->get();
    }

    /** Show a single plan with relations */
    public function show(Plan $plan)
    {
        return $plan->load(['coach', 'workouts', 'meals']);
    }

    public function store(array $data, ?CoachProfile $coach = null): Plan
    {
        return DB::transaction(function () use ($data, $coach) {
            if (empty($data['trainee_plan_id'])) {
                throw new \InvalidArgumentException('trainee_plan_id is required');
            }

            $tp = TraineePlan::findOrFail((int) $data['trainee_plan_id']);

            if ($tp->status !== 'pending') {
                throw new \RuntimeException('Trainee purchase is not pending');
            }

            // determine coach_profile_id
            $coachProfileId = $coach ? $coach->id : ($data['coach_profile_id'] ?? $tp->coach_profile_id ?? null);
            if (empty($coachProfileId)) {
                throw new \InvalidArgumentException('coach_profile_id is required');
            }

            // create the plan
            $planData = Arr::only($data, ['type', 'title', 'description']);
            $planData['coach_profile_id'] = $coachProfileId;

            $plan = Plan::create($planData);

            // attach workouts/meals if provided
            if (!empty($data['workout_ids']) && is_array($data['workout_ids'])) {
                $plan->workouts()->sync($data['workout_ids']);
            }

            if (!empty($data['meal_ids']) && is_array($data['meal_ids'])) {
                $plan->meals()->sync($data['meal_ids']);
            }

            // update trainee_plan to reference created plan and mark completed
            $tp->plan_id = $plan->id;
            $tp->status = 'completed';
            $tp->save();

            // attach the plan to trainee using stored tap_charge_id/purchased_at/items
            $attachData = [];
            if (!empty($tp->items)) {
                $attachData['items'] = is_string($tp->items) ? $tp->items : json_encode($tp->items);
            }
            $attachData['purchased_at'] = $tp->purchased_at ?: now();
            if (!empty($tp->tap_charge_id)) {
                $attachData['tap_charge_id'] = $tp->tap_charge_id;
            }

            // prevent duplicate attachment for same charge
            if (!empty($attachData['tap_charge_id'])) {
                $exists = $tp->trainee->plans()->wherePivot('tap_charge_id', $attachData['tap_charge_id'])->exists();
                if (!$exists) {
                    $tp->trainee->plans()->attach($plan->id, $attachData);
                }
            } else {
                $tp->trainee->plans()->attach($plan->id, $attachData);
            }

            // notify trainee that coach created the plan
            $traineeUser = optional($tp->trainee)->user;
            if ($traineeUser) {
                $traineeUser->notify(new \App\Notifications\PlanCreatedNotification([
                    'plan_id' => $plan->id,
                    'coach_profile_id' => $tp->coach_profile_id,
                    'trainee_plan_id' => $tp->id,
                ]));
            }

            return $plan->load(['workouts', 'meals']);
        });
    }

    /** Update an existing plan and optionally sync items */
    /**
     * NOTE: update/delete/sync helpers removed — service focused on creation and creation from purchases.
     */

    /** Update an existing plan and optionally sync items */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update(Arr::only($data, ['type', 'title', 'description']));

        // allow updating attached items on update (replace existing)
        if (array_key_exists('workout_ids', $data)) {
            $plan->workouts()->sync($data['workout_ids'] ?? []);
        }

        if (array_key_exists('meal_ids', $data)) {
            $plan->meals()->sync($data['meal_ids'] ?? []);
        }

        return $plan->fresh(['workouts', 'meals']);
    }

    public function delete(Plan $plan): bool
    {
        return (bool) $plan->delete();
    }
}
