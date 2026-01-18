<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * AuthController handles OAuth-only authentication.
 * Email/password authentication has been removed.
 * All login/registration is done via OAuthController.
 */
final class AuthController extends Controller
{
    /**
     * Issue Access Token and Refresh Token for authenticated user.
     * Access Token is returned in response body.
     * Refresh Token is set as HTTP-only cookie.
     */
    public function token(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Create Personal Access Token (JWT)
        $tokenResult = $user->createToken('access_token');

        // Get token expiration time in seconds (from config, default 15 minutes)
        $expiresInMinutes = (int) config('passport.tokens_expire_in', 15);
        $expiresInSeconds = $expiresInMinutes * 60;

        // Create Refresh Token and set as HTTP-only cookie
        $refreshTokenResult = $user->createToken('refresh_token');
        $refreshExpiresInMinutes = (int) config('passport.refresh_tokens_expire_in', 10080);

        // Set Refresh Token as HTTP-only cookie
        $refreshTokenCookie = Cookie::make(
            name: 'refresh_token',
            value: $refreshTokenResult->accessToken,
            minutes: $refreshExpiresInMinutes,
            path: '/api/auth',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        );

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresInSeconds,
        ])->withCookie($refreshTokenCookie);
    }

    /**
     * Refresh Access Token using Refresh Token from HTTP-only cookie.
     * Implements Refresh Token Rotation for security.
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token');

        if (! $refreshToken) {
            return response()->json([
                'message' => 'Refresh token is missing',
            ], 401);
        }

        // Validate refresh token and get user
        try {
            $tokenId = $this->getTokenIdFromJwt($refreshToken);

            if (! $tokenId) {
                return response()->json([
                    'message' => 'Invalid refresh token',
                ], 401);
            }

            // Find token using Token model directly
            $token = \Laravel\Passport\Token::find($tokenId);

            if (! $token || $token->revoked) {
                return response()->json([
                    'message' => 'Invalid refresh token',
                ], 401);
            }

            // Revoke old refresh token (Refresh Token Rotation)
            $token->revoke();

            $user = $token->user;

            if (! $user) {
                return response()->json([
                    'message' => 'User not found',
                ], 401);
            }

            // Issue new tokens
            $tokenResult = $user->createToken('access_token');
            $expiresInMinutes = (int) config('passport.tokens_expire_in', 15);
            $expiresInSeconds = $expiresInMinutes * 60;

            $refreshTokenResult = $user->createToken('refresh_token');
            $refreshExpiresInMinutes = (int) config('passport.refresh_tokens_expire_in', 10080);

            $refreshTokenCookie = Cookie::make(
                name: 'refresh_token',
                value: $refreshTokenResult->accessToken,
                minutes: $refreshExpiresInMinutes,
                path: '/api/auth',
                domain: null,
                secure: app()->environment('production'),
                httpOnly: true,
                raw: false,
                sameSite: 'lax'
            );

            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiresInSeconds,
            ])->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid refresh token',
            ], 401);
        }
    }

    /**
     * Extract token ID from JWT.
     */
    private function getTokenIdFromJwt(string $jwt): ?string
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);

            return $payload['jti'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke API token if it exists (Passport uses revoke(), not delete())
        if ($user) {
            $token = $user->currentAccessToken();
            if ($token && method_exists($token, 'revoke')) {
                $token->revoke();
            }
        }

        // Always try to logout from web guard (handles both session and stateless auth)
        Auth::guard('web')->logout();

        // Clear web session if available
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'data' => [
                'id' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get list of active sessions (tokens) for the authenticated user.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get current token ID if available
        $currentTokenId = null;
        $currentToken = $user->currentAccessToken();
        if ($currentToken && isset($currentToken->id)) {
            $currentTokenId = $currentToken->id;
        }

        // Get all active tokens for the user
        $tokens = $user->tokens()
            ->where('revoked', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at?->toIso8601String(),
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'is_current' => $token->id === $currentTokenId,
                ];
            });

        return response()->json([
            'data' => $tokens,
        ]);
    }

    /**
     * Revoke a specific session (token).
     */
    public function revokeSession(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Find the token belonging to the user
        $token = $user->tokens()
            ->where('id', $id)
            ->where('revoked', false)
            ->first();

        if (! $token) {
            return response()->json([
                'message' => 'Session not found',
            ], 404);
        }

        // Revoke the token
        $token->revoke();

        return response()->json([
            'message' => 'Session revoked successfully',
        ]);
    }

    /**
     * Revoke all sessions except the current one.
     */
    public function revokeAllSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get current token ID
        $currentTokenId = null;
        $currentToken = $user->currentAccessToken();
        if ($currentToken && isset($currentToken->id)) {
            $currentTokenId = $currentToken->id;
        }

        // Revoke all tokens except the current one
        $user->tokens()
            ->where('revoked', false)
            ->when($currentTokenId, function ($query) use ($currentTokenId) {
                return $query->where('id', '!=', $currentTokenId);
            })
            ->update(['revoked' => true]);

        return response()->json([
            'message' => 'All other sessions revoked successfully',
        ]);
    }
}
