<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'trainee_id',
        'last_message',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the coach participant
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the trainee participant
     */
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainee_id');
    }

    /**
     * Get all messages in this chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get the other participant for the given user
     */
    public function getOtherParticipant(User $user)
    {
        if ($this->coach_id === $user->id) {
            return $this->trainee;
        }

        if ($this->trainee_id === $user->id) {
            return $this->coach;
        }

        return null;
    }

    /**
     * Get unread messages count for a specific user
     */
    public function getUnreadCountForUser(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark messages as read for a specific user
     */
    public function markAsReadForUser(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Check if user is participant in this chat
     */
    public function isParticipant(User $user): bool
    {
        return $this->coach_id === $user->id || $this->trainee_id === $user->id;
    }

    /**
     * Get chat between coach and trainee
     */
    public static function findOrCreateBetween(int $coachId, int $traineeId): self
    {
        return self::firstOrCreate([
            'coach_id' => $coachId,
            'trainee_id' => $traineeId,
        ]);
    }

    /**
     * Scope to get chats for a specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('coach_id', $user->id)
            ->orWhere('trainee_id', $user->id);
    }

    /**
     * @deprecated Legacy method - use coach/trainee relations instead
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_user')->withTimestamps()->withPivot('last_read_at');
    }

    /**
     * @deprecated Legacy method - use latestMessage() instead
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
