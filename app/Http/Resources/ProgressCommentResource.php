<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
