<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Exceptions;

use DomainException;

final class InvalidEmailException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid email format');
    }
}
