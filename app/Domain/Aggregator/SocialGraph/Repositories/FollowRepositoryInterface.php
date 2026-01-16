<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\SocialGraph\Repositories;

use App\Domain\Core\User\ValueObjects\UserId;

interface FollowRepositoryInterface
{
    public function create(UserId $followerId, UserId $followingId): void;

    public function delete(UserId $followerId, UserId $followingId): void;

    public function exists(UserId $followerId, UserId $followingId): bool;

    /**
     * @return UserId[]
     */
    public function getFollowerIds(UserId $userId, int $limit = 10, int $offset = 0): array;

    /**
     * @return UserId[]
     */
    public function getFollowingIds(UserId $userId, int $limit = 10, int $offset = 0): array;

    public function countFollowers(UserId $userId): int;

    public function countFollowing(UserId $userId): int;

    /**
     * @param  UserId[]  $userIds
     * @return array<string, bool>
     */
    public function checkFollowing(UserId $followerId, array $userIds): array;
}
