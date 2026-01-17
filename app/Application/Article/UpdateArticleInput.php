<?php

declare(strict_types=1);

namespace App\Application\Article;

final readonly class UpdateArticleInput
{
    public function __construct(
        public string $userId,
        public string $slug,
        public string $title,
        public string $content,
        public string $category,
        public bool $isDraft = false
    ) {}
}
