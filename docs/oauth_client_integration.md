# OAuth Client Integration Guide

This document summarizes how mobile/web clients should integrate Google and Apple sign-in with the SuniorFit API. The backend now uses Laravel Socialite under the hood, so clients must provide **access tokens** or **authorization codes** whenever possible (ID tokens remain a fallback).

## Google Sign-In

1. Use the official Google Sign-In SDK for your platform (Android, iOS, web, Flutter, React Native).
2. Request the following scopes: `openid email profile`.
3. After a successful sign-in, send one of the following payloads to `POST /api/auth/google-signin`:

### Preferred (access token)

```json
{
    "access_token": "ya29.a0Abc...",
    "device_name": "iphone-14-pro",
    "locale": "en",
    "invite_code": "ABC123"
}
```

### Authorization code

```json
{
    "authorization_code": "4/0AdQt8...",
    "device_name": "pixel-7",
    "locale": "ar"
}
```

### Legacy fallback (only if the SDK cannot return the above)

```json
{
    "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6Ij..."
}
```

> **Important:** Provide either `access_token` **or** `authorization_code`. The backend falls back to `id_token`, but tokens must be valid and unexpired. Always send `device_name` so that the Sanctum token is labeled per device.

## Apple Sign-In

1. Use the `Sign in with Apple` capability with the `continue`/`nonce` flow recommended by Apple.
2. Request email & name scopes so first-time users can be created with profile data.
3. Send one of the following payloads to `POST /api/auth/apple-signin`:

### Preferred (authorization code)

```json
{
    "authorization_code": "c1a2b3-authorization-code",
    "full_name": { "given_name": "Sara", "family_name": "Ali" },
    "email": "relay@privaterelay.appleid.com",
    "device_name": "iphone-14",
    "locale": "en"
}
```

### Identity token fallback

```json
{
    "identity_token": "eyJraWQiOiJ...",
    "full_name": { "given_name": "Sara", "family_name": "Ali" },
    "email": "relay@privaterelay.appleid.com"
}
```

> **Note:** Apple only returns `full_name` and `email` the first time the user approves your app, so store them client-side and send them on subsequent sign-ins when available.

## Error Handling

-   **401 Invalid token:** Refresh the credential client-side (renew Google access token or Apple authorization code) and retry.
-   **422 Validation error:** Ensure you are sending at least one of the required credential fields and that strings respect length limits.
-   **409 Conflict:** The email is already linked to another provider. Prompt the user to sign in with the linked method or surface a helpful error.

## Testing Checklist

-   Use Google/Apple sandbox/test users where possible.
-   Verify expired tokens are rejected and that the client gracefully refreshes them.
-   Confirm `device_name` labels match the user device list in settings (if exposed).

For further details, refer to the API documentation in `docs/api_endpoints.txt` and the backend implementation under `app/Services/SocialAuthService.php`.
