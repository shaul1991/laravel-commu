<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\Events;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Shared\DomainEvent;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class ArticleLiked implements DomainEvent
{
    public function __construct(
        public readonly ArticleId $articleId,
        public readonly UserId $userId,
        private readonly DateTimeImmutable $occurredAt
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
