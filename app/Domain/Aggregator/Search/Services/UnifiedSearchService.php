<?php

declare(strict_types=1);

namespace App\Domain\Aggregator\Search\Services;

use App\Domain\Aggregator\Search\DTOs\SearchQuery;
use App\Domain\Aggregator\Search\DTOs\SearchResultDTO;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Tag\Repositories\TagRepositoryInterface;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;

class UnifiedSearchService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TagRepositoryInterface $tagRepository
    ) {}

    public function search(SearchQuery $query): SearchResultDTO
    {
        $articles = [];
        $users = [];
        $tags = [];

        if ($query->includesType('articles')) {
            $articles = $this->articleRepository->search($query->keyword, $query->limit);
        }

        if ($query->includesType('users')) {
            $users = $this->userRepository->search($query->keyword, $query->limit);
        }

        if ($query->includesType('tags')) {
            $tags = $this->tagRepository->search($query->keyword, $query->limit);
        }

        return new SearchResultDTO(
            articles: $articles,
            users: $users,
            tags: $tags,
            keyword: $query->keyword,
            totalArticles: count($articles),
            totalUsers: count($users),
            totalTags: count($tags)
        );
    }

    public function searchArticles(string $keyword, int $limit = 10): array
    {
        return $this->articleRepository->search($keyword, $limit);
    }

    public function searchUsers(string $keyword, int $limit = 10): array
    {
        return $this->userRepository->search($keyword, $limit);
    }

    public function searchTags(string $keyword, int $limit = 10): array
    {
        return $this->tagRepository->search($keyword, $limit);
    }
}
