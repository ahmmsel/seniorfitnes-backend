<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TapRedirectController extends Controller
{
    /**
     * Handle user-facing redirect after Tap checkout.
     * Expects `tap_id` query parameter. Returns charge status and raw payload.
     */
    public function redirect(Request $request)
    {
        $tapId = $request->get('tap_id') ?: $request->get('id');
        if (!$tapId) {
            return response()->json(['message' => 'Missing tap_id'], 400);
        }

        $cfg = config('services.tap', []);
        $secret = $cfg['secret'] ?? env('TAP_SECRET_KEY') ?? env('TAP_SECRET');

        // Call Tap API directly to retrieve the charge
        $base = 'https://api.tap.company/v2';
        $res = Http::withToken($secret)->acceptJson()->get($base . '/charges/' . $tapId);
        $data = $res->json() ?? [];
        $status = $data['status'] ?? ($data['data']['object']['status'] ?? null);

        // If client expects JSON (API call), return JSON
        if ($request->wantsJson() || $request->get('format') === 'json') {
            return response()->json(['status' => $status, 'raw' => $data], $res->ok() ? 200 : 502);
        }

        // Build mobile app deep link from config or environment.
        // Set MOBILE_APP_REDIRECT_URI in your .env, e.g. MOBILE_APP_REDIRECT_URI="suniorfit://payment"
        $appRedirect = config('services.tap.mobile_redirect') ?? env('MOBILE_APP_REDIRECT_URI', 'suniorfit://payment');

        $chargeId = $data['id'] ?? ($data['data']['object']['id'] ?? $tapId);

        // Add query parameters to deep link
        $params = http_build_query([
            'status' => $status,
            'charge_id' => $chargeId,
        ]);
        $sep = str_contains($appRedirect, '?') ? '&' : '?';
        $appRedirectWithParams = $appRedirect . $sep . $params;

        // Extract payment details for the view
        $amount = $data['amount'] ?? ($data['data']['object']['amount'] ?? null);
        $currency = $data['currency'] ?? ($data['data']['object']['currency'] ?? 'KWD');
        $reference = $data['reference']['transaction'] ?? ($data['data']['object']['reference']['transaction'] ?? null);

        // Return Arabic Blade template with payment status
        return view('payment-redirect', [
            'status' => $status,
            'chargeId' => $chargeId,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'appRedirect' => $appRedirectWithParams,
        ]);
    }
}
