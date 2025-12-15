<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CoachProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'coach_id',
        'profile_status',
        'description',
        'specialty',
        'years_of_experience',
        'nutrition_price',
        'workout_price',
        'full_package_price'
    ];

    protected $appends = ['profile_image_url'];

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('webp')
            ->format('webp')
            ->quality(75)
            ->nonQueued();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'profile_id');
    }

    public function transformations(): HasMany
    {
        return $this->hasMany(Transformation::class, 'profile_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(\App\Models\Plan::class, 'coach_profile_id');
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('profile_picture', 'webp') ?: $this->getFirstMediaUrl('profile_picture');
        return $url ?: null;
    }
}
