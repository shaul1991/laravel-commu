<?php

declare(strict_types=1);

namespace Tests\Feature\Tag;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagApiTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys', ['--force' => true]);
        }

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
    public function can_get_all_tags(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'PHP', 'slug' => 'php']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'JavaScript', 'slug' => 'javascript']);

        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'article_count'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_get_popular_tags(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 50]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'PHP', 'slug' => 'php', 'article_count' => 30]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'JavaScript', 'slug' => 'javascript', 'article_count' => 20]);

        $response = $this->getJson('/api/tags/popular?limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'article_count'],
                ],
            ]);

        // 인기순(article_count 내림차순)으로 정렬되어야 함
        $data = $response->json('data');
        $this->assertEquals('Laravel', $data[0]['name']);
        $this->assertEquals('PHP', $data[1]['name']);
    }

    #[Test]
    public function can_search_tags(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel Mix', 'slug' => 'laravel-mix']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'PHP', 'slug' => 'php']);

        $response = $this->getJson('/api/tags/search?q=laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'article_count'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function search_tags_returns_empty_for_no_match(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $response = $this->getJson('/api/tags/search?q=nonexistent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function can_get_tag_by_slug(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 42]);

        $response = $this->getJson('/api/tags/laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'article_count'],
            ])
            ->assertJsonPath('data.name', 'Laravel')
            ->assertJsonPath('data.slug', 'laravel')
            ->assertJsonPath('data.article_count', 42);
    }

    #[Test]
    public function get_tag_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/tags/nonexistent');

        $response->assertStatus(404);
    }

    #[Test]
    public function can_get_articles_by_tag(): void
    {
        $tag = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $article = ArticleModel::create([
            'uuid' => fake()->uuid(),
            'author_id' => $this->user->id,
            'title' => 'Laravel Tutorial',
            'slug' => 'laravel-tutorial',
            'content_markdown' => 'Content about Laravel',
            'content_html' => '<p>Content about Laravel</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $article->tags()->attach($tag->id);

        $response = $this->getJson('/api/tags/laravel/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'author'],
                ],
                'meta' => ['total', 'per_page', 'current_page'],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel Tutorial');
    }

    #[Test]
    public function can_create_article_with_tags(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Laravel 12 새로운 기능',
                'content' => 'Laravel 12의 새로운 기능을 소개합니다.',
                'category' => 'tech',
                'tags' => ['Laravel', 'PHP', 'Backend'],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'tags' => [
                        '*' => ['id', 'name', 'slug'],
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data.tags');

        // 태그가 DB에 생성되어야 함
        $this->assertDatabaseHas('tags', ['name' => 'Laravel']);
        $this->assertDatabaseHas('tags', ['name' => 'PHP']);
        $this->assertDatabaseHas('tags', ['name' => 'Backend']);
    }

    #[Test]
    public function article_with_tags_reuses_existing_tags(): void
    {
        // 기존 태그 생성
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Laravel 팁',
                'content' => 'Laravel 사용 팁입니다.',
                'category' => 'tech',
                'tags' => ['Laravel', 'PHP'],
            ]);

        $response->assertStatus(201);

        // Laravel 태그는 중복 생성되지 않아야 함
        $this->assertEquals(1, TagModel::where('name', 'Laravel')->count());
        // PHP 태그는 새로 생성
        $this->assertEquals(1, TagModel::where('name', 'PHP')->count());
    }

    #[Test]
    public function can_add_many_tags_to_article(): void
    {
        // 태그 갯수 제한 없음 - 여러 개의 태그 추가 가능
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Article with many tags',
                'content' => 'Content here.',
                'category' => 'tech',
                'tags' => ['Tag1', 'Tag2', 'Tag3', 'Tag4', 'Tag5', 'Tag6', 'Tag7', 'Tag8'],
            ]);

        $response->assertStatus(201)
            ->assertJsonCount(8, 'data.tags');
    }

    #[Test]
    public function can_update_article_tags(): void
    {
        // 아티클 생성
        $createResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Original Article',
                'content' => 'Original content',
                'category' => 'tech',
                'tags' => ['Laravel', 'PHP'],
            ]);

        $articleSlug = $createResponse->json('data.slug');

        // 태그 변경
        $updateResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/articles/{$articleSlug}", [
                'title' => 'Updated Article',
                'content' => 'Updated content',
                'category' => 'tech',
                'tags' => ['JavaScript', 'Vue'],
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonCount(2, 'data.tags');

        $tagNames = collect($updateResponse->json('data.tags'))->pluck('name')->toArray();
        $this->assertContains('JavaScript', $tagNames);
        $this->assertContains('Vue', $tagNames);
        $this->assertNotContains('Laravel', $tagNames);
        $this->assertNotContains('PHP', $tagNames);
    }

    #[Test]
    public function article_detail_includes_tags(): void
    {
        $tag = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $article = ArticleModel::create([
            'uuid' => fake()->uuid(),
            'author_id' => $this->user->id,
            'title' => 'Laravel Guide',
            'slug' => 'laravel-guide',
            'content_markdown' => 'Guide content',
            'content_html' => '<p>Guide content</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $article->tags()->attach($tag->id);

        $response = $this->getJson('/api/articles/laravel-guide');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'tags' => [
                        '*' => ['id', 'name', 'slug'],
                    ],
                ],
            ])
            ->assertJsonPath('data.tags.0.name', 'Laravel');
    }

    #[Test]
    public function tag_article_count_increments_when_article_created(): void
    {
        $tag = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 0]);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'New Article',
                'content' => 'Content here',
                'category' => 'tech',
                'tags' => ['Laravel'],
            ]);

        $this->assertEquals(1, $tag->fresh()->article_count);
    }

    #[Test]
    public function tag_article_count_decrements_when_article_deleted(): void
    {
        $tag = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 1]);

        $article = ArticleModel::create([
            'uuid' => fake()->uuid(),
            'author_id' => $this->user->id,
            'title' => 'To Be Deleted',
            'slug' => 'to-be-deleted',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $article->tags()->attach($tag->id);

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/articles/{$article->slug}");

        $this->assertEquals(0, $tag->fresh()->article_count);
    }
}
