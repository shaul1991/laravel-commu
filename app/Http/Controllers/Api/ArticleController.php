<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Article\CreateArticleInput;
use App\Application\Article\CreateArticleUseCase;
use App\Application\Article\DeleteArticleUseCase;
use App\Application\Article\LikeArticleUseCase;
use App\Application\Article\UpdateArticleInput;
use App\Application\Article\UpdateArticleUseCase;
use App\Domain\Core\Article\Exceptions\ArticleNotEditableException;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Article\CreateArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\TagModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ArticleController extends Controller
{
    public function __construct(
        private readonly CreateArticleUseCase $createArticleUseCase,
        private readonly UpdateArticleUseCase $updateArticleUseCase,
        private readonly DeleteArticleUseCase $deleteArticleUseCase,
        private readonly LikeArticleUseCase $likeArticleUseCase
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ArticleModel::published()->with('author');

        // Category filter
        if ($request->has('category')) {
            $query->byCategory($request->input('category'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'popular' => $query->orderBy('like_count', 'desc'),
            'views' => $query->orderBy('view_count', 'desc'),
            default => $query->orderBy('published_at', 'desc'),
        };

        $perPage = min((int) $request->input('per_page', 10), 50);
        $articles = $query->paginate($perPage);

        return response()->json([
            'data' => $articles->map(fn ($article) => $this->formatListItem($article)),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $article = ArticleModel::where('slug', $slug)->with(['author', 'tags'])->first();

        if (! $article) {
            throw new NotFoundException('Article not found');
        }

        // Try to get authenticated user from bearer token
        $currentUser = $this->getAuthenticatedUser($request);
        $isAuthor = $currentUser && $article->author_id === $currentUser->id;

        if ($article->status !== 'published' && ! $isAuthor) {
            throw new NotFoundException('Article not found');
        }

        // Increment view count for published articles (once per day per IP)
        if ($article->status === 'published') {
            $viewKey = 'article_view:'.$article->id.':'.md5($request->ip().date('Y-m-d'));
            if (! cache()->has($viewKey)) {
                $article->increment('view_count');
                $article->refresh();
                cache()->put($viewKey, true, now()->addDay());
            }
        }

        // Check if current user has liked this article
        $isLiked = $currentUser ? $article->isLikedBy($currentUser->id) : false;

        return response()->json([
            'data' => $this->formatDetail($article, $isAuthor, $isLiked),
        ]);
    }

    /**
     * Try to get authenticated user from bearer token without middleware.
     * For Passport JWT tokens, extract token ID and find the user.
     */
    private function getAuthenticatedUser(Request $request): ?UserModel
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return null;
        }

        try {
            // Parse JWT to get token ID
            $parts = explode('.', $bearerToken);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            $tokenId = $payload['jti'] ?? null;

            if (! $tokenId) {
                return null;
            }

            // Find Passport token
            $token = \Laravel\Passport\Token::find($tokenId);

            if (! $token || $token->revoked) {
                return null;
            }

            $user = $token->user;

            return $user instanceof UserModel ? $user : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function store(CreateArticleRequest $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $input = new CreateArticleInput(
            authorId: $user->uuid,
            title: $request->validated('title'),
            content: $request->validated('content'),
            category: $request->validated('category'),
            isDraft: (bool) $request->validated('is_draft', false)
        );

        $article = $this->createArticleUseCase->execute($input);

        // Load the created article with author
        $articleModel = ArticleModel::where('uuid', $article->id()->value())
            ->with(['author', 'tags'])
            ->firstOrFail();

        // Handle tags
        $tags = $request->validated('tags', []);
        if (! empty($tags)) {
            $this->syncTags($articleModel, $tags);
            $articleModel->refresh();
            $articleModel->load('tags');
        }

        return response()->json([
            'data' => $this->formatDetail($articleModel),
        ], 201);
    }

    public function update(UpdateArticleRequest $request, string $slug): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        try {
            $input = new UpdateArticleInput(
                userId: $user->uuid,
                slug: $slug,
                title: $request->validated('title'),
                content: $request->validated('content'),
                category: $request->validated('category'),
                isDraft: (bool) $request->validated('is_draft', true)
            );

            $article = $this->updateArticleUseCase->execute($input);

            $articleModel = ArticleModel::where('uuid', $article->id()->value())
                ->with(['author', 'tags'])
                ->firstOrFail();

            // Handle tags
            $tags = $request->validated('tags');
            if ($tags !== null) {
                $this->syncTags($articleModel, $tags);
                $articleModel->refresh();
                $articleModel->load('tags');
            }

            return response()->json([
                'data' => $this->formatDetail($articleModel),
            ]);
        } catch (ArticleNotEditableException) {
            throw new ForbiddenException('This article cannot be edited');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'Article not found') {
                throw new NotFoundException('Article not found');
            }
            throw $e;
        }
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        // Decrement tag article counts before deletion
        $articleModel = ArticleModel::where('slug', $slug)->with('tags')->first();
        if ($articleModel) {
            foreach ($articleModel->tags as $tag) {
                $tag->decrementArticleCount();
            }
        }

        $this->deleteArticleUseCase->execute($user->uuid, $slug);

        return response()->json([
            'message' => 'Article deleted successfully',
        ]);
    }

    public function like(Request $request, string $slug): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $result = $this->likeArticleUseCase->execute($user->uuid, $slug);

        return response()->json([
            'data' => [
                'is_liked' => $result->liked,
                'like_count' => $result->likeCount,
            ],
        ]);
    }

    public function drafts(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $drafts = ArticleModel::draft()
            ->byAuthor($user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'data' => $drafts->map(fn ($article) => $this->formatDraftItem($article)),
        ]);
    }

    public function publish(Request $request, string $slug): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $article = ArticleModel::where('slug', $slug)->with('author')->first();

        if (! $article) {
            throw new NotFoundException('Article not found');
        }

        if ($article->author_id !== $user->id) {
            throw new ForbiddenException('You are not authorized to publish this article');
        }

        if ($article->status === 'published') {
            return response()->json([
                'message' => 'Article is already published',
            ], 422);
        }

        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $article->refresh();

        return response()->json([
            'data' => $this->formatDetail($article),
        ]);
    }

    private function formatListItem(ArticleModel $article): array
    {
        return [
            'id' => $article->uuid,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $this->getExcerpt($article->content_html),
            'category' => $article->category,
            'view_count' => $article->view_count,
            'like_count' => $article->like_count,
            'reading_time' => $this->calculateReadingTime($article->content_html),
            'author' => $this->formatAuthor($article->author),
            'published_at' => $article->published_at?->toIso8601String(),
        ];
    }

    private function formatDraftItem(ArticleModel $article): array
    {
        return [
            'id' => $article->uuid,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category,
            'created_at' => $article->created_at->toIso8601String(),
            'updated_at' => $article->updated_at->toIso8601String(),
        ];
    }

    private function formatDetail(ArticleModel $article, bool $isAuthor = false, bool $isLiked = false): array
    {
        return [
            'id' => $article->uuid,
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content_markdown,
            'content_html' => $article->content_html,
            'category' => $article->category,
            'status' => $article->status,
            'view_count' => $article->view_count,
            'like_count' => $article->like_count,
            'is_liked' => $isLiked,
            'reading_time' => $this->calculateReadingTime($article->content_html),
            'author' => $this->formatAuthor($article->author),
            'is_author' => $isAuthor,
            'tags' => $article->tags->map(fn (TagModel $tag) => [
                'id' => $tag->uuid,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values()->toArray(),
            'published_at' => $article->published_at?->toIso8601String(),
            'created_at' => $article->created_at->toIso8601String(),
            'updated_at' => $article->updated_at->toIso8601String(),
        ];
    }

    private function formatAuthor(UserModel $author): array
    {
        return [
            'id' => $author->uuid,
            'name' => $author->name,
            'username' => $author->username,
            'avatar_url' => $author->avatar_url,
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

    /**
     * Calculate reading time in minutes based on content.
     * Average reading speed: ~500 characters per minute for Korean text.
     */
    private function calculateReadingTime(string $html): int
    {
        $text = strip_tags($html);
        $characterCount = mb_strlen($text);

        return max(1, (int) ceil($characterCount / 500));
    }

    /**
     * Sync tags for an article.
     * Creates new tags if they don't exist, reuses existing ones.
     *
     * @param  array<string>  $tagNames
     */
    private function syncTags(ArticleModel $article, array $tagNames): void
    {
        $oldTagIds = $article->tags->pluck('id')->toArray();
        $newTagIds = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            // Find or create tag
            $tag = TagModel::where('name', $tagName)->first();

            if (! $tag) {
                // Create new tag
                $slug = $this->generateTagSlug($tagName);
                $tag = TagModel::create([
                    'uuid' => Str::uuid()->toString(),
                    'name' => $tagName,
                    'slug' => $slug,
                    'article_count' => 0,
                ]);
            }

            $newTagIds[] = $tag->id;
        }

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

        // Sync the relationship
        $article->tags()->sync($newTagIds);
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
