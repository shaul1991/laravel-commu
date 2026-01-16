<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Notification\Entities;

use App\Domain\Core\Notification\Entities\Notification;
use App\Domain\Core\Notification\ValueObjects\NotificationId;
use App\Domain\Core\Notification\ValueObjects\NotificationType;
use App\Domain\Core\User\ValueObjects\UserId;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class NotificationTest extends TestCase
{
    #[Test]
    public function it_creates_notification(): void
    {
        $notification = Notification::create(
            id: NotificationId::fromString('notification-id'),
            userId: UserId::fromString('user-id'),
            type: NotificationType::NEW_COMMENT,
            data: ['article_id' => 'article-123', 'comment_id' => 'comment-456']
        );

        $this->assertSame('notification-id', $notification->id()->value());
        $this->assertSame('user-id', $notification->userId()->value());
        $this->assertSame(NotificationType::NEW_COMMENT, $notification->type());
        $this->assertSame(['article_id' => 'article-123', 'comment_id' => 'comment-456'], $notification->data());
        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->readAt());
    }

    #[Test]
    public function it_marks_notification_as_read(): void
    {
        $notification = $this->createNotification();

        $this->assertFalse($notification->isRead());

        $notification->markAsRead();

        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->readAt());
    }

    #[Test]
    public function it_does_not_update_read_at_when_already_read(): void
    {
        $notification = $this->createNotification();
        $notification->markAsRead();

        $firstReadAt = $notification->readAt();

        usleep(1000); // Ensure time difference
        $notification->markAsRead();

        $this->assertSame($firstReadAt, $notification->readAt());
    }

    #[Test]
    public function it_creates_different_notification_types(): void
    {
        $types = [
            NotificationType::NEW_ARTICLE,
            NotificationType::NEW_COMMENT,
            NotificationType::COMMENT_REPLY,
            NotificationType::ARTICLE_LIKED,
            NotificationType::NEW_FOLLOWER,
            NotificationType::MENTION,
        ];

        foreach ($types as $type) {
            $notification = Notification::create(
                id: NotificationId::generate(),
                userId: UserId::fromString('user-id'),
                type: $type,
                data: []
            );

            $this->assertSame($type, $notification->type());
        }
    }

    private function createNotification(): Notification
    {
        return Notification::create(
            id: NotificationId::fromString('notification-id'),
            userId: UserId::fromString('user-id'),
            type: NotificationType::NEW_COMMENT,
            data: ['test' => 'data']
        );
    }
}
