<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\SocialGraph\Exceptions;

use DomainException;

final class CannotFollowSelfException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot follow yourself');
    }
}
