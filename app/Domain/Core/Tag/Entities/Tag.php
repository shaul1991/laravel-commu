<?php

declare(strict_types=1);

namespace App\Domain\Core\Tag\Entities;

use App\Domain\Core\Tag\ValueObjects\TagId;
use DateTimeImmutable;

class Tag
{
    private function __construct(
        private readonly TagId $id,
        private string $name,
        private string $slug,
        private int $articleCount,
        private readonly DateTimeImmutable $createdAt
    ) {}

    public static function create(TagId $id, string $name): self
    {
        $slug = self::generateSlug($name);

        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            articleCount: 0,
            createdAt: new DateTimeImmutable
        );
    }

    public static function reconstitute(
        TagId $id,
        string $name,
        string $slug,
        int $articleCount,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            articleCount: $articleCount,
            createdAt: $createdAt
        );
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9가-힣]+/u', '-', $slug);

        return trim($slug, '-');
    }

    public function incrementArticleCount(): void
    {
        $this->articleCount++;
    }

    public function decrementArticleCount(): void
    {
        if ($this->articleCount > 0) {
            $this->articleCount--;
        }
    }

    // Getters
    public function id(): TagId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function articleCount(): int
    {
        return $this->articleCount;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
