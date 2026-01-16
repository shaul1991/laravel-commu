<?php

declare(strict_types=1);

namespace App\Domain\Core\Notification\Entities;

use App\Domain\Core\Notification\ValueObjects\NotificationId;
use App\Domain\Core\Notification\ValueObjects\NotificationType;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

class Notification
{
    private function __construct(
        private readonly NotificationId $id,
        private readonly UserId $userId,
        private readonly NotificationType $type,
        private readonly array $data,
        private ?DateTimeImmutable $readAt,
        private readonly DateTimeImmutable $createdAt
    ) {}

    public static function create(
        NotificationId $id,
        UserId $userId,
        NotificationType $type,
        array $data
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            type: $type,
            data: $data,
            readAt: null,
            createdAt: new DateTimeImmutable
        );
    }

    public function markAsRead(): void
    {
        if ($this->readAt === null) {
            $this->readAt = new DateTimeImmutable;
        }
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    // Getters
    public function id(): NotificationId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function type(): NotificationType
    {
        return $this->type;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function readAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
