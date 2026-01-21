# Flutter Google OAuth Integration Guide

## Overview
This guide explains how to integrate Google Sign-In with your Flutter app using the SuniorFit backend's OAuth implementation. The backend supports **two approaches**: token-based (recommended for mobile) and web redirect flow.

---

## Approach 1: Token-Based Sign-In (Recommended for Mobile)

### Why Token-Based?
- Native Google Sign-In experience
- Better UX on mobile devices
- No web redirects or browser popups
- Works offline-first with cached credentials

### High-Level Flow
1. User taps "Sign in with Google" button in your Flutter app
2. Flutter opens native Google Sign-In picker (uses device accounts)
3. User selects account and grants permissions
4. Google returns an `idToken` or `accessToken`
5. Flutter sends the token to your backend endpoint: `POST /api/auth/google-signin`
6. Backend validates token with Google, creates/updates user, returns Sanctum token
7. Flutter stores Sanctum token and navigates to home screen

### Required Flutter Package
Install `google_sign_in` package in your Flutter project's `pubspec.yaml`

### Configuration Steps

#### 1. Firebase/Google Cloud Console Setup
- Create a project in Google Cloud Console
- Enable Google Sign-In API
- Create OAuth 2.0 credentials:
  - **Android**: Add SHA-1 fingerprint from your debug/release keystore
  - **iOS**: Add iOS URL scheme (reversed client ID)
  - **Web** (optional): Add authorized redirect URIs if using web platform

#### 2. Platform-Specific Configuration

**Android (`android/app/build.gradle`)**:
- No special configuration needed if Firebase is set up correctly
- Ensure SHA-1 fingerprint matches your signing certificate

**iOS (`ios/Runner/Info.plist`)**:
- Add URL scheme with reversed client ID
- Add Google Sign-In URL scheme

**Web (optional)**:
- Add web client ID to `google_sign_in` initialization

#### 3. Flutter Implementation Pattern

**State Management**:
- Create an `AuthService` or `AuthProvider` class
- Initialize `GoogleSignIn` instance with required scopes
- Handle sign-in, sign-out, and session persistence

**Sign-In Flow**:
1. Initialize Google Sign-In instance
2. Call sign-in method
3. Retrieve authentication tokens (idToken or accessToken)
4. Send token to backend endpoint
5. Store returned Sanctum token in secure storage
6. Update app state to authenticated

**Error Handling**:
- Network errors (no internet)
- User cancellation
- Invalid credentials
- Backend validation failures

### Backend Endpoint
```
POST /api/auth/google-signin
Content-Type: application/json

Body:
{
  "access_token": "google_access_token_here"
  // OR
  "authorization_code": "google_auth_code_here"
}

Response (Success):
{
  "message": "Login successful",
  "token": "sanctum_token_here",
  "user": { ... }
}
```

### Security Considerations
- Store Sanctum token in secure storage (flutter_secure_storage package)
- Never store tokens in shared preferences without encryption
- Validate token expiration and handle refresh
- Clear tokens on sign-out

---

## Approach 2: Web OAuth Redirect Flow (Alternative)

### Why Web Flow?
- Required for web platform
- Can be used for mobile with WebView
- More complex but supports all platforms

### High-Level Flow
1. User taps "Sign in with Google"
2. Flutter opens WebView or system browser pointing to: `GET /auth/google/redirect`
3. User completes Google consent screen
4. Google redirects to: `GET /auth/google/callback`
5. Backend validates with Google, creates user, returns token (currently as JSON)
6. WebView/browser receives response
7. Flutter extracts token and closes WebView
8. Flutter stores token and navigates to home screen

### Implementation Pattern

**WebView Approach**:
- Use `webview_flutter` or `flutter_inappwebview` package
- Open backend's redirect URL in WebView
- Listen for callback URL navigation
- Extract token from final response
- Close WebView and proceed

**System Browser Approach**:
- Use `url_launcher` to open redirect URL
- Implement deep linking to catch callback
- Parse token from deep link parameters
- More complex but better security

### Required Flutter Packages
- `webview_flutter` or `flutter_inappwebview` (for WebView approach)
- `url_launcher` + `uni_links` or `app_links` (for system browser approach)

### Backend Endpoints
```
GET /auth/google/redirect
- Redirects user to Google consent screen

GET /auth/google/callback
- Receives authorization code from Google
- Validates and creates/updates user
- Currently returns JSON with token
- Can be modified to redirect to frontend with token in URL
```

### Redirect Customization
If you prefer the callback to redirect to your Flutter app instead of returning JSON:

1. Set a frontend callback URL in your `.env`:
   ```
   GOOGLE_OAUTH_FRONTEND_CALLBACK=suniorfit://auth/callback
   ```

2. Modify `googleCallback()` method in `AuthController.php` to redirect:
   ```
   return redirect($frontendUrl . '?token=' . $token);
   ```

3. Set up deep linking in Flutter to catch the redirect

---

## Choosing the Right Approach

### Use Token-Based (Approach 1) When:
- Building primarily for mobile (iOS/Android)
- Want native Google Sign-In UX
- Need offline-first authentication
- Users are likely to have Google accounts on device

### Use Web Redirect (Approach 2) When:
- Building for web platform
- Need to support multiple auth providers with same flow
- Want centralized OAuth handling on backend
- Building PWA or hybrid app

