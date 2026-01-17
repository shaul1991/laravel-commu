<?php

declare(strict_types=1);

namespace App\Application\Auth;

final readonly class LoginUserInput
{
    public function __construct(
        public string $emailOrUsername,
        public string $password
    ) {}
}
