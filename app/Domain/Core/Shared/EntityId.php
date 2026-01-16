<?php

declare(strict_types=1);

namespace App\Domain\Core\Shared;

use Ramsey\Uuid\Uuid;

abstract class EntityId extends ValueObject
{
    public function __construct(
        protected readonly string $value
    ) {}

    public static function generate(): static
    {
        return new static(Uuid::uuid7()->toString());
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
