<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use App\Services\MealService;

class MealController extends Controller
{
    public function __construct(protected MealService $service) {}

    public function index()
    {
        $perPage = request()->get('per_page', 10);

        $data = $this->service->getAll($perPage);

        return MealResource::collection($data);
    }

    public function show(Meal $meal)
    {
        $data = $this->service->get($meal);

        return new MealResource($data);
    }
}
