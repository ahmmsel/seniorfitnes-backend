<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscoverCoachService;
use Illuminate\Http\Request;

class DiscoverCoachController extends Controller
{
    public function __construct(protected DiscoverCoachService $service) {}

    public function coaches(Request $request)
    {
        $coaches = $this->service->getCoaches($request);

        return response()->json($coaches, 200);
    }

    public function show(Request $request, $coach)
    {
        $data = $this->service->getCoach($coach, $request->user());

        return response()->json($data, 200);
    }
}
