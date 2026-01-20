<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use App\Models\TraineePlan;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their chats
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create chats
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }

    /**
     * Determine whether the user can send messages to the chat.
     */
    public function sendMessage(User $user, Chat $chat): Response
    {
        if (!$chat->isParticipant($user)) {
            return Response::deny(__('chat.not_participant'));
        }

        // Check if user is trainee and has purchased a plan from the coach
        $traineeProfile = $user->traineeProfile;
        if ($traineeProfile && $chat->trainee_id === $user->id) {
            $hasPurchasedPlan = TraineePlan::where('trainee_id', $traineeProfile->id)
                ->where(function ($query) use ($chat) {
                    $query->whereHas('coachProfile', function ($q) use ($chat) {
                        $q->where('user_id', $chat->coach_id);
                    })
                        ->orWhereHas('plan', function ($q) use ($chat) {
                            $q->whereHas('coach', function ($q2) use ($chat) {
                                $q2->where('user_id', $chat->coach_id);
                            });
                        });
                })
                ->exists();

            if (!$hasPurchasedPlan) {
                return Response::deny(__('chat.no_plan_purchased'));
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can mark messages as read.
     */
    public function markAsRead(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }
}
