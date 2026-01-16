<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\ArticleFeed\DTOs;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Tag\Entities\Tag;
use App\Domain\Core\User\Entities\User;

final class ArticleFeedDTO
{
    /**
     * @param  Article[]  $articles
     * @param  array<string, User>  $authors
     * @param  array<string, Tag[]>  $tags
     */
    public function __construct(
        public readonly array $articles,
        public readonly array $authors,
        public readonly array $tags,
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
}
