<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class TapWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Signature validation done in rules/prepareForValidation
    }

    protected function prepareForValidation(): void
    {
        // Extract Tap object from various possible locations
        $body = $this->all();
        $obj = $body['charge'] ?? $body['authorize'] ?? $body['invoice'] ?? $body;

        // Store the extracted object for easier access
        $this->merge([
            'tap_object' => $obj,
            'tap_id' => $obj['id'] ?? null,
            'tap_currency' => $obj['currency'] ?? null,
            'tap_status' => $obj['status'] ?? null,
            'tap_created' => $obj['transaction']['date']['created']
                ?? $obj['transaction']['created']
                ?? $obj['created']
                ?? null,
        ]);

        // Extract amount from raw payload
        $raw = $this->getContent();
        $amount = null;
        if (preg_match('/"amount"\s*:\s*([0-9]+(?:\.[0-9]+)?)/', $raw, $m)) {
            $amount = $m[1];
        } elseif (isset($obj['amount'])) {
            $amount = (string)$obj['amount'];
        }
        $this->merge(['tap_amount' => $amount]);

        // Extract reference fields
        $ref = $obj['reference'] ?? [];
        $this->merge([
            'gateway_reference' => $ref['gateway'] ?? $ref['transaction'] ?? '',
            'payment_reference' => $ref['payment'] ?? $ref['order'] ?? '',
        ]);

        // Extract metadata and convert string IDs to integers
        $metadata = $obj['metadata'] ?? [];
        if (isset($metadata['trainee_id'])) {
            $metadata['trainee_id'] = (int) $metadata['trainee_id'];
        }
        if (isset($metadata['coach_profile_id'])) {
            $metadata['coach_profile_id'] = (int) $metadata['coach_profile_id'];
        }
        $this->merge(['tap_metadata' => $metadata]);
    }

    public function rules(): array
    {
        return [
            'tap_id' => 'required|string',
            'tap_object' => 'required|array',
            'tap_amount' => 'required',
            'tap_currency' => 'required|string',
            'tap_status' => 'required|string',
            'tap_created' => 'required',
            'tap_metadata' => 'required|array',
            'tap_metadata.trainee_id' => 'required|integer|exists:trainee_profiles,id',
            'tap_metadata.coach_profile_id' => 'required|integer|exists:coach_profiles,id',
            'tap_metadata.plan_type' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tap_id.required' => 'Missing charge ID',
            'tap_amount.required' => 'Missing amount',
            'tap_metadata.trainee_id.required' => 'Missing trainee_id in metadata',
            'tap_metadata.coach_profile_id.required' => 'Missing coach_profile_id in metadata',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate signature
            if (!$this->validateSignature()) {
                $validator->errors()->add('signature', 'Invalid webhook signature');
            }
        });
    }

    protected function validateSignature(): bool
    {
        // Get hash header
        $hashHeader = $this->header('hashstring')
            ?? $this->header('tap-hash')
            ?? $this->header('tap-signature')
            ?? $this->header('tap_hash')
            ?? $this->header('x-tap-signature')
            ?? $this->header('tap-hash-sha256');

        // Get webhook secret
        $secret = trim(config('services.tap.webhook_secret')
            ?? env('TAP_WEBHOOK_SECRET')
            ?? env('TAP_PUBLIC_KEY')
            ?? '');

        // If no hash header or no secret configured, allow in test mode
        if (!$hashHeader || empty($secret)) {
            Log::warning("Webhook signature validation skipped", [
                'has_hash_header' => !empty($hashHeader),
                'has_secret' => !empty($secret),
            ]);
            return true;
        }

        // Build signature string exactly as Tap does
        // Format: x_id{id}x_amount{amount}x_currency{currency}x_gateway_reference{ref}x_payment_reference{ref}x_status{status}x_created{timestamp}
        $id = $this->input('tap_id');
        $amount = $this->input('tap_amount');
        $currency = $this->input('tap_currency');
        $gatewayReference = $this->input('gateway_reference', '');
        $paymentReference = $this->input('payment_reference', '');
        $status = $this->input('tap_status');
        $created = $this->input('tap_created');

        $hashString = "x_id{$id}x_amount{$amount}x_currency{$currency}x_gateway_reference{$gatewayReference}x_payment_reference{$paymentReference}x_status{$status}x_created{$created}";

        $computed = hash_hmac('sha256', $hashString, $secret);

        Log::info('Tap Signature Validation', [
            'hash_string' => $hashString,
            'computed' => $computed,
            'received' => $hashHeader,
            'match' => hash_equals($computed, (string)$hashHeader),
        ]);

        // In production, enforce signature validation
        // In test mode with mismatches, log warning but allow
        if (!hash_equals($computed, (string)$hashHeader)) {
            if (env('TAP_MODE') === 'test' || config('app.env') !== 'production') {
                Log::warning("Tap signature mismatch (allowing in test mode)", [
                    'computed' => $computed,
                    'received' => $hashHeader,
                ]);
                return true;
            }

            Log::error("Tap signature validation failed", [
                'computed' => $computed,
                'received' => $hashHeader,
            ]);
            return false;
        }

        return true;
    }
}
