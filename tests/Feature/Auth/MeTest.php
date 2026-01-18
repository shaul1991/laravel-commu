<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Passport 키 생성
        if (! file_exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys', ['--force' => true]);
        }

        // Personal Access Client 생성
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);
    }

    #[Test]
    public function authenticated_user_can_get_their_info(): void
    {
        $user = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
            'bio' => 'Backend Developer',
        ]);

        $tokenResult = $user->createToken('auth-token');
        $accessToken = $tokenResult->accessToken;

        $response = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'username',
                    'bio',
                    'avatar_url',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.email', 'hong@example.com')
            ->assertJsonPath('data.username', 'honggildong')
            ->assertJsonPath('data.bio', 'Backend Developer');
    }

    #[Test]
    public function unauthenticated_user_cannot_get_info(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
