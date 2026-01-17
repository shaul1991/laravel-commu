<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListArticlesTest extends TestCase
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
    public function can_list_published_articles(): void
    {
        // Create published articles
        for ($i = 1; $i <= 3; $i++) {
            ArticleModel::create([
                'uuid' => "660e8400-e29b-41d4-a716-44665544000{$i}",
                'author_id' => $this->user->id,
                'title' => "Article {$i}",
                'slug' => "article-{$i}-123456",
                'content_markdown' => "Content {$i}",
                'content_html' => "<p>Content {$i}</p>",
                'category' => 'tech',
                'status' => 'published',
                'view_count' => 0,
                'like_count' => 0,
                'published_at' => now()->subHours($i),
            ]);
        }

        // Create a draft article (should not appear in list)
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440099',
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

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'category',
                        'view_count',
                        'like_count',
                        'author' => [
                            'id',
                            'name',
                            'username',
                            'avatar_url',
                        ],
                        'published_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function articles_are_sorted_by_published_at_desc_by_default(): void
    {
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Old Article',
            'slug' => 'old-article-123456',
            'content_markdown' => 'Old content',
            'content_html' => '<p>Old content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now()->subDays(2),
        ]);

        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440002',
            'author_id' => $this->user->id,
            'title' => 'New Article',
            'slug' => 'new-article-123456',
            'content_markdown' => 'New content',
            'content_html' => '<p>New content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'New Article')
            ->assertJsonPath('data.1.title', 'Old Article');
    }

    #[Test]
    public function can_filter_articles_by_category(): void
    {
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Tech Article',
            'slug' => 'tech-article-123456',
            'content_markdown' => 'Tech content',
            'content_html' => '<p>Tech content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440002',
            'author_id' => $this->user->id,
            'title' => 'Career Article',
            'slug' => 'career-article-123456',
            'content_markdown' => 'Career content',
            'content_html' => '<p>Career content</p>',
            'category' => 'career',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/articles?category=tech');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'tech');
    }

    #[Test]
    public function can_sort_articles_by_popularity(): void
    {
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Less Popular',
            'slug' => 'less-popular-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 10,
            'like_count' => 5,
            'published_at' => now(),
        ]);

        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440002',
            'author_id' => $this->user->id,
            'title' => 'More Popular',
            'slug' => 'more-popular-123456',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 100,
            'like_count' => 50,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/articles?sort=popular');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'More Popular')
            ->assertJsonPath('data.1.title', 'Less Popular');
    }

    #[Test]
    public function can_paginate_articles(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            ArticleModel::create([
                'uuid' => sprintf('660e8400-e29b-41d4-a716-4466554400%02d', $i),
                'author_id' => $this->user->id,
                'title' => "Article {$i}",
                'slug' => "article-{$i}-123456",
                'content_markdown' => "Content {$i}",
                'content_html' => "<p>Content {$i}</p>",
                'category' => 'tech',
                'status' => 'published',
                'view_count' => 0,
                'like_count' => 0,
                'published_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->getJson('/api/articles?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2);

        $response2 = $this->getJson('/api/articles?per_page=10&page=2');

        $response2->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }
}
