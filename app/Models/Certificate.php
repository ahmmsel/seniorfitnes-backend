<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Certificate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'profile_id',
        'name',
        'description'
    ];

    protected $appends = ['image_url'];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('webp')
            ->format('webp')
            ->quality(75)
            ->nonQueued();
    }

    public function coachProfile(): BelongsTo
    {
        return $this->belongsTo(CoachProfile::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('certificates', 'webp') ?: $this->getFirstMediaUrl('certificates');
    }
}
