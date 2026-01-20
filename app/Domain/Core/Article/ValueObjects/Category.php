<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\ValueObjects;

enum Category: string
{
    case GENERAL = 'general';
    case TECH = 'tech';
    case CAREER = 'career';
    case LIFE = 'life';
    case NEWS = 'news';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => '일반',
            self::TECH => '기술',
            self::CAREER => '커리어',
            self::LIFE => '일상',
            self::NEWS => '뉴스',
        };
    }

    public static function default(): self
    {
        return self::GENERAL;
    }
}
