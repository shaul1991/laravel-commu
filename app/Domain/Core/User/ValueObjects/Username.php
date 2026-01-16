<?php

declare(strict_types=1);

namespace App\Domain\Core\User\ValueObjects;

use App\Domain\Core\Shared\ValueObject;
use App\Domain\Core\User\Exceptions\InvalidUsernameException;

final class Username extends ValueObject
{
    private const MIN_LENGTH = 3;

    private const MAX_LENGTH = 30;

    private const PATTERN = '/^[a-zA-Z0-9_]+$/';

    public function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    private function validate(string $value): void
    {
        $length = mb_strlen($value);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new InvalidUsernameException(
                'Username must be between '.self::MIN_LENGTH.' and '.self::MAX_LENGTH.' characters'
            );
        }

        if (! preg_match(self::PATTERN, $value)) {
            throw new InvalidUsernameException(
                'Username can only contain letters, numbers, and underscores'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
