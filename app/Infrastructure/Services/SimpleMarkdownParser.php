<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use Illuminate\Support\Str;

final class SimpleMarkdownParser implements MarkdownParserInterface
{
    public function parse(string $markdown): string
    {
        // Convert Markdown to HTML using Laravel's Str::markdown
        // This uses league/commonmark under the hood
        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
