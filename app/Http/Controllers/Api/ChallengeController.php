<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Challenge\ChallengeMarkDayRequest;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;

class ChallengeController extends Controller
{
    public function __construct(protected ChallengeService $challengeService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->challengeService->getAll());
    }

    public function show(Challenge $challenge): JsonResponse
    {
        return response()->json($this->challengeService->show($challenge));
    }

    public function join(Challenge $challenge): JsonResponse
    {
        return response()->json($this->challengeService->join($challenge), 201);
    }

    public function markDayCompleted(ChallengeMarkDayRequest $request, Challenge $challenge): JsonResponse
    {
        return response()->json($this->challengeService->markDayCompleted($challenge, $request->validated()));
    }

    public function leaderboard(Challenge $challenge): JsonResponse
    {
        return response()->json($this->challengeService->leaderboard($challenge));
    }
}
