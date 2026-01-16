<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Events;

use App\Domain\Core\Shared\DomainEvent;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class UserUpdated implements DomainEvent
{
    public function __construct(
        public readonly UserId $userId,
        private readonly DateTimeImmutable $occurredAt
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
