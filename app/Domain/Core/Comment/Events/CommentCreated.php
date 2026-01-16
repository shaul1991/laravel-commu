<?php

declare(strict_types=1);

namespace App\Domain\Core\Comment\Events;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Comment\ValueObjects\CommentId;
use App\Domain\Core\Shared\DomainEvent;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class CommentCreated implements DomainEvent
{
    public function __construct(
        public readonly CommentId $commentId,
        public readonly ArticleId $articleId,
        public readonly UserId $authorId,
        private readonly DateTimeImmutable $occurredAt
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
