<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'session_id',
        'description',
    ];

    public function trainee()
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function session()
    {
        return $this->belongsTo(TrackingSession::class, 'session_id');
    }

    public function likes()
    {
        return $this->hasMany(ProgressLike::class, 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(ProgressComment::class, 'post_id');
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function commentsCount()
    {
        return $this->comments()->count();
    }

    public function isLikedBy($traineeId): bool
    {
        return $this->likes()->where('trainee_id', $traineeId)->exists();
    }
}
