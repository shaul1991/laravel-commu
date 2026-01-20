<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Tag;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Services\EloquentSyncArticleTagsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SyncArticleTagsServiceTest extends TestCase
{
    use RefreshDatabase;

    private EloquentSyncArticleTagsService $service;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EloquentSyncArticleTagsService;

        $this->user = UserModel::create([
            'uuid' => fake()->uuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
        ]);
    }

    private function createArticle(array $attributes = []): ArticleModel
    {
        return ArticleModel::create(array_merge([
            'uuid' => fake()->uuid(),
            'author_id' => $this->user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-'.fake()->unique()->numberBetween(1, 10000),
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }

    private function createTag(array $attributes = []): TagModel
    {
        return TagModel::create(array_merge([
            'uuid' => fake()->uuid(),
            'name' => 'Tag'.fake()->unique()->numberBetween(1, 10000),
            'slug' => 'tag-'.fake()->unique()->numberBetween(1, 10000),
            'article_count' => 0,
        ], $attributes));
    }

    #[Test]
    public function it_syncs_new_tags_to_article(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'PHP']);

        $article->refresh();
        $tagNames = $article->tags->pluck('name')->toArray();

        $this->assertCount(2, $tagNames);
        $this->assertContains('Laravel', $tagNames);
        $this->assertContains('PHP', $tagNames);
    }

    #[Test]
    public function it_reuses_existing_tags(): void
    {
        $existingTag = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'NewTag']);

        $article->refresh();

        // 기존 태그가 재사용되어야 함
        $this->assertEquals(1, TagModel::where('name', 'Laravel')->count());
        $this->assertContains($existingTag->id, $article->tags->pluck('id')->toArray());
    }

    #[Test]
    public function it_deduplicates_tags_case_insensitively(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'laravel', 'LARAVEL']);

        $article->refresh();

        // 하나의 태그만 연결되어야 함
        $this->assertCount(1, $article->tags);
        // DB에도 하나만 존재
        $this->assertEquals(1, TagModel::whereRaw('LOWER(name) = ?', ['laravel'])->count());
    }

    #[Test]
    public function it_preserves_first_occurrence_capitalization(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'laravel']);

        $article->refresh();

        // 첫 번째 발생의 대소문자가 유지되어야 함
        $this->assertEquals('Laravel', $article->tags->first()->name);
    }

    #[Test]
    public function it_finds_existing_tag_case_insensitively(): void
    {
        $existingTag = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = $this->createArticle();

        $this->service->sync($article, ['laravel']); // 소문자로 입력

        $article->refresh();

        // 기존 태그가 재사용되어야 함
        $this->assertContains($existingTag->id, $article->tags->pluck('id')->toArray());
        // 새 태그가 생성되지 않아야 함
        $this->assertEquals(1, TagModel::whereRaw('LOWER(name) = ?', ['laravel'])->count());
    }

    #[Test]
    public function it_trims_whitespace_from_tag_names(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['  Laravel  ', '   PHP   ']);

        $article->refresh();
        $tagNames = $article->tags->pluck('name')->toArray();

        $this->assertContains('Laravel', $tagNames);
        $this->assertContains('PHP', $tagNames);
        $this->assertNotContains('  Laravel  ', $tagNames);
    }

    #[Test]
    public function it_ignores_empty_tag_names(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', '', '   ', 'PHP']);

        $article->refresh();

        $this->assertCount(2, $article->tags);
    }

    #[Test]
    public function it_increments_article_count_for_new_tags(): void
    {
        $existingTag = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 5]);
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'NewTag']);

        $existingTag->refresh();
        $newTag = TagModel::where('name', 'NewTag')->first();

        $this->assertEquals(6, $existingTag->article_count);
        $this->assertEquals(1, $newTag->article_count);
    }

    #[Test]
    public function it_decrements_article_count_for_removed_tags(): void
    {
        $tag1 = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 5]);
        $tag2 = $this->createTag(['name' => 'PHP', 'slug' => 'php', 'article_count' => 3]);
        $article = $this->createArticle();

        // 먼저 두 태그 연결
        $article->tags()->attach([$tag1->id, $tag2->id]);
        $article->refresh();

        // PHP 태그 제거
        $this->service->sync($article, ['Laravel']);

        $tag1->refresh();
        $tag2->refresh();

        // Laravel은 유지되므로 카운트 변화 없음
        $this->assertEquals(5, $tag1->article_count);
        // PHP는 제거되므로 카운트 감소
        $this->assertEquals(2, $tag2->article_count);
    }

    #[Test]
    public function it_handles_complete_tag_replacement(): void
    {
        $oldTag = $this->createTag(['name' => 'OldTag', 'slug' => 'old-tag', 'article_count' => 1]);
        $article = $this->createArticle();
        $article->tags()->attach([$oldTag->id]);
        $article->refresh();

        $this->service->sync($article, ['NewTag']);

        $article->refresh();
        $oldTag->refresh();

        $this->assertCount(1, $article->tags);
        $this->assertEquals('NewTag', $article->tags->first()->name);
        $this->assertEquals(0, $oldTag->article_count);
    }

    #[Test]
    public function it_handles_empty_tag_array(): void
    {
        $tag = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 1]);
        $article = $this->createArticle();
        $article->tags()->attach([$tag->id]);
        $article->refresh();

        $this->service->sync($article, []);

        $article->refresh();
        $tag->refresh();

        $this->assertCount(0, $article->tags);
        $this->assertEquals(0, $tag->article_count);
    }

    #[Test]
    public function decrement_counts_for_article_decrements_all_tags(): void
    {
        $tag1 = $this->createTag(['name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 5]);
        $tag2 = $this->createTag(['name' => 'PHP', 'slug' => 'php', 'article_count' => 3]);
        $article = $this->createArticle();
        $article->tags()->attach([$tag1->id, $tag2->id]);
        $article->refresh();

        $this->service->decrementCountsForArticle($article);

        $tag1->refresh();
        $tag2->refresh();

        $this->assertEquals(4, $tag1->article_count);
        $this->assertEquals(2, $tag2->article_count);
    }

    #[Test]
    public function it_generates_unique_slugs_for_duplicate_names(): void
    {
        // 이미 'laravel' slug가 존재
        $this->createTag(['name' => 'Existing', 'slug' => 'laravel']);
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel']);

        $newTag = TagModel::where('name', 'Laravel')->first();

        // 중복되지 않는 slug가 생성되어야 함
        $this->assertNotEquals('laravel', $newTag->slug);
        $this->assertStringStartsWith('laravel', $newTag->slug);
    }

    #[Test]
    public function it_handles_korean_tag_names(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['라라벨', '프로그래밍']);

        $article->refresh();
        $tagNames = $article->tags->pluck('name')->toArray();

        $this->assertContains('라라벨', $tagNames);
        $this->assertContains('프로그래밍', $tagNames);
    }

    #[Test]
    public function it_generates_correct_slug_for_korean_names(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['라라벨']);

        $tag = TagModel::where('name', '라라벨')->first();

        $this->assertEquals('라라벨', $tag->slug);
    }

    #[Test]
    public function it_does_not_create_duplicate_when_syncing_same_tags(): void
    {
        $article = $this->createArticle();

        $this->service->sync($article, ['Laravel', 'PHP']);
        $initialCount = TagModel::count();

        // 같은 태그로 다시 sync
        $this->service->sync($article, ['Laravel', 'PHP']);

        $this->assertEquals($initialCount, TagModel::count());
    }
}
