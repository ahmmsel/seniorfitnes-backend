<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $fillable = ['coach_profile_id', 'type', 'title', 'description'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(CoachProfile::class, 'coach_profile_id');
    }

    public function trainees(): BelongsToMany
    {
        return $this->belongsToMany(TraineeProfile::class, 'trainee_plan', 'plan_id', 'trainee_id')
            ->withPivot(['tap_charge_id', 'purchased_at', 'items'])
            ->withTimestamps();
    }

    public function workouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'plan_workout', 'plan_id', 'workout_id')
            ->withTimestamps();
    }

    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'plan_meal', 'plan_id', 'meal_id')
            ->withTimestamps();
    }
}
