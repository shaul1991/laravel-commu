<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LikeArticleTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private string $token;

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

        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    #[Test]
    public function authenticated_user_can_like_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
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
            ->postJson("/api/articles/{$article->slug}/like");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_liked' => true,
                    'like_count' => 1,
                ],
            ]);

        $this->assertDatabaseHas('article_likes', [
            'article_id' => $article->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like_count' => 1,
        ]);
    }

    #[Test]
    public function authenticated_user_can_unlike_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 1,
            'published_at' => now(),
        ]);

        // Create existing like
        \DB::table('article_likes')->insert([
            'article_id' => $article->id,
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/articles/{$article->slug}/like");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_liked' => false,
                    'like_count' => 0,
                ],
            ]);

        $this->assertDatabaseMissing('article_likes', [
            'article_id' => $article->id,
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_like_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
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

        $response = $this->postJson("/api/articles/{$article->slug}/like");

        $response->assertStatus(401);
    }

    #[Test]
    public function cannot_like_draft_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Draft Article',
            'slug' => 'draft-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'draft',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => null,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/articles/{$article->slug}/like");

        $response->assertStatus(404);
    }

    #[Test]
    public function returns_404_for_nonexistent_article(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles/nonexistent-slug/like');

        $response->assertStatus(404);
    }
}
