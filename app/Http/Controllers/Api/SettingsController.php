<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateEmailRequest;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSettingsModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class SettingsController extends Controller
{
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        if (! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => '비밀번호가 일치하지 않습니다.',
            ], 422);
        }

        $user->update([
            'email' => $request->validated('email'),
            'email_verified_at' => null, // Reset email verification
        ]);

        $user->refresh();

        return response()->json([
            'data' => [
                'email' => $user->email,
            ],
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            return response()->json([
                'message' => '현재 비밀번호가 일치하지 않습니다.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'message' => '비밀번호가 변경되었습니다.',
        ]);
    }

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

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        /** @var UserModel $user */
        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => '비밀번호가 일치하지 않습니다.',
            ], 422);
        }

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
