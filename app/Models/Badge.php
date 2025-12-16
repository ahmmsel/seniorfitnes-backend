<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Badge extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'description'];

    protected $appends = ['image_url'];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('webp')
            ->format('webp')
            ->quality(75)
            ->nonQueued();
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('certificates', 'webp') ?: $this->getFirstMediaUrl('certificates');
    }

    public function trainees(): BelongsToMany
    {
        return $this->belongsToMany(TraineeProfile::class, 'badge_trainee', 'badge_id', 'trainee_id');
    }
}
