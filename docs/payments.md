# Tap Payments Integration

This project supports Tap (GoSell) payments. Follow these steps to install and configure locally.

# Tap Payments Integration

This project integrates with Tap (GoSell) using Tap's REST API. The official Tap PHP SDK (`tappayments/gosell`) is outdated and depends on older Guzzle versions that are incompatible with modern Laravel — do not install it. Instead, use Tap's REST API via HTTP.

## Integration approach (recommended)

-   Use Laravel's HTTP client (`Illuminate\Support\Facades\Http`) to call Tap's REST API at `https://api.tap.company/v2`.
-   The service `app/Services/TapPaymentService.php` performs charge creation and charge retrieval via HTTP requests and converts amounts to minor units (e.g. cents).

## Environment variables

Add these to your `.env` (use test keys in development):

```env
TAP_SECRET_KEY=sk_test_XXXXXXXXXXXXXXXXXXXX
TAP_PUBLIC_KEY=pk_test_XXXXXXXXXXXXXXXXXXXX
TAP_MODE=sandbox
# Note: Tap uses the Secret API Key for webhook verification; no separate webhook secret is required.
```

The code keeps backward compatibility with the older `TAP_SECRET` / `TAP_PUBLIC` names, but prefer the explicit `TAP_SECRET_KEY`/`TAP_PUBLIC_KEY` variables.

## Routes

-   Webhook (server-to-server): `POST /api/payment/tap/webhook` — named route `tap.webhook`.
-   Redirect (user-facing): `GET /api/payment/tap/redirect?tap_id={id}` — named route `tap.redirect`.

When creating a charge, your controller should include `post.url` (webhook) and `redirect.url` (redirect) pointing to these routes.

## Clearing cache after config changes

After updating `.env` or `config/services.php`, run:

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

## Testing

1. Use the trainee purchase endpoint (`POST /api/trainee/{id}/purchase-plan`) to create a charge. The response contains `checkout_url` and `charge_id`.
2. Open the `checkout_url` (or simulate Tap behavior in sandbox).
3. Tap will POST to the webhook; ensure `TAP_SECRET_KEY` (your Secret API Key) matches the key configured in the Tap dashboard. The webhook controller verifies the HMAC-SHA256 signature using the Secret API Key before processing.
4. After payment, Tap redirects the user to `tap.redirect` with `tap_id` in the query string. The redirect handler retrieves the charge and returns its status.

## Notes & recommendations

-   Keep `TAP_SECRET_KEY` server-side only; never expose it to clients.
-   Use `TAP_SECRET_KEY` (Secret API Key) to verify webhooks. The webhook verification attempts raw HMAC-SHA256 of the JSON body and a Tap-style `x_...` concatenation fallback.
-   Consider adding a user-facing success/failure HTML page and redirect the user there from the `tap.redirect` handler instead of returning JSON.
-   Add authorization checks to ensure only the trainee who starts the purchase can complete actions related to that purchase.

If you want, I can also:

-   Add HTML success/failure pages and redirect users there.
-   Add feature tests that simulate the webhook and full end-to-end flow.

---

End of document
TAP_MODE=sandbox
