<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatService
{
    /**
     * Get all chats for a user
     */
    public function getChatsForUser(User $user): Collection
    {
        return Chat::forUser($user)
            ->with(['coach', 'trainee', 'latestMessage.sender'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get a specific chat with messages
     */
    public function getChatWithMessages(Chat $chat, int $page = 1, int $perPage = 50): array
    {
        $messages = $chat->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'chat' => $chat->load(['coach', 'trainee']),
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'has_more_pages' => $messages->hasMorePages(),
            ],
        ];
    }

    /**
     * Send a message in a chat
     */
    public function sendMessage(Chat $chat, User $sender, string $messageContent): Message
    {
        // Determine sender type based on user's role in the chat
        $senderType = $this->determineSenderType($chat, $sender);

        // Create the message
        $message = $chat->messages()->create([
            'sender_id' => $sender->id,
            'sender_type' => $senderType,
            'body' => $messageContent,
        ]);

        // Update chat's last message
        $chat->update([
            'last_message' => $messageContent,
            'last_message_at' => now(),
        ]);

        // Load sender relationship for broadcasting
        $message->load('sender');

        // Broadcast the message to other participants (not the sender)
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return $message;
    }

    /**
     * Start or get existing chat between coach and trainee
     */
    public function startChatBetween(User $currentUser, int $participantId, string $participantType): Chat
    {
        // Determine coach and trainee IDs based on current user and participant
        if ($participantType === 'coach') {
            // Current user is trainee, participant is coach
            $coachId = $participantId;
            $traineeId = $currentUser->id;
        } else {
            // Current user is coach, participant is trainee
            $coachId = $currentUser->id;
            $traineeId = $participantId;
        }

        return Chat::findOrCreateBetween($coachId, $traineeId);
    }

    /**
     * Mark messages as read for a user
     */
    public function markMessagesAsRead(Chat $chat, User $user): void
    {
        $chat->markAsReadForUser($user);
    }

    /**
     * Determine sender type based on user's role in the chat
     */
    private function determineSenderType(Chat $chat, User $sender): string
    {
        if ($chat->coach_id === $sender->id) {
            return 'coach';
        }

        if ($chat->trainee_id === $sender->id) {
            return 'trainee';
        }

        throw new \InvalidArgumentException('User is not a participant in this chat.');
    }

    /**
     * Get profile image URL based on user type
     */
    private function getProfileImageUrl(User $user, string $userType): ?string
    {
        if ($userType === 'coach') {
            $coachProfile = $user->coachProfile;
            return $coachProfile?->profile_image_url;
        }

        if ($userType === 'trainee') {
            $traineeProfile = $user->traineeProfile;
            return $traineeProfile?->profile_image_url;
        }

        return null;
    }
}
