<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $query = Exercise::with('media');

        if ($request->has('q')) {
            $q = $request->get('q');
            $query->where('name', 'like', "%{$q}%");
        }

        $exercises = $query->latest()->paginate($perPage);

        return response()->json($exercises);
    }

    public function show(Exercise $exercise)
    {
        return response()->json(['exercise' => $exercise->load('media')]);
    }
}
