<?php

declare(strict_types=1);

namespace App\Domain\Core\Shared;

abstract class ValueObject
{
    abstract public function value(): mixed;

    public function equals(ValueObject $other): bool
    {
        return $this->value() === $other->value();
    }
}
