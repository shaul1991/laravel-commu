<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Exceptions;

use DomainException;

final class InvalidEmailException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("Invalid email format: {$email}");
    }
}
