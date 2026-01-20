<?php

declare(strict_types=1);

namespace App\Application\Article;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Content;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Infrastructure\Services\MarkdownParserInterface;

final class CreateArticleUseCase
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly MarkdownParserInterface $markdownParser
    ) {}

    public function execute(CreateArticleInput $input): Article
    {
        $articleId = ArticleId::generate();
        $authorId = UserId::fromString($input->authorId);

        // Parse markdown to HTML
        $html = $this->markdownParser->parse($input->content);
        $content = new Content($input->content, $html);

        // Generate unique slug
        $slug = $this->generateUniqueSlug($input->title);

        $category = $input->category !== null
            ? Category::from($input->category)
            : Category::default();

        $article = Article::create(
            id: $articleId,
            authorId: $authorId,
            title: $input->title,
            slug: $slug,
            content: $content,
            category: $category,
            isDraft: $input->isDraft
        );

        $this->articleRepository->save($article);

        return $article;
    }

    private function generateUniqueSlug(string $title): Slug
    {
        $baseSlug = Slug::fromTitle($title);

        if (! $this->articleRepository->existsBySlug($baseSlug)) {
            return $baseSlug->withTimestamp();
        }

        return $baseSlug->withTimestamp();
    }
}
