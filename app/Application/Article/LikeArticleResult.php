<?php

declare(strict_types=1);

namespace App\Application\Article;

final readonly class LikeArticleResult
{
    public function __construct(
        public bool $liked,
        public int $likeCount
    ) {}
}
