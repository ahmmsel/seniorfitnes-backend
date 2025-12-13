<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

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
    public function sendMessage(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }

    /**
     * Determine whether the user can mark messages as read.
     */
    public function markAsRead(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user);
    }
}
