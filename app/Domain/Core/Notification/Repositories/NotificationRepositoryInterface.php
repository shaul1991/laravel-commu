<?php

declare(strict_types=1);

namespace App\Domain\Core\Notification\Repositories;

use App\Domain\Core\Notification\Entities\Notification;
use App\Domain\Core\Notification\ValueObjects\NotificationId;
use App\Domain\Core\User\ValueObjects\UserId;

interface NotificationRepositoryInterface
{
    public function find(NotificationId $id): ?Notification;

    /**
     * @return Notification[]
     */
    public function findByUser(UserId $userId, int $limit = 20, int $offset = 0): array;

    /**
     * @return Notification[]
     */
    public function findUnreadByUser(UserId $userId, int $limit = 20): array;

    public function save(Notification $notification): void;

    public function delete(Notification $notification): void;

    public function markAllAsRead(UserId $userId): void;

    public function countUnread(UserId $userId): int;

    public function deleteOlderThan(\DateTimeImmutable $date): int;
}
