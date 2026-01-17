<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Core\User\Entities\User;

final readonly class LoginResult
{
    public function __construct(
        public User $user,
        public string $token
    ) {}
}
