<?php

declare(strict_types=1);

namespace App\Application\Article;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Article\Exceptions\ArticleNotEditableException;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Content;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Infrastructure\Services\MarkdownParserInterface;

final class UpdateArticleUseCase
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly MarkdownParserInterface $markdownParser
    ) {}

    public function execute(UpdateArticleInput $input): Article
    {
        $article = $this->articleRepository->findBySlug(new Slug($input->slug));

        if ($article === null) {
            throw new \RuntimeException('Article not found');
        }

        $userId = UserId::fromString($input->userId);

        if (! $article->canBeEditedBy($userId)) {
            throw new ArticleNotEditableException;
        }

        // Parse markdown to HTML
        $html = $this->markdownParser->parse($input->content);
        $content = new Content($input->content, $html);

        // Generate new slug if title changed
        $newSlug = Slug::fromTitle($input->title)->withTimestamp();

        $category = Category::from($input->category);

        $article->update(
            title: $input->title,
            slug: $newSlug,
            content: $content,
            category: $category
        );

        // Publish if transitioning from draft
        if ($article->isDraft() && ! $input->isDraft) {
            $article->publish();
        }

        $this->articleRepository->save($article);

        return $article;
    }
}
