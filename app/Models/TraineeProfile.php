<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TraineeProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'height',
        'weight',
        'goal',
        'level',
        'body_type'
    ];

    protected $appends = ['profile_image_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challengeDaysCompleted()
    {
        return $this->hasMany(ChallengeDayCompletion::class, 'trainee_id');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_trainee', 'trainee_id', 'badge_id');
    }

    public function challengesJoined(): HasMany
    {
        return $this->hasMany(ChallengeJoin::class, 'trainee_id');
    }

    public function target(): HasOne
    {
        return $this->hasOne(Target::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'trainee_plan', 'trainee_id', 'plan_id')
            ->withPivot(['tap_charge_id', 'purchased_at', 'items'])
            ->withTimestamps();
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('profile_picture');
        return $url ?: null;
    }
}
