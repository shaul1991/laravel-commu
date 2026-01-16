<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\Exceptions;

use DomainException;

final class ArticleNotEditableException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Article cannot be edited in its current state');
    }
}
