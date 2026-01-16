<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Events;

use App\Domain\Core\Shared\DomainEvent;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class UserRegistered implements DomainEvent
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Email $email,
        private readonly DateTimeImmutable $occurredAt
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
