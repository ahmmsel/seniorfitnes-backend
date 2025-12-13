<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\CommentRequest;
use App\Http\Resources\ProgressPostResource;
use App\Http\Resources\ProgressCommentResource;
use App\Models\ProgressPost;
use App\Models\ProgressLike;
use App\Models\ProgressComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    /**
     * Get community feed of shared progress
     * GET /api/community/feed
     */
    public function feed(Request $request): JsonResponse
    {
        $posts = ProgressPost::with(['trainee.user', 'session', 'likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'posts' => ProgressPostResource::collection($posts->items()),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Like a progress post
     * POST /api/progress/{id}/like
     */
    public function like(Request $request, $postId): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;

        if (!$trainee) {
            return response()->json(['message' => 'Trainee profile not found'], 404);
        }

        $post = ProgressPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Check if already liked
        $existingLike = ProgressLike::where('post_id', $postId)
            ->where('trainee_id', $trainee->id)
            ->first();

        if ($existingLike) {
            // Unlike (toggle behavior)
            $existingLike->delete();

            return response()->json([
                'message' => 'Post unliked',
                'likes_count' => $post->likes()->count(),
            ]);
        }

        // Create like
        ProgressLike::create([
            'post_id' => $postId,
            'trainee_id' => $trainee->id,
        ]);

        return response()->json([
            'message' => 'Post liked',
            'likes_count' => $post->likes()->count(),
        ]);
    }

    /**
     * Comment on a progress post
     * POST /api/progress/{id}/comment
     */
    public function comment(CommentRequest $request, $postId): JsonResponse
    {
        $trainee = $request->user()->traineeProfile;

        if (!$trainee) {
            return response()->json(['message' => 'Trainee profile not found'], 404);
        }

        $post = ProgressPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comment = ProgressComment::create([
            'post_id' => $postId,
            'trainee_id' => $trainee->id,
            'comment' => $request->validated()['comment'],
        ]);

        $comment->load(['trainee.user']);

        return response()->json([
            'message' => 'Comment added',
            'comment' => new ProgressCommentResource($comment),
        ], 201);
    }

    /**
     * Get comments for a progress post
     * GET /api/progress/{id}/comments
     */
    public function comments(Request $request, $postId): JsonResponse
    {
        $post = ProgressPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = ProgressComment::with(['trainee.user'])
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'comments' => ProgressCommentResource::collection($comments->items()),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }
}
