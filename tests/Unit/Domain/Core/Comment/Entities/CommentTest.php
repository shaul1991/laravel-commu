<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Comment\Entities;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Comment\Entities\Comment;
use App\Domain\Core\Comment\Events\CommentCreated;
use App\Domain\Core\Comment\ValueObjects\CommentId;
use App\Domain\Core\User\ValueObjects\UserId;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CommentTest extends TestCase
{
    #[Test]
    public function it_creates_comment_with_domain_event(): void
    {
        $comment = Comment::create(
            id: CommentId::fromString('comment-id'),
            articleId: ArticleId::fromString('article-id'),
            authorId: UserId::fromString('author-id'),
            content: 'This is a comment'
        );

        $this->assertSame('comment-id', $comment->id()->value());
        $this->assertSame('article-id', $comment->articleId()->value());
        $this->assertSame('author-id', $comment->authorId()->value());
        $this->assertSame('This is a comment', $comment->content());
        $this->assertSame(0, $comment->likeCount());
        $this->assertFalse($comment->isReply());
        $this->assertNull($comment->parentId());

        $events = $comment->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CommentCreated::class, $events[0]);
    }

    #[Test]
    public function it_creates_reply_comment(): void
    {
        $parentId = CommentId::fromString('parent-comment-id');

        $reply = Comment::create(
            id: CommentId::fromString('reply-id'),
            articleId: ArticleId::fromString('article-id'),
            authorId: UserId::fromString('author-id'),
            content: 'This is a reply',
            parentId: $parentId
        );

        $this->assertTrue($reply->isReply());
        $this->assertNotNull($reply->parentId());
        $this->assertSame('parent-comment-id', $reply->parentId()->value());
    }

    #[Test]
    public function it_updates_comment_content(): void
    {
        $comment = $this->createComment();
        $originalUpdatedAt = $comment->updatedAt();

        usleep(1000); // Ensure time difference
        $comment->update('Updated content');

        $this->assertSame('Updated content', $comment->content());
        $this->assertGreaterThanOrEqual($originalUpdatedAt, $comment->updatedAt());
    }

    #[Test]
    public function it_likes_comment(): void
    {
        $comment = $this->createComment();

        $this->assertSame(0, $comment->likeCount());

        $comment->like();
        $comment->like();

        $this->assertSame(2, $comment->likeCount());
    }

    #[Test]
    public function it_unlikes_comment(): void
    {
        $comment = $this->createComment();
        $comment->like();
        $comment->like();

        $comment->unlike();

        $this->assertSame(1, $comment->likeCount());
    }

    #[Test]
    public function it_does_not_go_below_zero_likes(): void
    {
        $comment = $this->createComment();

        $comment->unlike();
        $comment->unlike();

        $this->assertSame(0, $comment->likeCount());
    }

    #[Test]
    public function it_checks_edit_permission(): void
    {
        $authorId = UserId::fromString('author-id');
        $otherId = UserId::fromString('other-id');

        $comment = $this->createComment();

        $this->assertTrue($comment->canBeEditedBy($authorId));
        $this->assertFalse($comment->canBeEditedBy($otherId));
    }

    #[Test]
    public function it_checks_delete_permission(): void
    {
        $authorId = UserId::fromString('author-id');
        $otherId = UserId::fromString('other-id');

        $comment = $this->createComment();

        $this->assertTrue($comment->canBeDeletedBy($authorId));
        $this->assertFalse($comment->canBeDeletedBy($otherId));
    }

    private function createComment(): Comment
    {
        return Comment::create(
            id: CommentId::fromString('comment-id'),
            articleId: ArticleId::fromString('article-id'),
            authorId: UserId::fromString('author-id'),
            content: 'Test comment'
        );
    }
}
