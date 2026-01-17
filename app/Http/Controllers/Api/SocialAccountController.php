<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\SocialAccountModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SocialAccountController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'github'];

    /**
     * Get list of linked social accounts for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $socialAccounts = SocialAccountModel::where('user_id', $user->id)->get();

        $data = [];
        foreach (self::SUPPORTED_PROVIDERS as $provider) {
            $account = $socialAccounts->firstWhere('provider', $provider);
            $data[$provider] = $account ? [
                'provider' => $account->provider,
                'provider_email' => $account->provider_email,
                'nickname' => $account->nickname,
                'avatar_url' => $account->avatar_url,
                'linked_at' => $account->created_at->toIso8601String(),
            ] : null;
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Unlink a social account from the authenticated user.
     */
    public function destroy(Request $request, string $provider): JsonResponse
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            return response()->json([
                'message' => 'Unsupported OAuth provider',
            ], 422);
        }

        $user = $request->user();

        $socialAccount = SocialAccountModel::where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();

        if (! $socialAccount) {
            return response()->json([
                'message' => 'Social account not found',
            ], 404);
        }

        // Check if this is the last authentication method
        $hasPassword = $user->hasUsablePassword();
        $socialAccountCount = SocialAccountModel::where('user_id', $user->id)->count();

        if (! $hasPassword && $socialAccountCount <= 1) {
            return response()->json([
                'message' => 'Cannot unlink the last authentication method',
            ], 422);
        }

        $socialAccount->delete();

        return response()->json([
            'message' => 'Social account unlinked successfully',
        ]);
    }
}
