<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trainee\TraineeProfileStoreRequest;
use App\Http\Requests\Trainee\TraineeProfileUpdateRequest;

use App\Services\TraineeProfileService;

class TraineeProfileController extends Controller
{
    public function __construct(protected TraineeProfileService $service) {}

    public function show()
    {
        return response()->json($this->service->show(), 200);
    }

    public function store(TraineeProfileStoreRequest $request)
    {
        return response()->json(
            $this->service->store($request->validated(), $request->file('profile_image')),
            201
        );
    }

    public function update(TraineeProfileUpdateRequest $request)
    {
        return response()->json(
            $this->service->update($request->validated(), $request->file('profile_image')),
            200
        );
    }

    public function destroy()
    {
        return response()->json($this->service->destroy(), 200);
    }

    public function notifications()
    {
        return response()->json($this->service->notifications(true), 200);
    }

    /**
     * Return plans assigned to the authenticated trainee
     */
    public function plans()
    {
        return response()->json($this->service->plans(), 200);
    }

    /**
     * Return detailed plan assigned to the authenticated trainee.
     * GET /api/trainee/plans/{plan}
     */
    public function planDetail($planId)
    {
        return response()->json($this->service->planDetails($planId), 200);
    }

    /**
     * Quick access: latest assigned plan for the trainee
     * GET /api/trainee/plans/latest
     */
    public function latestPlan()
    {
        return response()->json($this->service->latestPlan(), 200);
    }

    /**
     * Quick access: latest started workout with progress
     * GET /api/trainee/workouts/latest/progress
     */
    public function latestWorkoutProgress()
    {
        return response()->json($this->service->latestWorkoutProgress(), 200);
    }
}
