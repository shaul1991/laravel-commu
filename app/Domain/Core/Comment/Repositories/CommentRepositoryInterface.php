<?php

declare(strict_types=1);

namespace App\Domain\Core\Comment\Repositories;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Comment\Entities\Comment;
use App\Domain\Core\Comment\ValueObjects\CommentId;
use App\Domain\Core\User\ValueObjects\UserId;

interface CommentRepositoryInterface
{
    public function find(CommentId $id): ?Comment;

    /**
     * @return Comment[]
     */
    public function findByArticle(ArticleId $articleId, int $limit = 50, int $offset = 0): array;

    /**
     * @return Comment[]
     */
    public function findReplies(CommentId $parentId): array;

    /**
     * @return Comment[]
     */
    public function findByAuthor(UserId $authorId, int $limit = 10, int $offset = 0): array;

    public function save(Comment $comment): void;

    public function delete(Comment $comment): void;

    public function countByArticle(ArticleId $articleId): int;

    public function countByAuthor(UserId $authorId): int;
}
