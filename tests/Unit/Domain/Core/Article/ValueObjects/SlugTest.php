<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Core\Article\ValueObjects;

use App\Domain\Core\Article\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SlugTest extends TestCase
{
    #[Test]
    public function it_creates_slug_directly(): void
    {
        $slug = new Slug('my-article-title');

        $this->assertSame('my-article-title', $slug->value());
    }

    #[Test]
    #[DataProvider('titleToSlugProvider')]
    public function it_generates_slug_from_title(string $title, string $expectedSlug): void
    {
        $slug = Slug::fromTitle($title);

        $this->assertSame($expectedSlug, $slug->value());
    }

    public static function titleToSlugProvider(): array
    {
        return [
            'simple english' => ['My Article Title', 'my-article-title'],
            'with numbers' => ['10 Tips for Laravel', '10-tips-for-laravel'],
            'korean' => ['Laravel 12 새로운 기능', 'laravel-12-새로운-기능'],
            'special characters' => ['Hello! How are you?', 'hello-how-are-you'],
            'multiple spaces' => ['Multiple   Spaces  Here', 'multiple-spaces-here'],
            'leading trailing spaces' => ['  Trimmed Title  ', 'trimmed-title'],
        ];
    }

    #[Test]
    public function it_adds_timestamp_suffix(): void
    {
        $slug = new Slug('my-article');
        $slugWithTimestamp = $slug->withTimestamp();

        $this->assertStringStartsWith('my-article-', $slugWithTimestamp->value());
        $this->assertMatchesRegularExpression('/my-article-\d+/', $slugWithTimestamp->value());
    }

    #[Test]
    public function it_compares_slugs(): void
    {
        $slug1 = new Slug('my-article');
        $slug2 = new Slug('my-article');
        $slug3 = new Slug('other-article');

        $this->assertTrue($slug1->equals($slug2));
        $this->assertFalse($slug1->equals($slug3));
    }
}
