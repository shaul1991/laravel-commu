<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Tag\Entities;

use App\Domain\Core\Tag\Entities\Tag;
use App\Domain\Core\Tag\ValueObjects\TagId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagTest extends TestCase
{
    #[Test]
    public function it_creates_tag(): void
    {
        $tag = Tag::create(
            id: TagId::fromString('tag-id'),
            name: 'Laravel'
        );

        $this->assertSame('tag-id', $tag->id()->value());
        $this->assertSame('Laravel', $tag->name());
        $this->assertSame('laravel', $tag->slug());
        $this->assertSame(0, $tag->articleCount());
    }

    #[Test]
    #[DataProvider('slugGenerationProvider')]
    public function it_generates_correct_slug(string $name, string $expectedSlug): void
    {
        $tag = Tag::create(
            id: TagId::fromString('tag-id'),
            name: $name
        );

        $this->assertSame($expectedSlug, $tag->slug());
    }

    public static function slugGenerationProvider(): array
    {
        return [
            'simple' => ['Laravel', 'laravel'],
            'with spaces' => ['React Native', 'react-native'],
            'with numbers' => ['PHP 8.4', 'php-8-4'],
            'korean' => ['프로그래밍', '프로그래밍'],
            'mixed' => ['Laravel 12 튜토리얼', 'laravel-12-튜토리얼'],
            'special characters' => ['C++', 'c'],
            'multiple spaces' => ['Web   Development', 'web-development'],
        ];
    }

    #[Test]
    public function it_increments_article_count(): void
    {
        $tag = $this->createTag();

        $tag->incrementArticleCount();
        $tag->incrementArticleCount();

        $this->assertSame(2, $tag->articleCount());
    }

    #[Test]
    public function it_decrements_article_count(): void
    {
        $tag = $this->createTag();
        $tag->incrementArticleCount();
        $tag->incrementArticleCount();

        $tag->decrementArticleCount();

        $this->assertSame(1, $tag->articleCount());
    }

    #[Test]
    public function it_does_not_go_below_zero_article_count(): void
    {
        $tag = $this->createTag();

        $tag->decrementArticleCount();
        $tag->decrementArticleCount();

        $this->assertSame(0, $tag->articleCount());
    }

    private function createTag(): Tag
    {
        return Tag::create(
            id: TagId::fromString('tag-id'),
            name: 'Test Tag'
        );
    }
}
