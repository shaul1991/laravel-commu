<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\ValueObjects;

use App\Domain\Core\Shared\ValueObject;

final class Slug extends ValueObject
{
    public function __construct(
        private readonly string $value
    ) {}

    public static function fromTitle(string $title): self
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9가-힣]+/u', '-', $slug);
        $slug = trim($slug, '-');

        return new self($slug);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function withTimestamp(): self
    {
        return new self($this->value.'-'.time());
    }
}
