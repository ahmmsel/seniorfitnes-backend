<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'sender_type',
        'body',
        'data',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Get the chat that owns the message
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who sent the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if the message is mine for a given user
     */
    public function isMine(User $user): bool
    {
        return $this->sender_id === $user->id;
    }

    /**
     * Scope to get unread messages for a user
     */
    public function scopeUnreadForUser($query, User $user)
    {
        return $query->where('sender_id', '!=', $user->id)
            ->whereNull('read_at');
    }

    /**
     * Mark the message as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * @deprecated Legacy relation - use sender instead
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
