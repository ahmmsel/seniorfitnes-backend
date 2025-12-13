<?php

namespace App\Services;

use App\Models\Workout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WorkoutCatalogService
{
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator|Collection
    {
        $query = Workout::with(['exercises', 'media'])->latest();

        if (!empty($filters['q'])) {
            $query->where('name', 'like', "%{$filters['q']}%")
                ->orWhere('description', 'like', "%{$filters['q']}%");
        }

        if ($perPage <= 0) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    public function show(Workout $workout): Workout
    {
        return $workout->load(['exercises', 'media']);
    }
}
