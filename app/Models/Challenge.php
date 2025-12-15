<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Challenge extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'badge_id',
        'start_date',
        'end_date'
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

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('challenges', 'webp') ?: $this->getFirstMediaUrl('challenges');
    }

    public function isJoinable(): bool
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date);
    }

    public function remainingTime(): array
    {
        $end = now()->diff($this->end_date);
        return [
            'days' => $end->d,
            'hours' => $end->h,
            'minutes' => $end->i,
        ];
    }
}
