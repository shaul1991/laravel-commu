<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\Exceptions;

use DomainException;

final class AlreadyPublishedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Article is already published');
    }
}
