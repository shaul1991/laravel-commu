<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSettingsModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SettingsController handles user settings.
 * Email/password update methods have been removed (OAuth-only authentication).
 */
final class SettingsController extends Controller
{
    public function getNotificationSettings(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $settings = $this->getOrCreateSettings($user);

        return response()->json([
            'data' => $this->formatNotificationSettings($settings),
        ]);
    }

    public function updateNotificationSettings(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $settings = $this->getOrCreateSettings($user);

        $updateData = array_filter($request->validated(), fn ($value) => $value !== null);
        $settings->update($updateData);

        $settings->refresh();

        return response()->json([
            'data' => $this->formatNotificationSettings($settings),
        ]);
    }

    /**
     * Delete user account with confirmation phrase (OAuth-only authentication).
     * Instead of password verification, user must type "삭제합니다" to confirm.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:삭제합니다'],
        ], [
            'confirmation.required' => '확인 문구를 입력해주세요.',
            'confirmation.in' => '확인 문구가 올바르지 않습니다. "삭제합니다"를 정확히 입력해주세요.',
        ]);

        /** @var UserModel $user */
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete the user
        $user->delete();

        return response()->json([
            'message' => '계정이 삭제되었습니다.',
        ]);
    }

    private function getOrCreateSettings(UserModel $user): UserSettingsModel
    {
        $settings = $user->settings;

        if (! $settings) {
            $settings = UserSettingsModel::create([
                'user_id' => $user->id,
            ]);
        }

        return $settings;
    }

    private function formatNotificationSettings(UserSettingsModel $settings): array
    {
        return [
            'email_on_comment' => $settings->email_on_comment,
            'email_on_reply' => $settings->email_on_reply,
            'email_on_follow' => $settings->email_on_follow,
            'email_on_like' => $settings->email_on_like,
            'push_enabled' => $settings->push_enabled,
        ];
    }
}
