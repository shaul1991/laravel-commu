<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 토큰 만료 처리 테스트
 *
 * ECS-126: Access Token 만료 시 401 에러 미처리 버그 수정
 */
final class TokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);
    }

    #[Test]
    public function expired_token_returns_401_for_protected_routes(): void
    {
        // 토큰 생성 후 만료 시간을 과거로 설정
        $token = $this->user->createToken('auth-token');

        // 토큰의 created_at을 과거로 설정하여 만료 시뮬레이션
        // Sanctum expiration이 설정되어 있어야 만료 검사가 동작함
        $accessToken = PersonalAccessToken::findToken($token->plainTextToken);
        $accessToken->created_at = now()->subMinutes(config('sanctum.expiration', 60) + 1);
        $accessToken->save();

        $response = $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
            ->getJson('/api/auth/me');

        // 만료된 토큰은 401을 반환해야 함
        $response->assertStatus(401);
    }

    #[Test]
    public function valid_token_allows_access_to_protected_routes(): void
    {
        $token = $this->user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'hong@example.com');
    }

    #[Test]
    public function expired_token_returns_401_for_comment_creation(): void
    {
        // 먼저 아티클 생성
        $article = \App\Infrastructure\Persistence\Eloquent\ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-123456',
            'content_markdown' => '# Test',
            'content_html' => '<h1>Test</h1>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        // 만료된 토큰 생성
        $token = $this->user->createToken('auth-token');
        $accessToken = PersonalAccessToken::findToken($token->plainTextToken);
        $accessToken->created_at = now()->subMinutes(config('sanctum.expiration', 60) + 1);
        $accessToken->save();

        $response = $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
            ->postJson("/api/articles/{$article->slug}/comments", [
                'content' => 'Test comment',
            ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function sanctum_expiration_is_configured(): void
    {
        // Sanctum 토큰 만료 시간이 설정되어 있는지 확인
        $expiration = config('sanctum.expiration');

        $this->assertNotNull($expiration, 'Sanctum token expiration should be configured');
        $this->assertIsInt($expiration, 'Sanctum token expiration should be an integer (minutes)');
        $this->assertGreaterThan(0, $expiration, 'Sanctum token expiration should be greater than 0');
    }
}
