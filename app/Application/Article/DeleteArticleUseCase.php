<?php

declare(strict_types=1);

namespace App\Application\Article;

use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\NotFoundException;

final class DeleteArticleUseCase
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    public function execute(string $userId, string $slug): void
    {
        $article = $this->articleRepository->findBySlug(new Slug($slug));

        if ($article === null) {
            throw new NotFoundException('Article not found');
        }

        $userIdVO = UserId::fromString($userId);

        if (! $article->canBeDeletedBy($userIdVO)) {
            throw new ForbiddenException('You are not authorized to delete this article');
        }

        $this->articleRepository->delete($article);
    }
}
