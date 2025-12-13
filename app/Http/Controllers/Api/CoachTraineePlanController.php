<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TraineePlanService;

class CoachTraineePlanController extends Controller
{
    public function __construct(protected TraineePlanService $service) {}

    /**
     * List pending trainee plans for the authenticated coach.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $coachProfile = $user->coachProfile;
        if (! $coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $pending = $this->service->pendingForCoach($coachProfile);

        return response()->json(['pending' => $pending]);
    }

    /**
     * List completed trainee plans for the authenticated coach.
     */
    public function completed(Request $request)
    {
        $user = $request->user();

        $coachProfile = $user->coachProfile;
        if (! $coachProfile) {
            return response()->json(['message' => 'Not a coach'], 403);
        }

        $completed = $this->service->completedForCoach($coachProfile);

        return response()->json(['completed' => $completed]);
    }
}
