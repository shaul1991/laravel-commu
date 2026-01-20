<?php

declare(strict_types=1);

namespace App\Application\Article;

final readonly class CreateArticleInput
{
    public function __construct(
        public string $authorId,
        public string $title,
        public string $content,
        public ?string $category = null,
        public bool $isDraft = false
    ) {}
}
