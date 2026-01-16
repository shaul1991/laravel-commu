<?php

declare(strict_types=1);

namespace App\Domain\Core\Notification\ValueObjects;

enum NotificationType: string
{
    case NEW_ARTICLE = 'new_article';
    case NEW_COMMENT = 'new_comment';
    case COMMENT_REPLY = 'comment_reply';
    case ARTICLE_LIKED = 'article_liked';
    case NEW_FOLLOWER = 'new_follower';
    case MENTION = 'mention';

    public function label(): string
    {
        return match ($this) {
            self::NEW_ARTICLE => '새 아티클',
            self::NEW_COMMENT => '새 댓글',
            self::COMMENT_REPLY => '댓글 답글',
            self::ARTICLE_LIKED => '좋아요',
            self::NEW_FOLLOWER => '새 팔로워',
            self::MENTION => '멘션',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW_ARTICLE => 'document',
            self::NEW_COMMENT => 'chat',
            self::COMMENT_REPLY => 'reply',
            self::ARTICLE_LIKED => 'heart',
            self::NEW_FOLLOWER => 'user-plus',
            self::MENTION => 'at',
        };
    }
}
