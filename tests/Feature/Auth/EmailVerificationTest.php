<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_인증된_사용자는_인증_이메일을_요청할_수_있다(): void
    {
        Notification::fake();

        $user = UserModel::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/email/send-verification');

        $response->assertOk()
            ->assertJsonPath('message', 'Verification email sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_이미_인증된_사용자는_인증_이메일을_요청할_수_없다(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create(); // Already verified
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/email/send-verification');

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Email is already verified');

        Notification::assertNothingSent();
    }

    public function test_인증되지_않은_사용자는_인증_이메일을_요청할_수_없다(): void
    {
        $response = $this->postJson('/api/auth/email/send-verification');

        $response->assertUnauthorized();
    }

    public function test_유효한_서명으로_이메일을_인증할_수_있다(): void
    {
        $user = UserModel::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // Extract path and query from URL
        $parsedUrl = parse_url($verificationUrl);
        $apiPath = '/api/auth/email/verify/'.$user->getKey().'/'.sha1($user->getEmailForVerification());

        Sanctum::actingAs($user);

        $response = $this->getJson($apiPath.'?'.($parsedUrl['query'] ?? ''));

        $response->assertOk()
            ->assertJsonPath('message', 'Email verified successfully');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_유효하지_않은_서명으로_이메일_인증시_에러가_발생한다(): void
    {
        $user = UserModel::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $apiPath = '/api/auth/email/verify/'.$user->getKey().'/'.sha1($user->getEmailForVerification());

        $response = $this->getJson($apiPath.'?signature=invalid');

        $response->assertForbidden();
    }

    public function test_다른_사용자의_인증_링크로_인증할_수_없다(): void
    {
        $user = UserModel::factory()->unverified()->create();
        $otherUser = UserModel::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $otherUser->getKey(),
                'hash' => sha1($otherUser->getEmailForVerification()),
            ]
        );

        $parsedUrl = parse_url($verificationUrl);
        $apiPath = '/api/auth/email/verify/'.$otherUser->getKey().'/'.sha1($otherUser->getEmailForVerification());

        Sanctum::actingAs($user);

        $response = $this->getJson($apiPath.'?'.($parsedUrl['query'] ?? ''));

        $response->assertForbidden();
    }
}
