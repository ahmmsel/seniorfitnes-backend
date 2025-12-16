<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapPaymentService
{
    protected string $base = 'https://api.tap.company/v2';

    public function createCharge($amount, string $currency, string $description, string $callbackUrl, array $data = []): array
    {
        $secret = env('TAP_SECRET_KEY');

        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return ['checkout_url' => null, 'charge_id' => null, 'raw' => ['error' => 'Invalid amount']];
        }
        // Build payload
        $payload = [
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'description' => $description,
            'metadata' => $data['metadata'],
            'customer' => $data['customer'],
            'source' =>  $data['source'],
            'post' => [
                'url' => $callbackUrl,
            ],
            'redirect' => [
                'url' => $data['redirect_url'],
            ],
        ];

        // Remove null values recursively
        $payload = $this->removeNulls($payload);

        try {
            $response = Http::withToken($secret)
                ->acceptJson()
                ->post("{$this->base}/charges", $payload);

            $json = $response->json() ?? [];

            if ($response->failed()) {
                return [
                    'checkout_url' => null,
                    'charge_id' => null,
                    'status' => $response->status(),
                    'raw' => $json
                ];
            }

            return [
                'checkout_url' => $json['transaction']['url'] ?? $json['links']['checkout'] ?? null,
                'charge_id' => $json['id'] ?? null,
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'checkout_url' => null,
                'charge_id' => null,
                'raw' => ['exception' => $e->getMessage()]
            ];
        }
    }

    public function retrieveCharge(string $chargeId): ?array
    {
        $secret = env('TAP_SECRET_KEY');

        try {
            $res = Http::withToken($secret)
                ->acceptJson()
                ->get("{$this->base}/charges/$chargeId");

            return $res->json();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Process webhook payment and create TraineePlan
     */
    public function processWebhook(array $webhookData): array
    {
        Log::info('Processing Tap Webhook', ['data' => $webhookData]);

        $tapId = $webhookData['tap_id'] ?? null;
        $status = $webhookData['tap_status'] ?? null;
        $body = $webhookData['tap_object'] ?? [];
        $metadata = $webhookData['tap_metadata'] ?? [];

        if (!$tapId || !$status) {
            Log::error('Missing tap_id or tap_status', $webhookData);
            return ['message' => 'Invalid webhook data', 'error' => true];
        }

        // Update or create payment record
        $payment = \App\Models\Payment::updateOrCreate(
            ['charge_id' => $tapId],
            [
                'status' => $status,
                'raw' => $body,
                'amount' => $webhookData['tap_amount'] ?? null,
                'currency' => $webhookData['tap_currency'] ?? null,
            ]
        );

        Log::info('Payment record updated', ['payment_id' => $payment->id]);

        // Ignore non-successful payments
        if (!in_array(strtoupper($status), ['CAPTURED', 'SUCCESS'])) {
            Log::info('Payment not successful, ignoring', ['status' => $status]);
            return ['message' => 'Ignored (not successful)', 'processed' => false];
        }

        // Extract metadata
        $traineeId = $metadata['trainee_id'] ?? null;
        $coachProfileId = $metadata['coach_profile_id'] ?? null;
        $planType = $metadata['plan_type'] ?? null;
        $items = $metadata['items'] ?? null;

        if (!$traineeId || !$coachProfileId || !$planType) {
            Log::error('Missing required metadata', ['metadata' => $metadata]);
            return ['message' => 'Missing required metadata', 'error' => true];
        }

        // Get models with relationships
        $trainee = \App\Models\TraineeProfile::with('user')->find($traineeId);
        $coach = \App\Models\CoachProfile::with('user')->find($coachProfileId);

        if (!$trainee) {
            Log::error('Trainee not found', ['trainee_id' => $traineeId]);
            return ['message' => 'Trainee not found', 'error' => true];
        }
        if (!$coach) {
            Log::error('Coach not found', ['coach_profile_id' => $coachProfileId]);
            return ['message' => 'Coach profile not found', 'error' => true];
        }

        Log::info('Found trainee and coach', [
            'trainee_id' => $trainee->id,
            'trainee_user_id' => $trainee->user_id,
            'coach_id' => $coach->id,
            'coach_user_id' => $coach->user_id,
        ]);

        // Prevent duplicates
        $exists = \App\Models\TraineePlan::where('trainee_id', $traineeId)
            ->where('coach_profile_id', $coachProfileId)
            ->where('tap_charge_id', $tapId)
            ->exists();

        if ($exists) {
            Log::info('TraineePlan already exists', ['tap_charge_id' => $tapId]);
            return ['message' => 'Already processed', 'processed' => false];
        }

        // Create TraineePlan
        $tp = \App\Models\TraineePlan::create([
            'trainee_id' => $traineeId,
            'plan_id' => null,
            'coach_profile_id' => $coachProfileId,
            'plan_type' => $planType,
            'items' => $items,
            'tap_charge_id' => $tapId,
            'purchased_at' => now(),
            'status' => 'pending',
        ]);

        Log::info('TraineePlan created', ['trainee_plan_id' => $tp->id]);

        // Notify coach
        try {
            if ($coach->user) {
                $coach->user->notify(new \App\Notifications\TraineePurchaseNotification([
                    'trainee_id' => $traineeId,
                    'trainee_name' => $trainee->user->name ?? 'Trainee',
                    'trainee_plan_id' => $tp->id,
                    'plan_type' => $planType,
                    'items' => $items,
                    'tap_charge_id' => $tapId,
                ]));
                Log::info('Coach notified', ['coach_user_id' => $coach->user->id]);
            } else {
                Log::warning('Coach has no user account', ['coach_id' => $coach->id]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'coach_id' => $coach->id,
            ]);
            // Don't fail the webhook if notification fails
        }

        return [
            'message' => 'Webhook processed successfully',
            'processed' => true,
            'trainee_plan_id' => $tp->id
        ];
    }

    /**
     * Recursively remove null or empty arrays
     */
    protected function removeNulls(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->removeNulls($value);
                if (empty($arr[$key])) {
                    unset($arr[$key]);
                }
            } elseif (is_null($value)) {
                unset($arr[$key]);
            }
        }
        return $arr;
    }
}
