<?php

namespace App\Services;

use App\Models\Meal;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MealService
{
    public function getAll(int $perPage = 10)
    {
        $meals = Meal::with(['media'])->latest()->paginate($perPage);

        return $meals;
    }

    public function get(Meal $meal)
    {
        if (!$meal) {
            throw new ModelNotFoundException('Meal not found.');
        }

        return $meal->load(['media']);
    }
}
