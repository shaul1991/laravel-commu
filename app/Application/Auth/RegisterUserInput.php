<?php

declare(strict_types=1);

namespace App\Application\Auth;

final readonly class RegisterUserInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $username,
        public string $password
    ) {}
}
