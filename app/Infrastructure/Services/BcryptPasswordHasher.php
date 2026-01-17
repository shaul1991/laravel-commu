<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Core\User\Services\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

final class BcryptPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }
}
