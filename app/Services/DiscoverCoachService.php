<?php

namespace App\Services;

use App\Models\CoachProfile;
use Illuminate\Http\Request;

class DiscoverCoachService
{
    public function getCoaches(Request $request)
    {
        $query = CoachProfile::query()
            ->with('user:id,name,email');

        $this->applyFilters($query, $request);

        $perPage = $request->get('per_page', 10);
        $coaches = $query->paginate($perPage);

        $coaches->getCollection()->transform(function ($coach) {
            return [
                'id' => $coach->id,
                'user_id' => $coach->user->id,
                'coach_id' => $coach->id,
                'name' => $coach->user->name,
                'email' => $coach->user->email,
                'specialty' => $coach->specialty,
                'years_of_experience' => $coach->years_of_experience,
                'nutrition_price' => $coach->nutrition_price,
                'workout_price' => $coach->workout_price,
                'full_package_price' => $coach->full_package_price,
                'profile_image_url' => $coach->profile_image_url,
            ];
        });

        return $coaches;
    }

    protected function applyFilters($query, Request $request)
    {
        $filters = [
            'specialty',
            'min_experience',
            'max_experience',
            'min_nutrition_price',
            'max_nutrition_price',
            'min_workout_price',
            'max_workout_price',
            'min_full_package_price',
            'max_full_package_price',
        ];

        foreach ($filters as $filter) {
            if (!$request->filled($filter)) continue;

            switch ($filter) {
                case 'specialty':
                    $query->where('specialty', $request->specialty);
                    break;
                case 'min_experience':
                    $query->where('years_of_experience', '>=', (int)$request->min_experience);
                    break;
                case 'max_experience':
                    $query->where('years_of_experience', '<=', (int)$request->max_experience);
                    break;
                case 'min_nutrition_price':
                    $query->where('nutrition_price', '>=', (float)$request->min_nutrition_price);
                    break;
                case 'max_nutrition_price':
                    $query->where('nutrition_price', '<=', (float)$request->max_nutrition_price);
                    break;
                case 'min_workout_price':
                    $query->where('workout_price', '>=', (float)$request->min_workout_price);
                    break;
                case 'max_workout_price':
                    $query->where('workout_price', '<=', (float)$request->max_workout_price);
                    break;
                case 'min_full_package_price':
                    $query->where('full_package_price', '>=', (float)$request->min_full_package_price);
                    break;
                case 'max_full_package_price':
                    $query->where('full_package_price', '<=', (float)$request->max_full_package_price);
                    break;
            }
        }
    }

    public function getCoach($id)
    {
        $coach = CoachProfile::with(['user:id,name,email', 'certificates', 'transformations'])->findOrFail($id);

        $certificates = $coach->certificates->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'image_url' => $c->getFirstMediaUrl('certificates'),
            ];
        })->values();

        $transformations = $coach->transformations->map(function ($t) {
            return [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'image_url' => $t->getFirstMediaUrl('transformations'),
            ];
        })->values();

        return [
            'id' => $coach->id,
            'user_id' => $coach->user->id,
            'coach_id' => $coach->id,
            'name' => $coach->user->name,
            'email' => $coach->user->email,
            'specialty' => $coach->specialty,
            'years_of_experience' => $coach->years_of_experience,
            'nutrition_price' => $coach->nutrition_price,
            'workout_price' => $coach->workout_price,
            'full_package_price' => $coach->full_package_price,
            'profile_image_url' => $coach->profile_image_url,
            'certificates' => $certificates,
            'transformations' => $transformations,
        ];
    }
}
