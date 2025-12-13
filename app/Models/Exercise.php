<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Exercise extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'instructions'
    ];

    protected $appends = ['image_url'];

    public function workouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class)
            ->withPivot('sets', 'reps')
            ->withTimestamps();
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('exercises');
    }
}
