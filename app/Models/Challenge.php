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

    public function registerMediaConversions(?Media $media = null): void
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
        $now = now();
        $end = $this->end_date;

        // If challenge has ended, return zero
        if ($now->greaterThanOrEqualTo($end)) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
            ];
        }

        // Calculate time remaining
        $diff = $now->diff($end);
        return [
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
        ];
    }

    public function getStatus(): string
    {
        $now = now();

        if ($now->lessThan($this->start_date)) {
            return 'upcoming';
        }

        if ($now->greaterThan($this->end_date)) {
            return 'completed';
        }

        return 'ongoing';
    }
}
