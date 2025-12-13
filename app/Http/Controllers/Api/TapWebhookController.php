<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\TapWebhookRequest;
use App\Services\TapPaymentService;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    public function __construct(
        protected TapPaymentService $tapPaymentService
    ) {}

    public function webhook(TapWebhookRequest $request)
    {
        // Log the webhook
        Log::info('Tap Webhook Received', $request->all());

        // Process webhook via service
        $result = $this->tapPaymentService->processWebhook($request->validated());

        // Handle error responses
        if (isset($result['error']) && $result['error']) {
            $statusCode = $result['message'] === 'Trainee not found' ||
                $result['message'] === 'Coach profile not found' ? 404 : 400;
            return response()->json(['message' => $result['message']], $statusCode);
        }

        return response()->json(['message' => $result['message']], 200);
    }
}
