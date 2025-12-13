<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    protected $fillable = [
        'trainee_profile_id',
        'target_calories',
        'target_steps',
        'target_water_liters',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class);
    }
}
