<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trainee = $this->trainee;
        $user = $trainee?->user;

        return [
            'id' => $this->id,
            'trainee' => [
                'id' => $trainee?->id,
                'name' => $user?->name,
                'avatar' => $trainee?->profile_image_url,
            ],
            'description' => $this->description,
            'distance' => $this->session?->distance,
            'time_seconds' => $this->session?->time_seconds,
            'pace' => $this->session?->pace,
            'calories' => $this->session?->calories,
            'steps' => $this->session?->steps,
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'is_liked' => $request->user()?->traineeProfile
                ? $this->isLikedBy($request->user()->traineeProfile->id)
                : false,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
