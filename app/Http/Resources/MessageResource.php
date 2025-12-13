<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'message' => $this->body,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar' => $this->sender->avatar ?? null,
                'profile_image_url' => $this->getProfileImageUrl($this->sender, $this->sender_type),
            ],
            'is_mine' => $this->isMine($user),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Get profile image URL based on user type
     */
    private function getProfileImageUrl($user, string $userType): ?string
    {
        if (!$user) {
            return null;
        }

        if ($userType === 'coach') {
            return $user->coachProfile?->profile_image_url;
        }

        if ($userType === 'trainee') {
            return $user->traineeProfile?->profile_image_url;
        }

        return null;
    }
}
