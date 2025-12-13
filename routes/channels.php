<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Check if user is a participant in the chat (coach or trainee)
    return \App\Models\Chat::where('id', $chatId)
        ->where(function ($query) use ($user) {
            $query->where('coach_id', $user->id)
                ->orWhere('trainee_id', $user->id);
        })
        ->exists();
});
