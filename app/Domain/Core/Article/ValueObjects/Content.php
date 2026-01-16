<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\ValueObjects;

use App\Domain\Core\Shared\ValueObject;

final class Content extends ValueObject
{
    public function __construct(
        private readonly string $markdown,
        private readonly string $html
    ) {}

    public function markdown(): string
    {
        return $this->markdown;
    }

    public function html(): string
    {
        return $this->html;
    }

    public function value(): string
    {
        return $this->markdown;
    }

    public function excerpt(int $length = 200): string
    {
        $text = strip_tags($this->html);
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'...';
    }
}
