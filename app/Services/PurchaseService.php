<?php

namespace App\Services;

use App\Models\CoachProfile;
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    protected TapPaymentService $tap;

    public function __construct(TapPaymentService $tap)
    {
        $this->tap = $tap;
    }

    /**
     * Create a Tap charge for the authenticated trainee user.
     */
    public function createChargeForTrainee($user, array $data): array
    {
        // Validate required fields
        if (empty($data['coach_profile_id']) || empty($data['plan_type'])) {
            throw new \InvalidArgumentException('coach_profile_id and plan_type are required');
        }

        $coach = CoachProfile::findOrFail($data['coach_profile_id']);
        $type = $data['plan_type'];

        // Determine amount based on plan type
        $amount = match ($type) {
            'nutrition' => $coach->nutrition_price,
            'workout'   => $coach->workout_price,
            default     => $coach->full_package_price ?? 0,
        };

        if (!is_numeric($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Calculated amount is invalid or zero');
        }

        $description = "Purchase {$type} plan from coach #{$coach->id}";
        $currency = 'AED';

        $metadata = [
            'trainee_id' => $user->traineeProfile->id ?? null,
            'coach_profile_id' => $data['coach_profile_id'],
            'plan_type' => $data['plan_type'],
        ];

        $customer = [
            'first_name' => 'Trainee',
            'email'      => 'email@example.com',
        ];

        $redirect = route('tap.redirect');

        $callback = route('tap.webhook');

        Log::debug("Log Webhook Route", ['route' => $callback]);

        return $this->tap->createCharge(
            $amount,
            $currency,
            $description,
            $callback,
            [
                'metadata' => $metadata,
                'customer' => $customer,
                'redirect_url' => $redirect,
                'source' => ['id' => 'src_all'],
            ]
        );
    }
}
