<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeleteArticleTest extends TestCase
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
    public function author_can_delete_own_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Article to Delete',
            'slug' => 'article-to-delete-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Article deleted successfully',
            ]);

        // Soft delete - article should still exist but with deleted_at
        $this->assertSoftDeleted('articles', [
            'id' => $article->id,
        ]);
    }

    #[Test]
    public function non_author_cannot_delete_article(): void
    {
        $otherUser = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440099',
            'name' => '김철수',
            'email' => 'kim@example.com',
            'username' => 'kimcheolsu',
            'password' => Hash::make('Password123!'),
        ]);

        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $otherUser->id,
            'title' => 'Other User Article',
            'slug' => 'other-user-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/articles/{$article->slug}");

        $response->assertStatus(403);
    }

    #[Test]
    public function unauthenticated_user_cannot_delete_article(): void
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

        $response = $this->deleteJson("/api/articles/{$article->slug}");

        $response->assertStatus(401);
    }

    #[Test]
    public function author_can_delete_draft_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Draft to Delete',
            'slug' => 'draft-to-delete-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'draft',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => null,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/articles/{$article->slug}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('articles', [
            'id' => $article->id,
        ]);
    }

    #[Test]
    public function returns_404_when_deleting_nonexistent_article(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson('/api/articles/nonexistent-slug');

        $response->assertStatus(404);
    }
}
