<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\ValueObjects;

enum Category: string
{
    case TECH = 'tech';
    case CAREER = 'career';
    case LIFE = 'life';
    case NEWS = 'news';

    public function label(): string
    {
        return match ($this) {
            self::TECH => '기술',
            self::CAREER => '커리어',
            self::LIFE => '일상',
            self::NEWS => '뉴스',
        };
    }
}
