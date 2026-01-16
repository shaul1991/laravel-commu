<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\ArticleFeed\DTOs;

use App\Domain\Core\Article\ValueObjects\Category;

final class FeedCriteria
{
    public function __construct(
        public readonly ?Category $category = null,
        public readonly ?string $tagSlug = null,
        public readonly string $sortBy = 'published_at',
        public readonly string $sortOrder = 'desc',
        public readonly int $page = 1,
        public readonly int $perPage = 10
    ) {}

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hash(): string
    {
        return md5(serialize([
            $this->category?->value,
            $this->tagSlug,
            $this->sortBy,
            $this->sortOrder,
            $this->page,
            $this->perPage,
        ]));
    }
}
