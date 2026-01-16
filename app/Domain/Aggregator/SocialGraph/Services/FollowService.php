<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\SocialGraph\Services;

use App\Application\Contracts\EventDispatcherInterface;
use App\Domain\Aggregator\SocialGraph\DTOs\SocialGraphDTO;
use App\Domain\Aggregator\SocialGraph\Events\UserFollowed;
use App\Domain\Aggregator\SocialGraph\Events\UserUnfollowed;
use App\Domain\Aggregator\SocialGraph\Exceptions\CannotFollowSelfException;
use App\Domain\Aggregator\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

class FollowService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly FollowRepositoryInterface $followRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function follow(UserId $followerId, UserId $followingId): void
    {
        if ($followerId->equals($followingId)) {
            throw new CannotFollowSelfException;
        }

        // Atomic upsert to avoid TOCTOU race condition
        $created = $this->followRepository->createIfNotExists($followerId, $followingId);

        if ($created) {
            $this->eventDispatcher->dispatch(
                new UserFollowed($followerId, $followingId, new DateTimeImmutable)
            );
        }
    }

    public function unfollow(UserId $followerId, UserId $followingId): void
    {
        if (! $this->followRepository->exists($followerId, $followingId)) {
            return;
        }

        $this->followRepository->delete($followerId, $followingId);

        $this->eventDispatcher->dispatch(
            new UserUnfollowed($followerId, $followingId, new DateTimeImmutable)
        );
    }

    public function isFollowing(UserId $followerId, UserId $followingId): bool
    {
        return $this->followRepository->exists($followerId, $followingId);
    }

    public function getFollowers(UserId $userId, int $page = 1, int $perPage = 10): SocialGraphDTO
    {
        $offset = ($page - 1) * $perPage;
        $followerIds = $this->followRepository->getFollowerIds($userId, $perPage, $offset);
        $total = $this->followRepository->countFollowers($userId);

        $users = $this->userRepository->findByIds($followerIds);

        return new SocialGraphDTO(
            users: $users,
            total: $total,
            page: $page,
            perPage: $perPage
        );
    }

    public function getFollowing(UserId $userId, int $page = 1, int $perPage = 10): SocialGraphDTO
    {
        $offset = ($page - 1) * $perPage;
        $followingIds = $this->followRepository->getFollowingIds($userId, $perPage, $offset);
        $total = $this->followRepository->countFollowing($userId);

        $users = $this->userRepository->findByIds($followingIds);

        return new SocialGraphDTO(
            users: $users,
            total: $total,
            page: $page,
            perPage: $perPage
        );
    }

    public function getStats(UserId $userId): array
    {
        return [
            'followers' => $this->followRepository->countFollowers($userId),
            'following' => $this->followRepository->countFollowing($userId),
        ];
    }
}
