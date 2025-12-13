<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transformation\TransformationStoreRequest;
use App\Http\Requests\Transformation\TransformationUpdateRequest;
use App\Models\Transformation;
use App\Services\TransformationService;
use Illuminate\Http\JsonResponse;

class TransformationController extends Controller
{
    public function __construct(protected TransformationService $service) {}

    public function index(): JsonResponse
    {
        $transformations = $this->service->getAll();
        return response()->json(['transformations' => $transformations]);
    }

    public function store(TransformationStoreRequest $request): JsonResponse
    {
        $transformation = $this->service->store($request->validated(), $request);
        return response()->json([
            'message' => 'Transformation created successfully.',
            'transformation' => $transformation,
        ], 201);
    }

    public function show(Transformation $transformation): JsonResponse
    {
        $data = $this->service->show($transformation);
        return response()->json(['transformation' => $data]);
    }

    public function update(TransformationUpdateRequest $request, Transformation $transformation): JsonResponse
    {
        $updated = $this->service->update($transformation, $request->validated(), $request);
        return response()->json([
            'message' => 'Transformation updated successfully.',
            'transformation' => $updated,
        ]);
    }

    public function destroy(Transformation $transformation): JsonResponse
    {
        $this->service->destroy($transformation);
        return response()->json(['message' => 'Transformation deleted successfully.']);
    }
}
