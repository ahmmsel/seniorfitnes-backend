<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workout\LogExerciseRequest;
use App\Services\WorkoutService;
use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;

class WorkoutController extends Controller
{
    public function __construct(protected WorkoutService $service) {}

    public function start(Workout $workout): JsonResponse
    {
        $log = $this->service->startWorkout($workout);
        return response()->json([
            'message' => 'Workout started successfully.',
            'workout_log' => $log,
        ], 201);
    }

    public function logExercise(LogExerciseRequest $request, Workout $workout, Exercise $exercise): JsonResponse
    {
        $result = $this->service->logExercise($workout, $exercise, $request->validated());
        return response()->json($result, 201);
    }

    public function progress(Workout $workout): JsonResponse
    {
        return response()->json($this->service->progress($workout));
    }

    public function completed(): JsonResponse
    {
        return response()->json(['workouts' => $this->service->completedLogs()]);
    }

    public function completedFor(Workout $workout): JsonResponse
    {
        return response()->json(['workouts' => $this->service->completedForWorkout($workout)]);
    }

    public function logs(): JsonResponse
    {
        return response()->json(['workouts' => $this->service->logs()]);
    }
}
