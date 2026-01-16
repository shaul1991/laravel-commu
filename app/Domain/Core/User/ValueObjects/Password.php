<?php

declare(strict_types=1);

namespace App\Domain\Core\User\ValueObjects;

use App\Domain\Core\Shared\ValueObject;
use App\Domain\Core\User\Exceptions\WeakPasswordException;
use App\Domain\Core\User\Services\PasswordHasherInterface;

final class Password extends ValueObject
{
    private const MIN_LENGTH = 8;

    private function __construct(
        private readonly string $hashedValue
    ) {}

    public static function fromHash(string $hashedValue): self
    {
        return new self($hashedValue);
    }

    public static function fromPlainText(string $plainText, PasswordHasherInterface $hasher): self
    {
        self::validateStrength($plainText);

        return new self($hasher->hash($plainText));
    }

    private static function validateStrength(string $password): void
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw new WeakPasswordException('Password must be at least '.self::MIN_LENGTH.' characters');
        }

        if (! preg_match('/[A-Za-z]/', $password)) {
            throw new WeakPasswordException('Password must contain at least one letter');
        }

        if (! preg_match('/[0-9]/', $password)) {
            throw new WeakPasswordException('Password must contain at least one number');
        }

        if (! preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            throw new WeakPasswordException('Password must contain at least one special character');
        }
    }

    public function verify(string $plainText, PasswordHasherInterface $hasher): bool
    {
        return $hasher->verify($plainText, $this->hashedValue);
    }

    public function value(): string
    {
        return $this->hashedValue;
    }
}
