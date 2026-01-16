<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Exceptions;

use DomainException;

final class WeakPasswordException extends DomainException {}
