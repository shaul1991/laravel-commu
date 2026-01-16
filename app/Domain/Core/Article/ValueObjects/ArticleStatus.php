<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\ValueObjects;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function isEditable(): bool
    {
        return $this !== self::ARCHIVED;
    }

    public function isVisible(): bool
    {
        return $this === self::PUBLISHED;
    }
}
