<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SocialAuthService
{
    /**
     * Login/Register user using Google or Apple provider
     */
    public function loginFromSocial(string $provider, array $input): User
    {
        $socialUser = $this->resolveUser($provider, $input);

        if (!$socialUser->getEmail()) {
            throw new UnauthorizedHttpException('', "Email not returned by $provider");
        }

        $email = $socialUser->getEmail();
        $providerId = $socialUser->getId();

        // Determine provider ID column
        $providerIdColumn = $provider === 'google' ? 'google_id' : 'apple_id';

        // Try to find user by provider ID first, then by email
        $user = User::where($providerIdColumn, $providerId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            // Update provider ID if not set
            if (empty($user->$providerIdColumn)) {
                $user->update([$providerIdColumn => $providerId]);
            }

            // Update email verification if not verified
            if (empty($user->email_verified_at)) {
                $user->update(['email_verified_at' => now()]);
            }

            return $user;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? explode('@', $email)[0],
            'email' => $email,
            $providerIdColumn => $providerId,
            'password' => bcrypt(Str::random(16)),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Resolve Socialite user from token or auth code
     */
    private function resolveUser(string $provider, array $input): SocialiteUser
    {
        $driver = $this->provider($provider);

        try {
            if (!empty($input['access_token'])) {
                return $driver->userFromToken($input['access_token']);
            }

            if (!empty($input['authorization_code'])) {
                $token = $driver->getAccessTokenResponse($input['authorization_code']);
                return $driver->userFromToken($token['access_token']);
            }
        } catch (\Throwable $e) {
        }

        throw new UnauthorizedHttpException('', "Invalid token for $provider");
    }

    /**
     * Provide stateless social driver
     */
    private function provider(string $provider): AbstractProvider
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless();
    }
}
