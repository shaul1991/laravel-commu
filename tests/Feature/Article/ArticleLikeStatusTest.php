<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 아티클 좋아요 상태 동기화 테스트
 *
 * ECS-196: 아티클 상세 페이지에서 좋아요 상태가 동기화되지 않는 버그 수정
 */
final class ArticleLikeStatusTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private UserModel $otherUser;

    private string $token;

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

        $this->otherUser = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'name' => '김철수',
            'email' => 'kim@example.com',
            'username' => 'kimchulsoo',
            'password' => Hash::make('Password123!'),
        ]);

        $this->token = $this->user->createToken('auth-token')->accessToken;
    }

    #[Test]
    public function article_detail_response_includes_is_liked_field_for_authenticated_user(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->otherUser->id,
            'title' => 'Test Article',
            'slug' => 'test-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'is_liked', // is_liked 필드가 응답에 포함되어야 함
                ],
            ])
            ->assertJsonPath('data.is_liked', false);
    }

    #[Test]
    public function article_detail_shows_is_liked_true_when_user_has_liked(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440002',
            'author_id' => $this->otherUser->id,
            'title' => 'Liked Article',
            'slug' => 'liked-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 1,
            'published_at' => now(),
        ]);

        // 사용자가 이미 좋아요를 누른 상태
        DB::table('article_likes')->insert([
            'article_id' => $article->id,
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_liked', true);
    }

    #[Test]
    public function article_detail_shows_is_liked_false_for_guest_user(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440003',
            'author_id' => $this->otherUser->id,
            'title' => 'Public Article',
            'slug' => 'public-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 5,
            'published_at' => now(),
        ]);

        // 비로그인 사용자
        $response = $this->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_liked', false);
    }

    #[Test]
    public function article_detail_shows_correct_is_liked_status_for_own_article(): void
    {
        // 자신이 작성한 아티클에 좋아요를 누른 경우
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440004',
            'author_id' => $this->user->id,
            'title' => 'My Article',
            'slug' => 'my-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 1,
            'published_at' => now(),
        ]);

        // 자신의 아티클에 좋아요를 누른 상태
        DB::table('article_likes')->insert([
            'article_id' => $article->id,
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_liked', true)
            ->assertJsonPath('data.is_author', true);
    }

    #[Test]
    public function article_detail_is_liked_reflects_current_user_not_others(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440005',
            'author_id' => $this->otherUser->id,
            'title' => 'Popular Article',
            'slug' => 'popular-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 10,
            'published_at' => now(),
        ]);

        // 다른 사용자만 좋아요를 누른 상태 (현재 사용자는 누르지 않음)
        DB::table('article_likes')->insert([
            'article_id' => $article->id,
            'user_id' => $this->otherUser->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_liked', false) // 현재 사용자는 좋아요를 누르지 않음
            ->assertJsonPath('data.like_count', 10); // 전체 좋아요 수는 유지
    }
}
