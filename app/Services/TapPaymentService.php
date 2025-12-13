<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $tapId = $webhookData['tap_id'];
        $status = $webhookData['tap_status'];
        $body = $webhookData['tap_object'];
        $metadata = $webhookData['tap_metadata'];

        // Update payment record
        $payment = \App\Models\Payment::where('charge_id', $tapId)->first();
        if ($payment) {
            $payment->status = $status;
            $payment->raw = $body;
            $payment->save();
        }

        // Ignore non-successful payments
        if (!in_array(strtoupper($status), ['CAPTURED', 'SUCCESS'])) {
            return ['message' => 'Ignored (not successful)', 'processed' => false];
        }

        // Extract metadata
        $traineeId = $metadata['trainee_id'];
        $coachProfileId = $metadata['coach_profile_id'];
        $planType = $metadata['plan_type'] ?? null;
        $items = $metadata['items'] ?? null;

        // Get models
        $trainee = \App\Models\TraineeProfile::find($traineeId);
        $coach = \App\Models\CoachProfile::find($coachProfileId);

        if (!$trainee) {
            return ['message' => 'Trainee not found', 'error' => true];
        }
        if (!$coach) {
            return ['message' => 'Coach profile not found', 'error' => true];
        }

        // Prevent duplicates
        $exists = \App\Models\TraineePlan::where('trainee_id', $traineeId)
            ->where('coach_profile_id', $coachProfileId)
            ->where('plan_type', $planType)
            ->where('tap_charge_id', $tapId)
            ->exists();

        if (!$exists) {
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

            // Notify coach
            if ($coach->user) {
                $coach->user->notify(new \App\Notifications\TraineePurchaseNotification([
                    'trainee_id' => $traineeId,
                    'trainee_name' => $trainee->user->name ?? null,
                    'trainee_plan_id' => $tp->id,
                    'plan_type' => $planType,
                    'items' => $items,
                    'tap_charge_id' => $tapId,
                ]));
            }

            return ['message' => 'Webhook processed', 'processed' => true, 'trainee_plan_id' => $tp->id];
        }

        return ['message' => 'Already processed', 'processed' => false];
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
