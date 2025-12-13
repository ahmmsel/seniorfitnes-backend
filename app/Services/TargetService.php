<?php

namespace App\Services;

use App\Models\Target;
use App\Models\TraineeProfile;

class TargetService
{
    public function createOrUpdate(TraineeProfile $profile): Target
    {
        $weight = $profile->weight;
        $height = $profile->height;

        // Basic BMR formula
        $bmr = (10 * $weight) + (6.25 * $height) - 125;

        // Activity factor
        $activityFactor = match ($profile->level) {
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'active' => 1.55,
            'very_active' => 1.725,
            default => 1.2,
        };

        $tdee = $bmr * $activityFactor;

        // Adjust calories based on goal
        $calories = match ($profile->goal) {
            'lose_weight' => $tdee * 0.85,
            'build_muscle' => $tdee * 1.15,
            'improve_cardio', 'maintain_fitness' => $tdee,
            default => $tdee,
        };

        // Water and steps calculation
        $water = round($weight * 35 / 1000, 2); // liters
        $steps = match ($profile->level) {
            'sedentary' => 5000,
            'lightly_active' => 7000,
            'active' => 10000,
            'very_active' => 13000,
            default => 7000,
        };

        // Create or update target for this trainee
        return Target::updateOrCreate(
            ['trainee_profile_id' => $profile->id],
            [
                'target_calories' => round($calories),
                'target_steps' => $steps,
                'target_water_liters' => $water,
            ]
        );
    }
}
