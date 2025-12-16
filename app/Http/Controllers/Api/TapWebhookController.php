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
        try {
            // Log the webhook
            Log::info('Tap Webhook Received', [
                'tap_id' => $request->input('tap_id'),
                'status' => $request->input('tap_status'),
                'amount' => $request->input('tap_amount'),
            ]);

            // Process webhook via service
            $result = $this->tapPaymentService->processWebhook($request->validated());

            // Log the result
            Log::info('Tap Webhook Result', $result);

            // Handle error responses
            if (isset($result['error']) && $result['error']) {
                $statusCode = $result['message'] === 'Trainee not found' ||
                    $result['message'] === 'Coach profile not found' ? 404 : 400;
                return response()->json(['message' => $result['message']], $statusCode);
            }

            return response()->json($result, 200);
        } catch (\Throwable $e) {
            Log::error('Tap Webhook Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
