<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregator\SocialGraph\Services;

use App\Application\Contracts\EventDispatcherInterface;
use App\Domain\Aggregator\SocialGraph\Events\UserFollowed;
use App\Domain\Aggregator\SocialGraph\Events\UserUnfollowed;
use App\Domain\Aggregator\SocialGraph\Exceptions\CannotFollowSelfException;
use App\Domain\Aggregator\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domain\Aggregator\SocialGraph\Services\FollowService;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\ValueObjects\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class FollowServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private FollowRepositoryInterface&MockObject $followRepository;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private FollowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->followRepository = $this->createMock(FollowRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->service = new FollowService(
            $this->userRepository,
            $this->followRepository,
            $this->eventDispatcher
        );
    }

    #[Test]
    public function it_follows_user(): void
    {
        $followerId = UserId::fromString('follower-id');
        $followingId = UserId::fromString('following-id');

        $this->followRepository
            ->expects($this->once())
            ->method('exists')
            ->with($followerId, $followingId)
            ->willReturn(false);

        $this->followRepository
            ->expects($this->once())
            ->method('create')
            ->with($followerId, $followingId);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserFollowed::class));

        $this->service->follow($followerId, $followingId);
    }

    #[Test]
    public function it_does_not_follow_already_followed_user(): void
    {
        $followerId = UserId::fromString('follower-id');
        $followingId = UserId::fromString('following-id');

        $this->followRepository
            ->expects($this->once())
            ->method('exists')
            ->with($followerId, $followingId)
            ->willReturn(true);

        $this->followRepository
            ->expects($this->never())
            ->method('create');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->service->follow($followerId, $followingId);
    }

    #[Test]
    public function it_throws_exception_when_following_self(): void
    {
        $userId = UserId::fromString('user-id');

        $this->expectException(CannotFollowSelfException::class);

        $this->service->follow($userId, $userId);
    }

    #[Test]
    public function it_unfollows_user(): void
    {
        $followerId = UserId::fromString('follower-id');
        $followingId = UserId::fromString('following-id');

        $this->followRepository
            ->expects($this->once())
            ->method('exists')
            ->with($followerId, $followingId)
            ->willReturn(true);

        $this->followRepository
            ->expects($this->once())
            ->method('delete')
            ->with($followerId, $followingId);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserUnfollowed::class));

        $this->service->unfollow($followerId, $followingId);
    }

    #[Test]
    public function it_does_not_unfollow_if_not_following(): void
    {
        $followerId = UserId::fromString('follower-id');
        $followingId = UserId::fromString('following-id');

        $this->followRepository
            ->expects($this->once())
            ->method('exists')
            ->with($followerId, $followingId)
            ->willReturn(false);

        $this->followRepository
            ->expects($this->never())
            ->method('delete');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->service->unfollow($followerId, $followingId);
    }

    #[Test]
    public function it_checks_if_following(): void
    {
        $followerId = UserId::fromString('follower-id');
        $followingId = UserId::fromString('following-id');

        $this->followRepository
            ->expects($this->once())
            ->method('exists')
            ->with($followerId, $followingId)
            ->willReturn(true);

        $result = $this->service->isFollowing($followerId, $followingId);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_gets_user_stats(): void
    {
        $userId = UserId::fromString('user-id');

        $this->followRepository
            ->expects($this->once())
            ->method('countFollowers')
            ->with($userId)
            ->willReturn(100);

        $this->followRepository
            ->expects($this->once())
            ->method('countFollowing')
            ->with($userId)
            ->willReturn(50);

        $stats = $this->service->getStats($userId);

        $this->assertSame(100, $stats['followers']);
        $this->assertSame(50, $stats['following']);
    }
}
