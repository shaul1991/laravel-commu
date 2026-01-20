<?php

declare(strict_types=1);

namespace App\Application\Contracts;

/**
 * Interface for syncing tags with articles.
 * Implementation handles tag creation, deduplication, and article count updates.
 */
interface SyncArticleTagsInterface
{
    /**
     * Sync tags for an article by article ID.
     * Creates new tags if they don't exist, reuses existing ones.
     * Handles deduplication and article count updates.
     *
     * @param  int  $articleId  The article's database ID
     * @param  array<string>  $tagNames  Array of tag names to sync
     */
    public function syncByArticleId(int $articleId, array $tagNames): void;

    /**
     * Decrement article counts for all tags attached to an article.
     * Should be called after article deletion within a transaction.
     *
     * @param  int  $articleId  The article's database ID
     */
    public function decrementCountsForArticleId(int $articleId): void;
}
