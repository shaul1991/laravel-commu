<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class SearchController extends Controller
{
    public function articles(Request $request): JsonResponse
    {
        $this->validateSearchQuery($request);

        $query = $request->input('q');
        $category = $request->input('category');

        $lowerQuery = mb_strtolower($query);
        $articlesQuery = ArticleModel::published()
            ->with('author')
            ->where(function ($q) use ($query, $lowerQuery) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('title', 'LIKE', "%{$lowerQuery}%")
                    ->orWhere('content_markdown', 'LIKE', "%{$query}%")
                    ->orWhere('content_markdown', 'LIKE', "%{$lowerQuery}%")
                    ->orWhere('content_html', 'LIKE', "%{$query}%")
                    ->orWhere('content_html', 'LIKE', "%{$lowerQuery}%");
            });

        if ($category) {
            $articlesQuery->byCategory($category);
        }

        $articlesQuery->orderBy('published_at', 'desc');

        $perPage = min((int) $request->input('per_page', 10), 50);
        $articles = $articlesQuery->paginate($perPage);

        return response()->json([
            'data' => $articles->map(fn ($article) => $this->formatArticle($article)),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $this->validateSearchQuery($request);

        $query = $request->input('q');
        $limit = min((int) $request->input('limit', 10), 50);

        $lowerQuery = mb_strtolower($query);
        $users = UserModel::where(function ($q) use ($query, $lowerQuery) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('name', 'LIKE', "%{$lowerQuery}%")
                ->orWhere('username', 'LIKE', "%{$query}%")
                ->orWhere('username', 'LIKE', "%{$lowerQuery}%");
        })
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $users->map(fn ($user) => $this->formatUser($user)),
        ]);
    }

    private function validateSearchQuery(Request $request): void
    {
        $query = $request->input('q');

        if (empty($query)) {
            throw ValidationException::withMessages([
                'q' => ['검색어는 필수입니다.'],
            ]);
        }

        if (mb_strlen($query) < 2) {
            throw ValidationException::withMessages([
                'q' => ['검색어는 2자 이상이어야 합니다.'],
            ]);
        }
    }

    private function formatArticle(ArticleModel $article): array
    {
        return [
            'id' => $article->uuid,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $this->getExcerpt($article->content_html),
            'category' => $article->category,
            'view_count' => $article->view_count,
            'like_count' => $article->like_count,
            'author' => $this->formatUser($article->author),
            'published_at' => $article->published_at?->toIso8601String(),
        ];
    }

    private function formatUser(UserModel $user): array
    {
        return [
            'id' => $user->uuid,
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
        ];
    }

    private function getExcerpt(string $html, int $length = 200): string
    {
        $text = strip_tags($html);
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'...';
    }
}
