<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\SocialAccountModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SocialAccountTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // ECS-147: SocialAccountModel Tests
    // ========================================

    #[Test]
    public function social_account_model_encrypts_access_token(): void
    {
        $user = $this->createUser();

        $socialAccount = SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '123456',
            'provider_email' => 'test@github.com',
            'nickname' => 'testuser',
            'access_token' => 'plain_access_token',
        ]);

        // Fresh load from database
        $socialAccount->refresh();

        // The accessor should decrypt the token
        $this->assertEquals('plain_access_token', $socialAccount->access_token);

        // Check that it's actually encrypted in the database
        $rawToken = $socialAccount->getRawOriginal('access_token');
        $this->assertNotEquals('plain_access_token', $rawToken);
    }

    #[Test]
    public function social_account_model_encrypts_refresh_token(): void
    {
        $user = $this->createUser();

        $socialAccount = SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '123456',
            'refresh_token' => 'plain_refresh_token',
        ]);

        $socialAccount->refresh();

        $this->assertEquals('plain_refresh_token', $socialAccount->refresh_token);

        $rawToken = $socialAccount->getRawOriginal('refresh_token');
        $this->assertNotEquals('plain_refresh_token', $rawToken);
    }

    #[Test]
    public function social_account_belongs_to_user(): void
    {
        $user = $this->createUser();

        $socialAccount = SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '123456',
        ]);

        $this->assertInstanceOf(UserModel::class, $socialAccount->user);
        $this->assertEquals($user->id, $socialAccount->user->id);
    }

    #[Test]
    public function user_has_many_social_accounts(): void
    {
        $user = $this->createUser();

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '123456',
        ]);

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '789012',
        ]);

        $this->assertCount(2, $user->socialAccounts);
    }

    #[Test]
    public function social_account_uses_soft_deletes(): void
    {
        $user = $this->createUser();

        $socialAccount = SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '123456',
        ]);

        $socialAccount->delete();

        $this->assertSoftDeleted('social_accounts', [
            'id' => $socialAccount->id,
        ]);

        // Should not be found in normal queries
        $this->assertNull(SocialAccountModel::find($socialAccount->id));

        // Should be found with trashed
        $this->assertNotNull(SocialAccountModel::withTrashed()->find($socialAccount->id));
    }

    // ========================================
    // ECS-148: OAuthController Tests
    // ========================================

    #[Test]
    public function oauth_callback_logs_in_existing_user_via_social_account(): void
    {
        $user = $this->createUser(['email' => 'existing@example.com']);

        // Create existing social account
        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
            'provider_email' => 'existing@example.com',
        ]);

        $this->mockSocialiteUser('github', [
            'id' => 'github_123',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'nickname' => 'existinguser',
            'avatar' => 'https://example.com/avatar.jpg',
            'token' => 'new_access_token',
            'refreshToken' => 'new_refresh_token',
            'expiresIn' => 3600,
        ]);

        $response = $this->get('/api/auth/oauth/github/callback?code=valid_code');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'username'],
                    'token',
                ],
                'message',
            ]);

        $this->assertEquals($user->uuid, $response->json('data.user.id'));
    }

    #[Test]
    public function oauth_callback_links_social_account_to_existing_user_by_email(): void
    {
        $user = $this->createUser(['email' => 'user@example.com']);

        $this->mockSocialiteUser('github', [
            'id' => 'github_new_123',
            'email' => 'user@example.com',
            'name' => 'User Name',
            'nickname' => 'username',
            'avatar' => 'https://example.com/avatar.jpg',
            'token' => 'access_token',
            'refreshToken' => 'refresh_token',
            'expiresIn' => 3600,
        ]);

        $response = $this->get('/api/auth/oauth/github/callback?code=valid_code');

        $response->assertStatus(200);

        // Verify social account was created and linked
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_new_123',
        ]);
    }

    #[Test]
    public function oauth_callback_creates_new_user_and_social_account(): void
    {
        $this->mockSocialiteUser('github', [
            'id' => 'github_brand_new',
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'nickname' => 'newuser',
            'avatar' => 'https://example.com/avatar.jpg',
            'token' => 'access_token',
            'refreshToken' => 'refresh_token',
            'expiresIn' => 3600,
        ]);

        $response = $this->get('/api/auth/oauth/github/callback?code=valid_code');

        $response->assertStatus(200);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);

        $user = UserModel::where('email', 'newuser@example.com')->first();

        // Verify social account was created
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_brand_new',
        ]);
    }

    #[Test]
    public function oauth_callback_updates_social_account_tokens(): void
    {
        $user = $this->createUser();

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
            'access_token' => 'old_token',
            'refresh_token' => 'old_refresh_token',
        ]);

        $this->mockSocialiteUser('github', [
            'id' => 'github_123',
            'email' => $user->email,
            'name' => $user->name,
            'nickname' => 'nickname',
            'avatar' => 'https://example.com/avatar.jpg',
            'token' => 'new_access_token',
            'refreshToken' => 'new_refresh_token',
            'expiresIn' => 3600,
        ]);

        $response = $this->get('/api/auth/oauth/github/callback?code=valid_code');

        $response->assertStatus(200);

        $socialAccount = SocialAccountModel::where('provider', 'github')
            ->where('provider_id', 'github_123')
            ->first();

        $this->assertEquals('new_access_token', $socialAccount->access_token);
        $this->assertEquals('new_refresh_token', $socialAccount->refresh_token);
    }

    // ========================================
    // ECS-149: SocialAccountController Tests
    // ========================================

    #[Test]
    public function user_can_list_linked_social_accounts(): void
    {
        $user = $this->createUser();

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
            'provider_email' => 'user@github.com',
            'nickname' => 'githubuser',
            'avatar_url' => 'https://github.com/avatar.jpg',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/auth/social-accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'github',
                    'google',
                ],
            ]);

        $this->assertNotNull($response->json('data.github'));
        $this->assertNull($response->json('data.google'));
    }

    #[Test]
    public function user_can_unlink_social_account(): void
    {
        $user = $this->createUser(['password' => Hash::make('password123')]);

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social-accounts/github');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Social account unlinked successfully',
            ]);

        // Verify soft deleted
        $this->assertSoftDeleted('social_accounts', [
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);
    }

    #[Test]
    public function user_cannot_unlink_last_authentication_method(): void
    {
        // User without password (OAuth only user) - use empty string as marker
        $user = $this->createOAuthOnlyUser();

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social-accounts/github');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot unlink the last authentication method',
            ]);

        // Verify NOT deleted
        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'github',
            'provider_id' => 'github_123',
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function user_with_password_can_unlink_all_social_accounts(): void
    {
        $user = $this->createUser(['password' => Hash::make('password123')]);

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social-accounts/github');

        $response->assertStatus(200);

        $this->assertSoftDeleted('social_accounts', [
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);
    }

    #[Test]
    public function user_with_multiple_social_accounts_can_unlink_one(): void
    {
        $user = $this->createOAuthOnlyUser();

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => 'github_123',
        ]);

        SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_456',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social-accounts/github');

        $response->assertStatus(200);

        $this->assertSoftDeleted('social_accounts', [
            'provider' => 'github',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function unlink_returns_404_for_non_linked_provider(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social-accounts/github');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Social account not found',
            ]);
    }

    #[Test]
    public function social_accounts_endpoints_require_authentication(): void
    {
        $this->getJson('/api/auth/social-accounts')
            ->assertStatus(401);

        $this->deleteJson('/api/auth/social-accounts/github')
            ->assertStatus(401);
    }

    // ========================================
    // Helper Methods
    // ========================================

    private function createUser(array $attributes = []): UserModel
    {
        return UserModel::create(array_merge([
            'uuid' => fake()->uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => Hash::make('password'),
        ], $attributes));
    }

    /**
     * Create a user without a password (OAuth-only user).
     * The password is set to an empty string hash to satisfy DB constraint.
     */
    private function createOAuthOnlyUser(array $attributes = []): UserModel
    {
        return UserModel::create(array_merge([
            'uuid' => fake()->uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => '', // Empty password - treated as "no password set"
        ], $attributes));
    }

    private function mockSocialiteUser(string $provider, array $data): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($data['id']);
        $socialiteUser->shouldReceive('getEmail')->andReturn($data['email']);
        $socialiteUser->shouldReceive('getName')->andReturn($data['name']);
        $socialiteUser->shouldReceive('getNickname')->andReturn($data['nickname']);
        $socialiteUser->shouldReceive('getAvatar')->andReturn($data['avatar']);
        $socialiteUser->token = $data['token'];
        $socialiteUser->refreshToken = $data['refreshToken'] ?? null;
        $socialiteUser->expiresIn = $data['expiresIn'] ?? null;

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturn($driver);
    }
}
