<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'trainee_id',
        'comment',
    ];

    public function post()
    {
        return $this->belongsTo(ProgressPost::class, 'post_id');
    }

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }
}
