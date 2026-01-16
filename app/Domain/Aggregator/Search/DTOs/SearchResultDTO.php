<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\Search\DTOs;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Tag\Entities\Tag;
use App\Domain\Core\User\Entities\User;

final class SearchResultDTO
{
    /**
     * @param  Article[]  $articles
     * @param  User[]  $users
     * @param  Tag[]  $tags
     */
    public function __construct(
        public readonly array $articles,
        public readonly array $users,
        public readonly array $tags,
        public readonly string $keyword,
        public readonly int $totalArticles,
        public readonly int $totalUsers,
        public readonly int $totalTags
    ) {}

    public function totalResults(): int
    {
        return $this->totalArticles + $this->totalUsers + $this->totalTags;
    }

    public function isEmpty(): bool
    {
        return $this->totalResults() === 0;
    }
}
