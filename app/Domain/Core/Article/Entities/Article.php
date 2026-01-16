<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\Entities;

use App\Domain\Core\Article\Events\ArticleLiked;
use App\Domain\Core\Article\Events\ArticlePublished;
use App\Domain\Core\Article\Exceptions\AlreadyPublishedException;
use App\Domain\Core\Article\Exceptions\ArticleNotEditableException;
use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Article\ValueObjects\ArticleStatus;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Content;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\Shared\AggregateRoot;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

class Article extends AggregateRoot
{
    private function __construct(
        private readonly ArticleId $id,
        private readonly UserId $authorId,
        private string $title,
        private Slug $slug,
        private Content $content,
        private Category $category,
        private ArticleStatus $status,
        private int $viewCount,
        private int $likeCount,
        private ?DateTimeImmutable $publishedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        ArticleId $id,
        UserId $authorId,
        string $title,
        Slug $slug,
        Content $content,
        Category $category,
        bool $isDraft = false
    ): self {
        $now = new DateTimeImmutable;

        $article = new self(
            id: $id,
            authorId: $authorId,
            title: $title,
            slug: $slug,
            content: $content,
            category: $category,
            status: $isDraft ? ArticleStatus::DRAFT : ArticleStatus::PUBLISHED,
            viewCount: 0,
            likeCount: 0,
            publishedAt: $isDraft ? null : $now,
            createdAt: $now,
            updatedAt: $now
        );

        if (! $isDraft) {
            $article->recordEvent(new ArticlePublished($id, $authorId, $now));
        }

        return $article;
    }

    public function publish(): void
    {
        if ($this->status === ArticleStatus::PUBLISHED) {
            throw new AlreadyPublishedException;
        }

        $now = new DateTimeImmutable;
        $this->status = ArticleStatus::PUBLISHED;
        $this->publishedAt = $now;
        $this->updatedAt = $now;

        $this->recordEvent(new ArticlePublished($this->id, $this->authorId, $now));
    }

    public function update(string $title, Slug $slug, Content $content, Category $category): void
    {
        if (! $this->status->isEditable()) {
            throw new ArticleNotEditableException;
        }

        $this->title = $title;
        $this->slug = $slug;
        $this->content = $content;
        $this->category = $category;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function archive(): void
    {
        $this->status = ArticleStatus::ARCHIVED;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    public function like(UserId $userId): void
    {
        $this->likeCount++;
        $this->recordEvent(new ArticleLiked($this->id, $userId, new DateTimeImmutable));
    }

    public function unlike(): void
    {
        if ($this->likeCount > 0) {
            $this->likeCount--;
        }
    }

    public function canBeEditedBy(UserId $userId): bool
    {
        return $this->authorId->equals($userId) && $this->status->isEditable();
    }

    public function canBeDeletedBy(UserId $userId): bool
    {
        return $this->authorId->equals($userId);
    }

    // Getters
    public function id(): ArticleId
    {
        return $this->id;
    }

    public function authorId(): UserId
    {
        return $this->authorId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }

    public function content(): Content
    {
        return $this->content;
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function status(): ArticleStatus
    {
        return $this->status;
    }

    public function viewCount(): int
    {
        return $this->viewCount;
    }

    public function likeCount(): int
    {
        return $this->likeCount;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isDraft(): bool
    {
        return $this->status === ArticleStatus::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED;
    }
}
