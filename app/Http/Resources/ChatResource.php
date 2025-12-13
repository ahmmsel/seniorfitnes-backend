<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $otherParticipant = $this->getOtherParticipant($user);

        return [
            'id' => $this->id,
            'other_participant' => $otherParticipant ? [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->name,
                'email' => $otherParticipant->email,
                'avatar' => $otherParticipant->avatar ?? null,
                'type' => $this->coach_id === $otherParticipant->id ? 'coach' : 'trainee',
                'profile_image_url' => $this->getProfileImageUrl($otherParticipant),
            ] : null,
            'last_message' => $this->last_message,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count' => $this->getUnreadCountForUser($user),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get profile image URL based on user type
     */
    private function getProfileImageUrl($user): ?string
    {
        if (!$user) {
            return null;
        }

        $userType = $this->coach_id === $user->id ? 'coach' : 'trainee';

        if ($userType === 'coach') {
            return $user->coachProfile?->profile_image_url;
        }

        if ($userType === 'trainee') {
            return $user->traineeProfile?->profile_image_url;
        }

        return null;
    }
}
