<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Transformation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'profile_id',
        'title',
        'description'
    ];

    protected $appends = ['image_url'];

    public function coachProfile(): BelongsTo
    {
        return $this->belongsTo(CoachProfile::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('transformations');
    }
}
