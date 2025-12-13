<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkoutResource;
use App\Models\Workout;
use App\Services\WorkoutCatalogService;
use Illuminate\Http\Request;

class WorkoutCatalogController extends Controller
{
    public function __construct(protected WorkoutCatalogService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 12);
        $perPage = $perPage > 0 ? $perPage : 12;

        $filters = $request->only('q');

        $workouts = $this->service->list($filters, $perPage);

        return WorkoutResource::collection($workouts);
    }

    public function show(Request $request, Workout $workout)
    {
        $workout = $this->service->show($workout);

        return new WorkoutResource($workout);
    }
}
