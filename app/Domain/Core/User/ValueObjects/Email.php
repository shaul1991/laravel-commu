<?php

declare(strict_types=1);

namespace App\Domain\Core\User\ValueObjects;

use App\Domain\Core\Shared\ValueObject;
use App\Domain\Core\User\Exceptions\InvalidEmailException;

final class Email extends ValueObject
{
    public function __construct(
        private readonly string $value
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException;
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }
}
