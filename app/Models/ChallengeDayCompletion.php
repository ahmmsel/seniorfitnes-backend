<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChallengeDayCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'trainee_id',
        'day_number',
        'completed_at',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }
}
