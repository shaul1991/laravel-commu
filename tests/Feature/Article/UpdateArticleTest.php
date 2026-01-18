<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateArticleTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

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

        $this->token = $this->user->createToken('auth-token')->accessToken;
    }

    #[Test]
    public function author_can_update_own_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Original Title',
            'slug' => 'original-title-123456',
            'content_markdown' => 'Original content',
            'content_html' => '<p>Original content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/articles/{$article->slug}", [
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'category' => 'career',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.category', 'career');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated Title',
            'category' => 'career',
        ]);
    }

    #[Test]
    public function updating_article_regenerates_slug(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Original Title',
            'slug' => 'original-title-123456',
            'content_markdown' => 'Original content',
            'content_html' => '<p>Original content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/articles/{$article->slug}", [
                'title' => 'Completely New Title',
                'content' => 'Updated content',
                'category' => 'tech',
            ]);

        $response->assertStatus(200);

        $newSlug = $response->json('data.slug');
        $this->assertStringContainsString('completely-new-title', $newSlug);
    }

    #[Test]
    public function non_author_cannot_update_article(): void
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
            ->putJson("/api/articles/{$article->slug}", [
                'title' => 'Trying to Update',
                'content' => 'New content',
                'category' => 'tech',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function unauthenticated_user_cannot_update_article(): void
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

        $response = $this->putJson("/api/articles/{$article->slug}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category' => 'tech',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function cannot_update_archived_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Archived Article',
            'slug' => 'archived-article-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'archived',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/articles/{$article->slug}", [
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'category' => 'tech',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function author_can_publish_draft_article(): void
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
            ->putJson("/api/articles/{$article->slug}", [
                'title' => 'Now Published',
                'content' => 'Content',
                'category' => 'tech',
                'is_draft' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'published',
        ]);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
            'published_at' => null,
        ]);
    }
}
