<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_비밀번호_재설정_링크를_요청할_수_있다(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password reset link sent to your email');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_존재하지_않는_이메일로_요청시_에러가_발생하지_않는다(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // For security, we don't reveal whether the email exists
        $response->assertOk()
            ->assertJsonPath('message', 'Password reset link sent to your email');

        Notification::assertNothingSent();
    }

    public function test_유효하지_않은_이메일_형식으로_요청시_검증_에러가_발생한다(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_유효한_토큰으로_비밀번호를_재설정할_수_있다(): void
    {
        $user = UserModel::factory()->create();

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password has been reset successfully');

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newPassword123!', $user->password));
    }

    public function test_유효하지_않은_토큰으로_비밀번호_재설정시_에러가_발생한다(): void
    {
        $user = UserModel::factory()->create();

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Invalid or expired token');
    }

    public function test_비밀번호_확인이_일치하지_않으면_에러가_발생한다(): void
    {
        $user = UserModel::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newPassword123!',
            'password_confirmation' => 'differentPassword!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_비밀번호가_너무_짧으면_에러가_발생한다(): void
    {
        $user = UserModel::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
