<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\CreatePlanFromPurchaseRequest;
use App\Http\Requests\Workout\UpdatePlanRequest;
use App\Services\PlanService;
use App\Models\Plan;

class PlanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected PlanService $service) {}

    public function index()
    {
        return response()->json(['plans' => $this->service->index()]);
    }

    public function show(Plan $plan)
    {
        return response()->json(['plan' => $this->service->show($plan)]);
    }

    public function store(CreatePlanFromPurchaseRequest $request)
    {
        // Only allow creation when there is a pending TraineePlan purchase (request authorizes it)
        $coachProfile = $request->user() ? $request->user()->coachProfile : null;
        $data = $request->validated();

        $plan = $this->service->store($data, $coachProfile);

        return response()->json(['message' => 'Plan created for purchase', 'plan' => $plan], 201);
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $plan = $this->service->update($plan, $request->validated());

        return response()->json(['message' => 'Plan updated', 'plan' => $plan]);
    }

    public function destroy(Plan $plan)
    {
        $this->authorize('delete', $plan);
        $this->service->delete($plan);
        return response()->json(['message' => 'Plan deleted']);
    }
}
