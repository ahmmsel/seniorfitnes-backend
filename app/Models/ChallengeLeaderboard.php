<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeLeaderboard extends Model
{
    protected $fillable = ['trainee_id', 'challenge_id', 'completed_days', 'badge_earned'];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }
}
