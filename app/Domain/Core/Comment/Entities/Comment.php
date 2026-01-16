<?php

declare(strict_types=1);

namespace App\Domain\Core\Comment\Entities;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Comment\Events\CommentCreated;
use App\Domain\Core\Comment\ValueObjects\CommentId;
use App\Domain\Core\Shared\AggregateRoot;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

class Comment extends AggregateRoot
{
    private function __construct(
        private readonly CommentId $id,
        private readonly ArticleId $articleId,
        private readonly UserId $authorId,
        private readonly ?CommentId $parentId,
        private string $content,
        private int $likeCount,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        CommentId $id,
        ArticleId $articleId,
        UserId $authorId,
        string $content,
        ?CommentId $parentId = null
    ): self {
        $now = new DateTimeImmutable;

        $comment = new self(
            id: $id,
            articleId: $articleId,
            authorId: $authorId,
            parentId: $parentId,
            content: $content,
            likeCount: 0,
            createdAt: $now,
            updatedAt: $now
        );

        $comment->recordEvent(new CommentCreated($id, $articleId, $authorId, $now));

        return $comment;
    }

    public function update(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function like(): void
    {
        $this->likeCount++;
    }

    public function unlike(): void
    {
        if ($this->likeCount > 0) {
            $this->likeCount--;
        }
    }

    public function canBeEditedBy(UserId $userId): bool
    {
        return $this->authorId->equals($userId);
    }

    public function canBeDeletedBy(UserId $userId): bool
    {
        return $this->authorId->equals($userId);
    }

    public function isReply(): bool
    {
        return $this->parentId !== null;
    }

    // Getters
    public function id(): CommentId
    {
        return $this->id;
    }

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }

    public function authorId(): UserId
    {
        return $this->authorId;
    }

    public function parentId(): ?CommentId
    {
        return $this->parentId;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function likeCount(): int
    {
        return $this->likeCount;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
