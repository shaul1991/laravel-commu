<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Article\Entities;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Article\Events\ArticleLiked;
use App\Domain\Core\Article\Events\ArticlePublished;
use App\Domain\Core\Article\Exceptions\AlreadyPublishedException;
use App\Domain\Core\Article\Exceptions\ArticleNotEditableException;
use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Article\ValueObjects\ArticleStatus;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Content;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ArticleTest extends TestCase
{
    #[Test]
    public function it_creates_published_article_with_domain_event(): void
    {
        $article = Article::create(
            id: ArticleId::fromString('test-article-id'),
            authorId: UserId::fromString('author-id'),
            title: 'Test Article',
            slug: new Slug('test-article'),
            content: new Content('# Markdown', '<h1>Markdown</h1>'),
            category: Category::TECH,
            isDraft: false
        );

        $this->assertSame('test-article-id', $article->id()->value());
        $this->assertSame('author-id', $article->authorId()->value());
        $this->assertSame('Test Article', $article->title());
        $this->assertSame('test-article', $article->slug()->value());
        $this->assertTrue($article->isPublished());
        $this->assertNotNull($article->publishedAt());

        $events = $article->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticlePublished::class, $events[0]);
    }

    #[Test]
    public function it_creates_draft_article_without_event(): void
    {
        $article = Article::create(
            id: ArticleId::fromString('test-article-id'),
            authorId: UserId::fromString('author-id'),
            title: 'Draft Article',
            slug: new Slug('draft-article'),
            content: new Content('# Draft', '<h1>Draft</h1>'),
            category: Category::TECH,
            isDraft: true
        );

        $this->assertTrue($article->isDraft());
        $this->assertNull($article->publishedAt());

        $events = $article->pullDomainEvents();
        $this->assertCount(0, $events);
    }

    #[Test]
    public function it_publishes_draft_article(): void
    {
        $article = $this->createDraftArticle();

        $article->publish();

        $this->assertTrue($article->isPublished());
        $this->assertNotNull($article->publishedAt());

        $events = $article->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticlePublished::class, $events[0]);
    }

    #[Test]
    public function it_throws_exception_when_publishing_already_published_article(): void
    {
        $article = $this->createPublishedArticle();

        $this->expectException(AlreadyPublishedException::class);

        $article->publish();
    }

    #[Test]
    public function it_updates_article(): void
    {
        $article = $this->createDraftArticle();

        $article->update(
            title: 'Updated Title',
            slug: new Slug('updated-slug'),
            content: new Content('# Updated', '<h1>Updated</h1>'),
            category: Category::CAREER
        );

        $this->assertSame('Updated Title', $article->title());
        $this->assertSame('updated-slug', $article->slug()->value());
        $this->assertSame(Category::CAREER, $article->category());
    }

    #[Test]
    public function it_throws_exception_when_updating_archived_article(): void
    {
        $article = $this->createPublishedArticle();
        $article->archive();

        $this->expectException(ArticleNotEditableException::class);

        $article->update(
            title: 'New Title',
            slug: new Slug('new-slug'),
            content: new Content('# New', '<h1>New</h1>'),
            category: Category::TECH
        );
    }

    #[Test]
    public function it_increments_view_count(): void
    {
        $article = $this->createPublishedArticle();

        $this->assertSame(0, $article->viewCount());

        $article->incrementViewCount();
        $article->incrementViewCount();

        $this->assertSame(2, $article->viewCount());
    }

    #[Test]
    public function it_likes_article_with_event(): void
    {
        $article = $this->createPublishedArticle();
        $article->pullDomainEvents(); // Clear publish event

        $userId = UserId::fromString('liker-id');
        $article->like($userId);

        $this->assertSame(1, $article->likeCount());

        $events = $article->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleLiked::class, $events[0]);
    }

    #[Test]
    public function it_unlikes_article(): void
    {
        $article = $this->createPublishedArticle();
        $article->like(UserId::fromString('liker-id'));

        $article->unlike();

        $this->assertSame(0, $article->likeCount());
    }

    #[Test]
    public function it_checks_edit_permission(): void
    {
        $authorId = UserId::fromString('author-id');
        $otherId = UserId::fromString('other-id');

        $article = $this->createDraftArticle();

        $this->assertTrue($article->canBeEditedBy($authorId));
        $this->assertFalse($article->canBeEditedBy($otherId));
    }

    #[Test]
    public function it_checks_delete_permission(): void
    {
        $authorId = UserId::fromString('author-id');
        $otherId = UserId::fromString('other-id');

        $article = $this->createDraftArticle();

        $this->assertTrue($article->canBeDeletedBy($authorId));
        $this->assertFalse($article->canBeDeletedBy($otherId));
    }

    #[Test]
    public function it_archives_article(): void
    {
        $article = $this->createPublishedArticle();

        $article->archive();

        $this->assertSame(ArticleStatus::ARCHIVED, $article->status());
    }

    private function createDraftArticle(): Article
    {
        return Article::create(
            id: ArticleId::fromString('test-article-id'),
            authorId: UserId::fromString('author-id'),
            title: 'Test Article',
            slug: new Slug('test-article'),
            content: new Content('# Test', '<h1>Test</h1>'),
            category: Category::TECH,
            isDraft: true
        );
    }

    private function createPublishedArticle(): Article
    {
        return Article::create(
            id: ArticleId::fromString('test-article-id'),
            authorId: UserId::fromString('author-id'),
            title: 'Test Article',
            slug: new Slug('test-article'),
            content: new Content('# Test', '<h1>Test</h1>'),
            category: Category::TECH,
            isDraft: false
        );
    }
}
