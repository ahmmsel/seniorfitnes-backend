<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\TapPaymentService;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct(protected TapPaymentService $tap) {}

    /**
     * Create a Tap charge and return hosted payment url
     */
    public function pay(Request $request)
    {
        $v = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string',
            'description' => 'sometimes|string|nullable',
            'customer.first_name' => 'sometimes|string|nullable',
            'customer.last_name' => 'sometimes|string|nullable',
            'customer.email' => 'sometimes|email|nullable',
            'customer.phone' => 'sometimes|array|nullable',
            'reference.transaction' => 'sometimes|string|nullable',
            'reference.order' => 'sometimes|string|nullable',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        $description = $data['description'] ?? 'Payment';

        $callback = route('tap.webhook');
        $redirect = route('tap.redirect');

        $meta = $data['metadata'] ?? [];
        if (!empty($data['reference'])) {
            $meta['reference'] = $data['reference'];
        }
        if (!empty($data['customer'])) {
            $meta['customer'] = $data['customer'];
        }

        $result = $this->tap->createCharge($data['amount'], $data['currency'], $description, $callback, array_merge($meta, ['redirect_url' => $redirect]));

        // Persist a payment record to correlate later
        $payment = Payment::create([
            'charge_id' => $result['charge_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => $result['raw']['status'] ?? 'INITIATED',
            'reference' => $data['reference'] ?? null,
            'metadata' => $meta,
            'raw' => $result['raw'] ?? null,
        ]);

        if (empty($result['checkout_url'])) {
            return response()->json(['message' => 'Could not create payment', 'details' => $result['raw'] ?? null], 500);
        }

        return response()->json(['payment_url' => $result['checkout_url'], 'charge_id' => $result['charge_id'], 'payment_id' => $payment->id]);
    }

    /**
     * Handle Tap redirect (user-facing)
     */
    public function redirect(Request $request)
    {
        // Tap may or may not include identifiers. If tap_id present, use it.
        $tapId = $request->get('tap_id') ?: $request->get('id');
        $orderId = $request->get('order_id');

        if ($tapId) {
            $charge = $this->tap->retrieveCharge($tapId);
            $status = $charge['status'] ?? $charge['data']['object']['status'] ?? null;
            // Optionally redirect to frontend pages
            return response()->json(['status' => $status, 'charge' => $charge]);
        }

        if ($orderId) {
            // Lookup payment linked to order if stored in metadata/reference
            $payment = Payment::whereJsonContains('reference->order', (string) $orderId)->first();
            if ($payment) {
                return response()->json(['status' => $payment->status, 'payment' => $payment]);
            }
        }

        return response()->json(['message' => 'Missing tap_id or order_id'], 400);
    }

    /**
     * Return stored status for a payment identified by payment_id or order_id
     */
    public function status(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $orderId = $request->get('order_id');

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if (!$payment) return response()->json(['message' => 'Not found'], 404);
            return response()->json(['status' => $payment->status, 'payment' => $payment]);
        }

        if ($orderId) {
            $payment = Payment::whereJsonContains('reference->order', (string) $orderId)->first();
            if (!$payment) return response()->json(['message' => 'Not found'], 404);
            return response()->json(['status' => $payment->status, 'payment' => $payment]);
        }

        return response()->json(['message' => 'payment_id or order_id required'], 422);
    }
}
