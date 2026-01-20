<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\Http\NotFoundException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = TagModel::orderBy('name')
            ->get()
            ->map(fn (TagModel $tag) => $this->formatTag($tag));

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function popular(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 50);

        $tags = TagModel::popular($limit)
            ->get()
            ->map(fn (TagModel $tag) => $this->formatTag($tag));

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $keyword = $request->input('q', '');
        $limit = min((int) $request->input('limit', 10), 50);

        if (empty($keyword)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $tags = TagModel::search($keyword)
            ->take($limit)
            ->get()
            ->map(fn (TagModel $tag) => $this->formatTag($tag));

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $tag = TagModel::where('slug', $slug)->first();

        if (! $tag) {
            throw new NotFoundException('Tag not found');
        }

        return response()->json([
            'data' => $this->formatTag($tag),
        ]);
    }

    public function articles(Request $request, string $slug): JsonResponse
    {
        $tag = TagModel::where('slug', $slug)->first();

        if (! $tag) {
            throw new NotFoundException('Tag not found');
        }

        $perPage = min((int) $request->input('per_page', 10), 50);

        $articles = $tag->articles()
            ->where('status', 'published')
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $articles->map(fn (ArticleModel $article) => $this->formatArticle($article)),
            'meta' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }

    private function formatTag(TagModel $tag): array
    {
        return [
            'id' => $tag->uuid,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'article_count' => $tag->article_count,
        ];
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
            'author' => [
                'id' => $article->author->uuid,
                'name' => $article->author->name,
                'username' => $article->author->username,
                'avatar_url' => $article->author->avatar_url,
            ],
            'published_at' => $article->published_at?->toIso8601String(),
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
