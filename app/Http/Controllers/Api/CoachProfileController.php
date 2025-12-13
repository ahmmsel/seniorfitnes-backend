<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\CoachProfileStoreRequest;
use App\Http\Requests\Coach\CoachProfileUpdateRequest;
use App\Services\CoachProfileService;

class CoachProfileController extends Controller
{
    public function __construct(protected CoachProfileService $service) {}

    public function show()
    {
        return response()->json($this->service->show(), 200);
    }

    public function store(CoachProfileStoreRequest $request)
    {
        return $this->service->store($request->validated(), $request->file('profile_image'));
    }

    public function update(CoachProfileUpdateRequest $request)
    {
        return $this->service->update($request->validated(), $request->file('profile_image'));
    }

    public function destroy()
    {
        return response()->json($this->service->destroy(), 200);
    }

    public function notifications()
    {
        return response()->json($this->service->notifications(true), 200);
    }

    public function analytics()
    {
        return response()->json($this->service->analytics(), 200);
    }
}