### Hybrid Solution
You can support both:
- Use token-based for mobile platforms
- Use web redirect for web platform
- Detect platform at runtime and choose appropriate flow

---

## Testing Strategy

### Test on Real Devices
- Google Sign-In doesn't work reliably on emulators/simulators
- Test with different Google account types (consumer, workspace)
- Test with accounts that have 2FA enabled

### Test Scenarios
1. **First-time sign-in**: User doesn't exist in backend
2. **Returning user**: User exists, should retrieve existing profile
3. **Account conflict**: Google account email matches existing email/password user
4. **Network failures**: Poor connectivity during sign-in
5. **User cancellation**: User closes picker/consent screen
6. **Permissions**: User denies required scopes
7. **Sign-out**: Proper cleanup and token revocation

### Backend Testing
- Test with invalid tokens
- Test with expired tokens
- Test with tokens from different Google project
- Verify user creation and profile updates
- Check token expiration and refresh

---

## Common Issues & Solutions

### Issue: "PlatformException: sign_in_failed"
**Solution**: Verify SHA-1 fingerprint matches in Google Cloud Console

### Issue: "Error 10" on Android
**Solution**: Check package name matches between app and Google Console

### Issue: Sign-in works in debug but fails in release
**Solution**: Add release SHA-1 fingerprint to Google Cloud Console

### Issue: iOS "No identities available for signing"
**Solution**: Verify reversed client ID is correctly added to Info.plist

### Issue: Backend returns 401 or invalid token
**Solution**: 
- Check token hasn't expired (tokens expire quickly)
- Verify Google client ID matches between app and backend
- Ensure backend can reach Google's token verification endpoint

### Issue: User exists but can't sign in
**Solution**: 
- Check backend conflict resolution logic in `SocialAuthService`
- Verify user's email is verified
- Check user isn't soft-deleted or banned

---

## Security Best Practices

### Client-Side (Flutter)
1. **Secure Storage**: Use flutter_secure_storage for Sanctum tokens
2. **Token Validation**: Check token expiration before API calls
3. **Error Handling**: Don't expose sensitive error details to users
4. **HTTPS Only**: Never send tokens over HTTP
5. **Scope Minimization**: Request only necessary Google scopes

### Backend-Side (Already Implemented)
1. **Token Verification**: Backend validates all Google tokens with Google servers
2. **Rate Limiting**: Protect sign-in endpoints from abuse
3. **Sanctum Tokens**: Secure, short-lived API tokens
4. **User Validation**: Email verification and profile checks
5. **Audit Logging**: Track sign-in attempts and failures

---

## API Reference

### Token-Based Sign-In
```
POST /api/auth/google-signin
Authorization: Not required
Content-Type: application/json

Request:
{
  "access_token": "ya29.a0AfH6SMB..." 
  // OR
  "authorization_code": "4/0AY0e-g5..."
}

Success Response (200):
{
  "message": "Login successful",
  "token": "1|laravel_sanctum_token",
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@gmail.com",
    "avatar": "https://...",
    ...
  }
}

Error Response (401):
{
  "message": "Invalid Google credentials"
}
```

### Web OAuth Redirect
```
GET /auth/google/redirect
- Initiates OAuth flow
- Redirects to Google consent screen

GET /auth/google/callback?code=...&state=...
- Google redirects here after consent
- Returns JSON with token (or can redirect to frontend)

Success Response (200):
{
  "message": "Login successful",
  "token": "1|laravel_sanctum_token",
  "user": { ... }
}
```

---

## Production Checklist

### Before Launch
- [ ] Replace test Google credentials with production credentials
- [ ] Add production SHA-1/SHA-256 fingerprints to Google Console
- [ ] Verify redirect URIs match production domain
- [ ] Test on real devices with real Google accounts
- [ ] Implement proper error handling and user feedback
- [ ] Set up analytics to track sign-in success/failure rates
- [ ] Document fallback authentication methods
- [ ] Add terms of service and privacy policy links
- [ ] Test with users in different regions/countries
- [ ] Verify GDPR/data protection compliance

### Monitoring
- Track sign-in success rate
- Monitor token validation failures
- Alert on unusual sign-in patterns
- Track user creation vs existing user login
- Monitor API response times for auth endpoints

---

## Additional Resources

### Google Documentation
- Google Sign-In for Android
- Google Sign-In for iOS
- OAuth 2.0 for Mobile & Desktop Apps
- Google Identity Platform

### Flutter Packages
- google_sign_in: Official Google Sign-In plugin
- flutter_secure_storage: Secure token storage
- webview_flutter: WebView implementation
- url_launcher: Open URLs and deep linking

### SuniorFit Backend Documentation
- See `/docs/SOCIAL_AUTH_INTEGRATION.md` for backend OAuth implementation details
- See `/docs/AUTHENTICATION_LOCALIZATION.md` for error message localization
- See `/docs/API_ENDPOINTS.md` for complete API reference

---

## Support

For backend issues or questions:
- Check backend logs: `storage/logs/laravel.log`
- Verify `.env` configuration
- Test endpoints with Postman/curl
- Review `SocialAuthService.php` for token validation logic

For Flutter integration issues:
- Check Flutter console for detailed error messages
- Enable verbose logging in google_sign_in
- Use Android Studio/Xcode debugger for native layer issues
- Test with Google Sign-In test users first
