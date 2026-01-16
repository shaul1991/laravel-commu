<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\SocialGraph\DTOs;

use App\Domain\Core\User\Entities\User;

final class SocialGraphDTO
{
    /**
     * @param  User[]  $users
     */
    public function __construct(
        public readonly array $users,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage
    ) {}

    public function hasMore(): bool
    {
        return $this->total > ($this->page * $this->perPage);
    }

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function isEmpty(): bool
    {
        return count($this->users) === 0;
    }
}
