<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\CertificateStoreRequest;
use App\Http\Requests\Certificate\CertificateUpdateRequest;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->certificateService->getAll());
    }

    public function store(CertificateStoreRequest $request): JsonResponse
    {
        return response()->json($this->certificateService->store($request->validated(), $request), 201);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        return response()->json($this->certificateService->show($certificate));
    }

    public function update(CertificateUpdateRequest $request, Certificate $certificate): JsonResponse
    {
        return response()->json($this->certificateService->update($certificate, $request->validated(), $request));
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        return response()->json($this->certificateService->delete($certificate));
    }
}
