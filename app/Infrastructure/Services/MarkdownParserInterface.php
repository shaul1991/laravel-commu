<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

interface MarkdownParserInterface
{
    public function parse(string $markdown): string;
}
