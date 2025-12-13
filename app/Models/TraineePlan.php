<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraineePlan extends Model
{
    protected $table = 'trainee_plan';

    protected $fillable = [
        'trainee_id',
        'plan_id',
        'coach_profile_id',
        'plan_type',
        'items',
        'tap_charge_id',
        'purchased_at',
        'status'
    ];

    protected $casts = [
        'items' => 'array',
        'purchased_at' => 'datetime',
    ];

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function coachProfile()
    {
        return $this->belongsTo(CoachProfile::class, 'coach_profile_id');
    }
}
