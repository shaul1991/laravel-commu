<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class OAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'github'];

    public function redirect(string $provider): JsonResponse
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            return response()->json([
                'message' => 'Unsupported OAuth provider',
            ], 422);
        }

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return response()->json([
            'data' => [
                'url' => $url,
            ],
        ]);
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            return response()->json([
                'message' => 'Unsupported OAuth provider',
            ], 422);
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = UserModel::where('email', $socialUser->getEmail())->first();

            if (! $user) {
                $username = $this->generateUniqueUsername(
                    $socialUser->getNickname() ?? $socialUser->getName()
                );

                $user = UserModel::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'username' => $username,
                    'password' => Hash::make(Str::random(32)),
                    'avatar_url' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }

            // Create token
            $token = $user->createToken('oauth-token')->plainTextToken;

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->uuid,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'bio' => $user->bio,
                        'avatar_url' => $user->avatar_url,
                        'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                        'created_at' => $user->created_at->toIso8601String(),
                    ],
                    'token' => $token,
                ],
                'message' => 'Login successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'OAuth authentication failed',
            ], 400);
        }
    }

    private function generateUniqueUsername(string $baseName): string
    {
        $username = Str::slug($baseName, '');

        if (strlen($username) < 3) {
            $username = 'user'.Str::random(6);
        }

        $originalUsername = $username;
        $counter = 1;

        while (UserModel::where('username', $username)->exists()) {
            $username = $originalUsername.$counter;
            $counter++;
        }

        return $username;
    }
}
