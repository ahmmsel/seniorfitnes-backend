<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasePlanRequest;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Create a Tap charge for the authenticated trainee and return checkout URL
     * POST /api/purchases (authenticated)
     */
    public function purchase(PurchasePlanRequest $request)
    {
        $user = Auth::user();
        if (!$user || !$user->traineeProfile) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();

        try {
            $result = $this->purchaseService->createChargeForTrainee($user, $data);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (empty($result['checkout_url'])) {
            return response()->json(['message' => 'Could not create payment.', 'details' => $result['raw'] ?? null], 500);
        }

        return response()->json(['checkout_url' => $result['checkout_url'], 'charge_id' => $result['charge_id']]);
    }
}
