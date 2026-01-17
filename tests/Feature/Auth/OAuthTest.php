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

    public function test_소셜_로그인_리다이렉트_ur_l을_반환한다(): void
    {
        $response = $this->getJson('/api/auth/oauth/google/redirect');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['url']]);
    }

    public function test_지원하지_않는_프로바이더는_에러를_반환한다(): void
    {
        $response = $this->getJson('/api/auth/oauth/invalid/redirect');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Unsupported OAuth provider');
    }

    public function test_git_hub_소셜_로그인_리다이렉트_ur_l을_반환한다(): void
    {
        $response = $this->getJson('/api/auth/oauth/github/redirect');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['url']]);
    }

    public function test_새_사용자가_소셜_로그인으로_가입할_수_있다(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('social@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Social User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->shouldReceive('getNickname')->andReturn('socialuser');

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($driver);

        $response = $this->postJson('/api/auth/oauth/google/callback', [
            'code' => 'valid-auth-code',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'username'],
                    'token',
                ],
                'message',
            ]);

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

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('github')
            ->andReturn($driver);

        $response = $this->postJson('/api/auth/oauth/github/callback', [
            'code' => 'valid-auth-code',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'existing@example.com');

        // Ensure no new user was created
        $this->assertEquals(1, UserModel::count());
    }

    public function test_콜백에_코드가_없으면_에러가_발생한다(): void
    {
        $response = $this->postJson('/api/auth/oauth/google/callback', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
