<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'status',
        'distance',
        'time_seconds',
        'bpm',
        'steps',
        'pace',
        'calories',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'distance' => 'float',
        'pace' => 'float',
        'calories' => 'float',
        'time_seconds' => 'integer',
        'bpm' => 'integer',
        'steps' => 'integer',
    ];

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function progressPost()
    {
        return $this->hasOne(ProgressPost::class, 'session_id');
    }

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }
}
