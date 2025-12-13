<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Meal extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'date',
        'type',
        'calories',
        'protein',
        'carbs',
        'fats'
    ];

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('meals');
    }

    public function plans(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Plan::class, 'plan_meal', 'meal_id', 'plan_id')
            ->withTimestamps();
    }
}
