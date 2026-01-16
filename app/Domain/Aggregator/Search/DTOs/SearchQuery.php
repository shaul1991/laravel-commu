<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\Search\DTOs;

final class SearchQuery
{
    /**
     * @param  string[]  $types
     */
    public function __construct(
        public readonly string $keyword,
        public readonly array $types = ['articles', 'users', 'tags'],
        public readonly int $limit = 10,
        public readonly ?string $period = null
    ) {}

    public function includesType(string $type): bool
    {
        return in_array($type, $this->types, true);
    }

    public function onlyArticles(): bool
    {
        return $this->types === ['articles'];
    }

    public function onlyUsers(): bool
    {
        return $this->types === ['users'];
    }

    public function onlyTags(): bool
    {
        return $this->types === ['tags'];
    }
}
