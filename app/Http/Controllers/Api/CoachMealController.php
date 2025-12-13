<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreMealRequest;
use App\Http\Requests\Coach\UpdateMealRequest;
use App\Models\Meal;
use App\Services\MealService;
use Illuminate\Http\Request;

class CoachMealController extends Controller
{
    public function __construct(protected MealService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $meals = $this->service->getAll($perPage);

        return response()->json($meals);
    }

    public function store(StoreMealRequest $request)
    {
        $user = $request->user();
        if (! $user->coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $meal = Meal::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'] ?? null,
            'type' => $data['type'] ?? null,
            'calories' => $data['calories'] ?? null,
            'protein' => $data['protein'] ?? null,
            'carbs' => $data['carbs'] ?? null,
            'fats' => $data['fats'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $meal->addMediaFromRequest('image')->toMediaCollection('meals');
        }

        return response()->json(['message' => 'Meal created', 'meal' => $meal->load('media')], 201);
    }

    public function show(Request $request, Meal $meal)
    {
        $user = $request->user();
        if (! $user->coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        return response()->json(['meal' => $meal->load('media')]);
    }

    public function update(UpdateMealRequest $request, Meal $meal)
    {
        $user = $request->user();
        if (! $user->coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $data = $request->validated();

        $meal->update(array_filter([
            'name' => $data['name'] ?? $meal->name,
            'description' => $data['description'] ?? $meal->description,
            'date' => $data['date'] ?? $meal->date,
            'type' => $data['type'] ?? $meal->type,
            'calories' => $data['calories'] ?? $meal->calories,
            'protein' => $data['protein'] ?? $meal->protein,
            'carbs' => $data['carbs'] ?? $meal->carbs,
            'fats' => $data['fats'] ?? $meal->fats,
        ]));

        if ($request->hasFile('image')) {
            $meal->clearMediaCollection('meals');
            $meal->addMediaFromRequest('image')->toMediaCollection('meals');
        }

        return response()->json(['message' => 'Meal updated', 'meal' => $meal->fresh()->load('media')]);
    }

    public function destroy(Request $request, Meal $meal)
    {
        $user = $request->user();
        if (! $user->coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $meal->clearMediaCollection('meals');
        $meal->delete();

        return response()->json(['message' => 'Meal deleted']);
    }
}
