<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Domain\Core\Tag\Entities\Tag;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\EloquentTagRepository;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentTagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentTagRepository;
    }

    #[Test]
    public function find_or_create_returns_existing_tag(): void
    {
        TagModel::create([
            'uuid' => fake()->uuid(),
            'name' => 'Laravel',
            'slug' => 'laravel',
            'article_count' => 5,
        ]);

        $tag = $this->repository->findOrCreate('Laravel');

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('Laravel', $tag->name());
        $this->assertEquals(5, $tag->articleCount());

        // 중복 생성되지 않아야 함
        $this->assertEquals(1, TagModel::where('name', 'Laravel')->count());
    }

    #[Test]
    public function find_or_create_creates_new_tag(): void
    {
        $tag = $this->repository->findOrCreate('NewTag');

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('NewTag', $tag->name());
        $this->assertEquals(0, $tag->articleCount());

        // DB에 저장되어야 함
        $this->assertDatabaseHas('tags', ['name' => 'NewTag']);
    }

    #[Test]
    public function find_by_article_ids_eager_loads_tags(): void
    {
        $user = UserModel::create([
            'uuid' => fake()->uuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $tag1 = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag1', 'slug' => 'tag1']);
        $tag2 = TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag2', 'slug' => 'tag2']);

        $article = ArticleModel::create([
            'uuid' => fake()->uuid(),
            'author_id' => $user->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $article->tags()->attach([$tag1->id, $tag2->id]);

        $articleId = new \App\Domain\Core\Article\ValueObjects\ArticleId($article->uuid);
        $result = $this->repository->findByArticleIds([$articleId]);

        $this->assertArrayHasKey($article->uuid, $result);
        $this->assertCount(2, $result[$article->uuid]);
        $this->assertContainsOnlyInstancesOf(Tag::class, $result[$article->uuid]);
    }

    #[Test]
    public function search_returns_matching_tags(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel Mix', 'slug' => 'laravel-mix']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'PHP', 'slug' => 'php']);

        $results = $this->repository->search('laravel', 10);

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(Tag::class, $results);
    }

    #[Test]
    public function find_popular_returns_tags_ordered_by_article_count(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Low', 'slug' => 'low', 'article_count' => 5]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'High', 'slug' => 'high', 'article_count' => 100]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Medium', 'slug' => 'medium', 'article_count' => 50]);

        $results = $this->repository->findPopular(3);

        $this->assertCount(3, $results);
        $this->assertEquals('High', $results[0]->name());
        $this->assertEquals('Medium', $results[1]->name());
        $this->assertEquals('Low', $results[2]->name());
    }

    #[Test]
    public function find_by_names_returns_matching_tags(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'PHP', 'slug' => 'php']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'JavaScript', 'slug' => 'javascript']);

        $results = $this->repository->findByNames(['Laravel', 'PHP']);

        $this->assertCount(2, $results);
        $names = array_map(fn (Tag $tag) => $tag->name(), $results);
        $this->assertContains('Laravel', $names);
        $this->assertContains('PHP', $names);
    }

    #[Test]
    public function find_returns_tag_by_id(): void
    {
        $uuid = fake()->uuid();
        TagModel::create(['uuid' => $uuid, 'name' => 'Laravel', 'slug' => 'laravel']);

        $tagId = new \App\Domain\Core\Tag\ValueObjects\TagId($uuid);
        $tag = $this->repository->find($tagId);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('Laravel', $tag->name());
    }

    #[Test]
    public function find_returns_null_for_nonexistent_id(): void
    {
        $tagId = new \App\Domain\Core\Tag\ValueObjects\TagId(fake()->uuid());
        $tag = $this->repository->find($tagId);

        $this->assertNull($tag);
    }

    #[Test]
    public function find_by_slug_returns_tag(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $tag = $this->repository->findBySlug('laravel');

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('Laravel', $tag->name());
    }

    #[Test]
    public function find_by_slug_returns_null_for_nonexistent(): void
    {
        $tag = $this->repository->findBySlug('nonexistent');

        $this->assertNull($tag);
    }

    #[Test]
    public function find_by_ids_returns_matching_tags(): void
    {
        $uuid1 = fake()->uuid();
        $uuid2 = fake()->uuid();
        TagModel::create(['uuid' => $uuid1, 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => $uuid2, 'name' => 'PHP', 'slug' => 'php']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'JavaScript', 'slug' => 'javascript']);

        $ids = [
            new \App\Domain\Core\Tag\ValueObjects\TagId($uuid1),
            new \App\Domain\Core\Tag\ValueObjects\TagId($uuid2),
        ];
        $tags = $this->repository->findByIds($ids);

        $this->assertCount(2, $tags);
    }

    #[Test]
    public function find_by_ids_returns_empty_array_for_empty_input(): void
    {
        $tags = $this->repository->findByIds([]);

        $this->assertIsArray($tags);
        $this->assertCount(0, $tags);
    }

    #[Test]
    public function delete_soft_deletes_tag(): void
    {
        $uuid = fake()->uuid();
        TagModel::create(['uuid' => $uuid, 'name' => 'Laravel', 'slug' => 'laravel']);

        $tagId = new \App\Domain\Core\Tag\ValueObjects\TagId($uuid);
        $tag = $this->repository->find($tagId);

        $this->repository->delete($tag);

        // Soft deleted이므로 일반 쿼리에서는 찾을 수 없음
        $this->assertNull($this->repository->find($tagId));
        // withTrashed로는 찾을 수 있음
        $this->assertDatabaseHas('tags', ['uuid' => $uuid]);
    }

    #[Test]
    public function search_is_case_insensitive(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'LARAVEL Mix', 'slug' => 'laravel-mix']);

        $results = $this->repository->search('laravel', 10);

        $this->assertCount(2, $results);
    }

    #[Test]
    public function find_popular_respects_limit_parameter(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag1', 'slug' => 'tag1', 'article_count' => 100]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag2', 'slug' => 'tag2', 'article_count' => 50]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag3', 'slug' => 'tag3', 'article_count' => 25]);
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Tag4', 'slug' => 'tag4', 'article_count' => 10]);

        $results = $this->repository->findPopular(2);

        $this->assertCount(2, $results);
        $this->assertEquals('Tag1', $results[0]->name());
        $this->assertEquals('Tag2', $results[1]->name());
    }

    #[Test]
    public function find_or_create_handles_korean_names(): void
    {
        $tag = $this->repository->findOrCreate('라라벨');

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('라라벨', $tag->name());
        $this->assertEquals('라라벨', $tag->slug());
        $this->assertDatabaseHas('tags', ['name' => '라라벨']);
    }

    #[Test]
    public function save_updates_existing_tag(): void
    {
        $uuid = fake()->uuid();
        TagModel::create(['uuid' => $uuid, 'name' => 'Laravel', 'slug' => 'laravel', 'article_count' => 5]);

        $tagId = new \App\Domain\Core\Tag\ValueObjects\TagId($uuid);
        $tag = $this->repository->find($tagId);

        // Increment article count
        $tag->incrementArticleCount();
        $this->repository->save($tag);

        $updatedTag = $this->repository->find($tagId);
        $this->assertEquals(6, $updatedTag->articleCount());
    }

    #[Test]
    public function find_by_names_returns_empty_array_for_no_match(): void
    {
        TagModel::create(['uuid' => fake()->uuid(), 'name' => 'Laravel', 'slug' => 'laravel']);

        $results = $this->repository->findByNames(['NonExistent', 'AlsoNotFound']);

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    #[Test]
    public function find_by_article_ids_returns_empty_for_no_tags(): void
    {
        $user = UserModel::create([
            'uuid' => fake()->uuid(),
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'username' => 'testuser2',
            'password' => 'password',
        ]);

        $article = ArticleModel::create([
            'uuid' => fake()->uuid(),
            'author_id' => $user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-no-tags',
            'content_markdown' => 'Content',
            'content_html' => '<p>Content</p>',
            'category' => 'tech',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $articleId = new \App\Domain\Core\Article\ValueObjects\ArticleId($article->uuid);
        $result = $this->repository->findByArticleIds([$articleId]);

        $this->assertArrayHasKey($article->uuid, $result);
        $this->assertCount(0, $result[$article->uuid]);
    }
}
