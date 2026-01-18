<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

final class OAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_소셜_로그인_리다이렉트를_수행한다(): void
    {
        $response = $this->get('/api/auth/oauth/google/redirect');

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_지원하지_않는_프로바이더는_에러를_반환한다(): void
    {
        $response = $this->get('/api/auth/oauth/invalid/redirect');

        $response->assertRedirect('/login?error=unsupported_provider');
    }

    public function test_git_hub_소셜_로그인_리다이렉트를_수행한다(): void
    {
        $response = $this->get('/api/auth/oauth/github/redirect');

        $response->assertRedirect();
        $this->assertStringContainsString('github.com', $response->headers->get('Location'));
    }

    public function test_새_사용자가_소셜_로그인으로_가입할_수_있다(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('social@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Social User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->shouldReceive('getNickname')->andReturn('socialuser');
        $socialiteUser->token = 'fake-access-token';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = 3600;

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);

        $response = $this->get('/api/auth/oauth/google/callback?code=valid-auth-code');

        $response->assertOk();
        $response->assertSee('localStorage.setItem', false);
        $response->assertSee("window.location.href = '/'", false);

        $this->assertDatabaseHas('users', [
            'email' => 'social@example.com',
        ]);
    }

    public function test_기존_사용자가_소셜_로그인으로_로그인할_수_있다(): void
    {
        $existingUser = UserModel::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->shouldReceive('getNickname')->andReturn('existinguser');
        $socialiteUser->token = 'fake-access-token';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = 3600;

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('github')
            ->andReturn($driver);

        $response = $this->get('/api/auth/oauth/github/callback?code=valid-auth-code');

        $response->assertOk();
        $response->assertSee('localStorage.setItem', false);

        // Ensure no new user was created
        $this->assertEquals(1, UserModel::count());
    }

    public function test_콜백에_코드가_없으면_로그인_페이지로_리다이렉트된다(): void
    {
        $response = $this->get('/api/auth/oauth/google/callback');

        $response->assertRedirect('/login?error=oauth_failed');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
