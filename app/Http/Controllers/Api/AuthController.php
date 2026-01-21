<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AppleSignInRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\GoogleSignInRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\AuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected SocialAuthService $socialAuthService
    ) {}

    public function check(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'has_coach_profile' => $user->coachProfile()->exists(),
            'has_trainee_profile' => $user->traineeProfile()->exists(),
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return response()->json($result, 200);
    }

    public function googleSignIn(GoogleSignInRequest $request)
    {
        $user = $this->socialAuthService->loginFromSocial('google', $request->validated());

        // Generate token
        $deviceName = $request->input('device_name', 'google-signin');
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->loadMissing('coachProfile', 'traineeProfile');

        $result = [
            'status' => 'success',
            'message' => 'Signed in successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'has_coach_profile' => $user->coachProfile !== null,
                    'has_trainee_profile' => $user->traineeProfile !== null,
                ],
                'auth' => [
                    'token_type' => 'sanctum',
                    'access_token' => $token,
                ],
            ],
        ];

        return response()->json($result, 200);
    }

    public function appleSignIn(AppleSignInRequest $request)
    {
        $user = $this->socialAuthService->loginFromSocial('apple', $request->validated());

        // Generate token
        $deviceName = $request->input('device_name', 'apple-signin');
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->loadMissing('coachProfile', 'traineeProfile');

        $result = [
            'status' => 'success',
            'message' => __('auth.signed_in_successfully'),
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'has_coach_profile' => $user->coachProfile !== null,
                    'has_trainee_profile' => $user->traineeProfile !== null,
                ],
                'auth' => [
                    'token_type' => 'sanctum',
                    'access_token' => $token,
                ],
            ],
        ];

        return response()->json($result, 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $email = $request->validated()['email'];

        // Generate random token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Store new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send notification
        $user = User::where('email', $email)->first();
        $user->notify(new ResetPasswordNotification($token));

        return response()->json([
            'status' => 'success',
            'message' => __('auth.password_reset_link_sent'),
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        // Find token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.invalid_reset_token'),
            ], 400);
        }

        // Check if token matches
        if (!Hash::check($validated['token'], $tokenRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.invalid_reset_token'),
            ], 400);
        }

        // Check if token is expired (60 minutes default)
        $expiryMinutes = config('auth.passwords.users.expire', 60);
        if (now()->diffInMinutes($tokenRecord->created_at) > $expiryMinutes) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return response()->json([
                'status' => 'error',
                'message' => __('auth.reset_token_expired'),
            ], 400);
        }

        // Update password
        $user = User::where('email', $validated['email'])->first();
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Delete token after successful reset
        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('auth.password_reset_successfully'),
        ], 200);
    }

    // =========================================================================
    // OAUTH WEB FLOW (Browser-based Google OAuth)
    // =========================================================================

    /**
     * Redirect user to Google OAuth consent screen
     * GET /auth/google/redirect
     */
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google OAuth
     * GET /auth/google/callback
     */
    public function googleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser->getEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('auth.social_login_failed', ['provider' => 'Google']),
                ], 400);
            }

            // Find or create user
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Update Google ID if not set
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }

                // Verify email if not verified
                if (empty($user->email_verified_at)) {
                    $user->update(['email_verified_at' => now()]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName() ?? explode('@', $googleUser->getEmail())[0],
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]);
            }

            // Generate token
            $user->tokens()->where('name', 'google-oauth')->delete();
            $token = $user->createToken('google-oauth')->plainTextToken;

            $user->loadMissing('coachProfile', 'traineeProfile');

            // Redirect to Flutter app with token
            $callbackUrl = config('app.google_oauth_frontend_callback', 'suniorfit://auth/callback');
            $redirectUrl = $callbackUrl . '?token=' . urlencode($token) . '&user_id=' . $user->id;

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            // Redirect to Flutter app with error
            $callbackUrl = config('app.google_oauth_frontend_callback', 'suniorfit://auth/callback');
            $errorMessage = __('auth.social_login_failed', ['provider' => 'Google']);
            $redirectUrl = $callbackUrl . '?error=oauth_failed&message=' . urlencode($errorMessage);

            return redirect($redirectUrl);
        }
    }
}
