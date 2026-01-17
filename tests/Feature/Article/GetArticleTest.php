<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GetArticleTest extends TestCase
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
    public function can_get_article_by_slug(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-123456',
            'content_markdown' => '# Test\n\nThis is a test.',
            'content_html' => '<h1>Test</h1><p>This is a test.</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'content_html',
                    'category',
                    'status',
                    'view_count',
                    'like_count',
                    'author' => [
                        'id',
                        'name',
                        'username',
                        'avatar_url',
                    ],
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Test Article')
            ->assertJsonPath('data.slug', 'test-article-123456');
    }

    #[Test]
    public function viewing_article_increments_view_count(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440002',
            'author_id' => $this->user->id,
            'title' => 'View Count Test',
            'slug' => 'view-count-test-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 5,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $this->getJson("/api/articles/{$article->slug}");

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'view_count' => 6,
        ]);
    }

    #[Test]
    public function returns_404_for_nonexistent_article(): void
    {
        $response = $this->getJson('/api/articles/nonexistent-slug');

        $response->assertStatus(404);
    }

    #[Test]
    public function draft_article_is_not_accessible_to_public(): void
    {
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440003',
            'author_id' => $this->user->id,
            'title' => 'Draft Article',
            'slug' => 'draft-article-123456',
            'content_markdown' => 'Draft content',
            'content_html' => '<p>Draft content</p>',
            'category' => 'tech',
            'status' => 'draft',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/articles/draft-article-123456');

        $response->assertStatus(404);
    }

    #[Test]
    public function author_can_view_own_draft_article(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440004',
            'author_id' => $this->user->id,
            'title' => 'My Draft Article',
            'slug' => 'my-draft-article-123456',
            'content_markdown' => 'Draft content',
            'content_html' => '<p>Draft content</p>',
            'category' => 'tech',
            'status' => 'draft',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => null,
        ]);

        $token = $this->user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'My Draft Article')
            ->assertJsonPath('data.status', 'draft');
    }
}
