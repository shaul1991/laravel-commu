<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\SocialGraph\Events;

use App\Domain\Core\Shared\DomainEvent;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class UserUnfollowed implements DomainEvent
{
    public function __construct(
        public readonly UserId $followerId,
        public readonly UserId $followingId,
        private readonly DateTimeImmutable $occurredAt
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
