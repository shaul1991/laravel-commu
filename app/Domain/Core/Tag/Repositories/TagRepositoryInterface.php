<?php

declare(strict_types=1);

namespace App\Domain\Core\Tag\Repositories;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Tag\Entities\Tag;
use App\Domain\Core\Tag\ValueObjects\TagId;

interface TagRepositoryInterface
{
    public function find(TagId $id): ?Tag;

    public function findByName(string $name): ?Tag;

    public function findBySlug(string $slug): ?Tag;

    /**
     * @param  TagId[]  $ids
     * @return Tag[]
     */
    public function findByIds(array $ids): array;

    /**
     * @param  string[]  $names
     * @return Tag[]
     */
    public function findByNames(array $names): array;

    /**
     * @param  ArticleId[]  $articleIds
     * @return array<string, Tag[]>
     */
    public function findByArticleIds(array $articleIds): array;

    /**
     * @return Tag[]
     */
    public function findPopular(int $limit = 10): array;

    public function save(Tag $tag): void;

    public function delete(Tag $tag): void;

    /**
     * @return Tag[]
     */
    public function search(string $keyword, int $limit = 10): array;

    public function findOrCreate(string $name): Tag;
}
