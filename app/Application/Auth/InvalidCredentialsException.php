<?php

declare(strict_types=1);

namespace App\Application\Auth;

use Exception;

final class InvalidCredentialsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid credentials');
    }
}
