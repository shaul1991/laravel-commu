<?php

declare(strict_types=1);

namespace App\Application\Tag;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use Illuminate\Support\Str;

final class SyncArticleTagsService
{
    /**
     * Sync tags for an article.
     * Creates new tags if they don't exist, reuses existing ones.
     * Handles deduplication and article count updates.
     *
     * @param  array<string>  $tagNames
     */
    public function sync(ArticleModel $article, array $tagNames): void
    {
        $oldTagIds = $article->tags->pluck('id')->toArray();
        $newTagIds = [];

        // Normalize and deduplicate tag names (case-insensitive)
        $normalizedTagNames = $this->normalizeAndDeduplicate($tagNames);

        foreach ($normalizedTagNames as $tagName) {
            $tag = $this->findOrCreateTag($tagName);
            $newTagIds[] = $tag->id;
        }

        // Ensure newTagIds is unique (additional safety)
        $newTagIds = array_unique($newTagIds);

        // Update article counts
        $this->updateArticleCounts($oldTagIds, $newTagIds);

        // Sync the relationship
        $article->tags()->sync($newTagIds);
    }

    /**
     * Decrement article counts for all tags attached to an article.
     * Should be called after article deletion within a transaction.
     */
    public function decrementCountsForArticle(ArticleModel $article): void
    {
        foreach ($article->tags as $tag) {
            $tag->decrementArticleCount();
        }
    }

    /**
     * Normalize and deduplicate tag names.
     * Removes empty tags and duplicates (case-insensitive).
     *
     * @param  array<string>  $tagNames
     * @return array<string>
     */
    private function normalizeAndDeduplicate(array $tagNames): array
    {
        $normalized = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            $key = mb_strtolower($tagName);
            if (! isset($normalized[$key])) {
                $normalized[$key] = $tagName;
            }
        }

        return array_values($normalized);
    }

    /**
     * Find existing tag or create a new one.
     */
    private function findOrCreateTag(string $tagName): TagModel
    {
        $tag = TagModel::where('name', $tagName)->first();

        if ($tag) {
            return $tag;
        }

        return TagModel::create([
            'uuid' => Str::uuid()->toString(),
            'name' => $tagName,
            'slug' => $this->generateTagSlug($tagName),
            'article_count' => 0,
        ]);
    }

    /**
     * Update article counts for added and removed tags.
     *
     * @param  array<int>  $oldTagIds
     * @param  array<int>  $newTagIds
     */
    private function updateArticleCounts(array $oldTagIds, array $newTagIds): void
    {
        // Decrement count for removed tags
        $removedTagIds = array_diff($oldTagIds, $newTagIds);
        foreach ($removedTagIds as $tagId) {
            $tag = TagModel::find($tagId);
            if ($tag) {
                $tag->decrementArticleCount();
            }
        }

        // Increment count for added tags
        $addedTagIds = array_diff($newTagIds, $oldTagIds);
        foreach ($addedTagIds as $tagId) {
            $tag = TagModel::find($tagId);
            if ($tag) {
                $tag->incrementArticleCount();
            }
        }
    }

    /**
     * Generate a slug for a tag name.
     * Supports Korean characters.
     */
    private function generateTagSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9가-힣]+/u', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while (TagModel::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
