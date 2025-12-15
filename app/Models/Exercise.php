<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Exercise extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'instructions'
    ];

    protected $appends = ['image_url'];

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('webp')
            ->format('webp')
            ->quality(75)
            ->nonQueued();
    }

    public function workouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class)
            ->withPivot('sets', 'reps')
            ->withTimestamps();
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('exercises', 'webp') ?: $this->getFirstMediaUrl('exercises');
    }
}
