<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChatController extends Controller
{
    use AuthorizesRequests;

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get all chats for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $chats = $this->chatService->getChatsForUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Chats retrieved successfully.',
            'data' => [
                'chats' => ChatResource::collection($chats),
            ],
        ]);
    }

    /**
     * Get specific chat with messages
     */
    public function show(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('view', $chat);

        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 50), 100); // Max 100 messages per page

        $chatData = $this->chatService->getChatWithMessages($chat, $page, $perPage);

        // Mark messages as read for the current user
        $this->chatService->markMessagesAsRead($chat, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Chat retrieved successfully.',
            'data' => [
                'chat' => new ChatResource($chatData['chat']),
                'messages' => MessageResource::collection($chatData['messages']),
                'pagination' => $chatData['pagination'],
            ],
        ]);
    }

    /**
     * Start a new chat or get existing one
     */
    public function store(StartChatRequest $request): JsonResponse
    {
        $user = $request->user();
        $participantId = $request->participant_id;
        $participantType = $request->participant_type;

        // Validate that user cannot chat with themselves
        if ($participantId === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot start a chat with yourself.',
                'data' => [],
            ], 422);
        }

        try {
            $chat = $this->chatService->startChatBetween($user, $participantId, $participantType);

            return response()->json([
                'status' => 'success',
                'message' => 'Chat started successfully.',
                'data' => [
                    'chat' => new ChatResource($chat->load(['coach', 'trainee'])),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to start chat: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Send a message to a chat
     */
    public function sendMessage(SendMessageRequest $request, Chat $chat): JsonResponse
    {
        $this->authorize('sendMessage', $chat);

        try {
            $message = $this->chatService->sendMessage(
                $chat,
                $request->user(),
                $request->message
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully.',
                'data' => [
                    'message' => new MessageResource($message),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to send message: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Mark messages as read in a chat
     */
    public function markAsRead(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('markAsRead', $chat);

        try {
            $this->chatService->markMessagesAsRead($chat, $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Messages marked as read.',
                'data' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to mark messages as read: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Legacy endpoints for backward compatibility

    /**
     * @deprecated Use index() instead
     */
    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();
        $chats = $this->chatService->getChatsForUser($user)->take(5);

        return response()->json([
            'status' => 'success',
            'message' => 'Latest chats retrieved successfully.',
            'data' => [
                'chats' => ChatResource::collection($chats),
            ],
        ]);
    }
}
