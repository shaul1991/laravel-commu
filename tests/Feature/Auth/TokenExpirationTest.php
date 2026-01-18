<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 토큰 만료 처리 테스트
 *
 * ECS-126: Access Token 만료 시 401 에러 미처리 버그 수정
 * ECS-169: Sanctum -> Passport 마이그레이션
 */
final class TokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

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

        $this->user = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);
    }

    #[Test]
    public function revoked_token_returns_401_for_protected_routes(): void
    {
        // 토큰 생성
        $tokenResult = $this->user->createToken('auth-token');

        // 토큰 취소 (만료 시뮬레이션)
        $tokenId = $this->getTokenIdFromJwt($tokenResult->accessToken);
        $token = Token::find($tokenId);
        $token->revoke();

        $response = $this->withHeader('Authorization', "Bearer {$tokenResult->accessToken}")
            ->getJson('/api/auth/me');

        // 취소된 토큰은 401을 반환해야 함
        $response->assertStatus(401);
    }

    #[Test]
    public function valid_token_allows_access_to_protected_routes(): void
    {
        $tokenResult = $this->user->createToken('auth-token');

        $response = $this->withHeader('Authorization', "Bearer {$tokenResult->accessToken}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'hong@example.com');
    }

    #[Test]
    public function revoked_token_returns_401_for_comment_creation(): void
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

        // 취소된 토큰 생성
        $tokenResult = $this->user->createToken('auth-token');
        $tokenId = $this->getTokenIdFromJwt($tokenResult->accessToken);
        $token = Token::find($tokenId);
        $token->revoke();

        $response = $this->withHeader('Authorization', "Bearer {$tokenResult->accessToken}")
            ->postJson("/api/articles/{$article->slug}/comments", [
                'content' => 'Test comment',
            ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function passport_token_expiration_is_configured(): void
    {
        // Passport 토큰 만료 시간이 설정되어 있는지 확인
        $accessExpiration = config('passport.tokens_expire_in');
        $refreshExpiration = config('passport.refresh_tokens_expire_in');

        $this->assertNotNull($accessExpiration, 'Passport access token expiration should be configured');
        $this->assertIsInt($accessExpiration, 'Passport access token expiration should be an integer (minutes)');
        $this->assertGreaterThan(0, $accessExpiration, 'Passport access token expiration should be greater than 0');

        $this->assertNotNull($refreshExpiration, 'Passport refresh token expiration should be configured');
        $this->assertIsInt($refreshExpiration, 'Passport refresh token expiration should be an integer (minutes)');
        $this->assertGreaterThan(0, $refreshExpiration, 'Passport refresh token expiration should be greater than 0');
    }

    /**
     * JWT에서 토큰 ID 추출
     */
    private function getTokenIdFromJwt(string $jwt): ?string
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);

            return $payload['jti'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
