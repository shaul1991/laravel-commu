<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\ArticleFeed\Services;

use App\Application\Contracts\CacheInterface;
use App\Domain\Aggregator\ArticleFeed\DTOs\ArticleFeedDTO;
use App\Domain\Aggregator\ArticleFeed\DTOs\FeedCriteria;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\ArticleStatus;
use App\Domain\Core\Tag\Repositories\TagRepositoryInterface;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\ValueObjects\UserId;

class ArticleFeedService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TagRepositoryInterface $tagRepository,
        private readonly CacheInterface $cache
    ) {}

    public function getFeed(FeedCriteria $criteria): ArticleFeedDTO
    {
        $cacheKey = "feed:{$criteria->hash()}";

        return $this->cache->remember($cacheKey, self::CACHE_TTL, function () use ($criteria) {
            $articles = $this->articleRepository->findPublished(
                category: $criteria->category,
                limit: $criteria->perPage,
                offset: $criteria->offset(),
                sortBy: $criteria->sortBy,
                sortOrder: $criteria->sortOrder
            );

            $total = $this->articleRepository->countByStatus(ArticleStatus::PUBLISHED);

            // Batch load authors
            $authorIds = array_map(fn ($article) => $article->authorId(), $articles);
            $authorsArray = $this->userRepository->findByIds($authorIds);
            $authors = [];
            foreach ($authorsArray as $author) {
                $authors[$author->id()->value()] = $author;
            }

            // Batch load tags
            $articleIds = array_map(fn ($article) => $article->id(), $articles);
            $tags = $this->tagRepository->findByArticleIds($articleIds);

            return new ArticleFeedDTO(
                articles: $articles,
                authors: $authors,
                tags: $tags,
                total: $total,
                page: $criteria->page,
                perPage: $criteria->perPage
            );
        });
    }

    public function getAuthorFeed(UserId $authorId, int $page = 1, int $perPage = 10): ArticleFeedDTO
    {
        $offset = ($page - 1) * $perPage;

        $articles = $this->articleRepository->findByAuthor($authorId, $perPage, $offset);
        $total = $this->articleRepository->countByAuthor($authorId);

        $author = $this->userRepository->find($authorId);
        $authors = $author ? [$author->id()->value() => $author] : [];

        $articleIds = array_map(fn ($article) => $article->id(), $articles);
        $tags = $this->tagRepository->findByArticleIds($articleIds);

        return new ArticleFeedDTO(
            articles: $articles,
            authors: $authors,
            tags: $tags,
            total: $total,
            page: $page,
            perPage: $perPage
        );
    }

    public function invalidateCache(): void
    {
        $this->cache->tags(['feed'])->flush();
    }
}
