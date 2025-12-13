<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreWorkoutRequest;
use App\Http\Requests\Coach\UpdateWorkoutRequest;
use App\Models\Workout;
use App\Models\Exercise;
use Illuminate\Http\Request;

class CoachWorkoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $workouts = Workout::whereHas('plans', function ($q) use ($profile) {
            $q->where('coach_profile_id', $profile->id);
        })->orWhereHas('coach', function ($q) use ($profile) {
            $q->where('id', $profile->id);
        })->with('exercises')->get();

        return response()->json(['workouts' => $workouts]);
    }

    public function store(StoreWorkoutRequest $request)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $workout = Workout::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['exercise_ids'])) {
            $workout->exercises()->sync($data['exercise_ids']);
        }

        if ($request->hasFile('image')) {
            $workout->addMediaFromRequest('image')->toMediaCollection('workouts');
        }

        return response()->json(['message' => 'Workout created', 'workout' => $workout->load('exercises')], 201);
    }

    public function show(Request $request, Workout $workout)
    {
        return response()->json(['workout' => $workout->load('exercises')]);
    }

    public function update(UpdateWorkoutRequest $request, Workout $workout)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $workout->update(array_filter([
            'name' => $data['name'] ?? $workout->name,
            'slug' => $data['slug'] ?? $workout->slug,
            'description' => $data['description'] ?? $workout->description,
        ]));

        if (array_key_exists('exercise_ids', $data)) {
            $workout->exercises()->sync($data['exercise_ids'] ?? []);
        }

        if ($request->hasFile('image')) {
            $workout->clearMediaCollection('workouts');
            $workout->addMediaFromRequest('image')->toMediaCollection('workouts');
        }

        return response()->json(['message' => 'Workout updated', 'workout' => $workout->fresh('exercises')]);
    }

    public function destroy(Request $request, Workout $workout)
    {
        $user = $request->user();
        $profile = $user->coachProfile;
        if (! $profile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $workout->clearMediaCollection('workouts');
        $workout->delete();

        return response()->json(['message' => 'Workout deleted']);
    }
}
