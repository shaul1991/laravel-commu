<?php

declare(strict_types=1);

namespace App\Application\Tag;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Services\EloquentSyncArticleTagsService;

/**
 * Service wrapper for syncing article tags.
 * Delegates to EloquentSyncArticleTagsService in Infrastructure layer.
 *
 * @deprecated Use SyncArticleTagsInterface instead for new code.
 */
final class SyncArticleTagsService
{
    public function __construct(
        private readonly EloquentSyncArticleTagsService $eloquentService
    ) {}

    /**
     * Sync tags for an article.
     *
     * @param  array<string>  $tagNames
     */
    public function sync(ArticleModel $article, array $tagNames): void
    {
        $this->eloquentService->sync($article, $tagNames);
    }

    /**
     * Decrement article counts for all tags attached to an article.
     */
    public function decrementCountsForArticle(ArticleModel $article): void
    {
        $this->eloquentService->decrementCountsForArticle($article);
    }
}
