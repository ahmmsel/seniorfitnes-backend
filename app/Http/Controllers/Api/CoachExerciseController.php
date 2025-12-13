<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreExerciseRequest;
use App\Http\Requests\Coach\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\Request;

class CoachExerciseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $exercises = Exercise::with('media')->latest()->get();

        return response()->json(['exercises' => $exercises]);
    }

    public function store(StoreExerciseRequest $request)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $exercise = Exercise::create([
            'name' => $data['name'],
            'instructions' => $data['instructions'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $exercise->addMediaFromRequest('image')->toMediaCollection('exercises');
        }

        return response()->json(['message' => 'Exercise created', 'exercise' => $exercise], 201);
    }

    public function show(Request $request, Exercise $exercise)
    {
        return response()->json(['exercise' => $exercise->load('media')]);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $exercise->update(array_filter([
            'name' => $data['name'] ?? $exercise->name,
            'instructions' => $data['instructions'] ?? $exercise->instructions,
        ]));

        if ($request->hasFile('image')) {
            $exercise->clearMediaCollection('exercises');
            $exercise->addMediaFromRequest('image')->toMediaCollection('exercises');
        }

        return response()->json(['message' => 'Exercise updated', 'exercise' => $exercise->fresh()]);
    }

    public function destroy(Request $request, Exercise $exercise)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $exercise->clearMediaCollection('exercises');
        $exercise->delete();

        return response()->json(['message' => 'Exercise deleted']);
    }
}
