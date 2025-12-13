<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    protected $fillable = ['trainee_id', 'workout_id', 'status', 'started_at', 'completed_at'];

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class);
    }
}
