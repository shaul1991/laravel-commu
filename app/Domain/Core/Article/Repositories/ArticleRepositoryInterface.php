<?php

declare(strict_types=1);

namespace App\Domain\Core\Article\Repositories;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Article\ValueObjects\ArticleStatus;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;

interface ArticleRepositoryInterface
{
    public function find(ArticleId $id): ?Article;

    public function findBySlug(Slug $slug): ?Article;

    /**
     * @param  ArticleId[]  $ids
     * @return Article[]
     */
    public function findByIds(array $ids): array;

    /**
     * @return Article[]
     */
    public function findByAuthor(UserId $authorId, int $limit = 10, int $offset = 0): array;

    /**
     * @return Article[]
     */
    public function findPublished(
        ?Category $category = null,
        int $limit = 10,
        int $offset = 0,
        string $sortBy = 'published_at',
        string $sortOrder = 'desc'
    ): array;

    /**
     * @return Article[]
     */
    public function findDraftsByAuthor(UserId $authorId): array;

    public function save(Article $article): void;

    public function delete(Article $article): void;

    public function existsBySlug(Slug $slug): bool;

    /**
     * @return Article[]
     */
    public function search(string $keyword, int $limit = 10): array;

    public function countByStatus(ArticleStatus $status): int;

    public function countByAuthor(UserId $authorId): int;

    /**
     * Count published articles, optionally filtered by category.
     */
    public function countPublished(?Category $category = null): int;
}
