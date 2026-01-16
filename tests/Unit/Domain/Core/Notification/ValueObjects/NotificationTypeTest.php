<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Notification\ValueObjects;

use App\Domain\Core\Notification\ValueObjects\NotificationType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class NotificationTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('notificationTypeLabelProvider')]
    public function it_returns_correct_label(NotificationType $type, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, $type->label());
    }

    public static function notificationTypeLabelProvider(): array
    {
        return [
            'new article' => [NotificationType::NEW_ARTICLE, '새 아티클'],
            'new comment' => [NotificationType::NEW_COMMENT, '새 댓글'],
            'comment reply' => [NotificationType::COMMENT_REPLY, '댓글 답글'],
            'article liked' => [NotificationType::ARTICLE_LIKED, '좋아요'],
            'new follower' => [NotificationType::NEW_FOLLOWER, '새 팔로워'],
            'mention' => [NotificationType::MENTION, '멘션'],
        ];
    }

    #[Test]
    #[DataProvider('notificationTypeIconProvider')]
    public function it_returns_correct_icon(NotificationType $type, string $expectedIcon): void
    {
        $this->assertSame($expectedIcon, $type->icon());
    }

    public static function notificationTypeIconProvider(): array
    {
        return [
            'new article' => [NotificationType::NEW_ARTICLE, 'document'],
            'new comment' => [NotificationType::NEW_COMMENT, 'chat'],
            'comment reply' => [NotificationType::COMMENT_REPLY, 'reply'],
            'article liked' => [NotificationType::ARTICLE_LIKED, 'heart'],
            'new follower' => [NotificationType::NEW_FOLLOWER, 'user-plus'],
            'mention' => [NotificationType::MENTION, 'at'],
        ];
    }

    #[Test]
    public function it_creates_from_string_value(): void
    {
        $type = NotificationType::from('new_comment');

        $this->assertSame(NotificationType::NEW_COMMENT, $type);
    }

    #[Test]
    public function it_returns_string_value(): void
    {
        $type = NotificationType::NEW_FOLLOWER;

        $this->assertSame('new_follower', $type->value);
    }
}
