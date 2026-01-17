<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\Http\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $user = UserModel::where('username', $username)->first();

        if (! $user) {
            throw new NotFoundException('User not found');
        }

        $articleCount = ArticleModel::where('author_id', $user->id)
            ->published()
            ->count();

        return response()->json([
            'data' => [
                'id' => $user->uuid,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'article_count' => $articleCount,
                'follower_count' => $user->follower_count,
                'following_count' => $user->following_count,
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    public function articles(Request $request, string $username): JsonResponse
    {
        $user = UserModel::where('username', $username)->first();

        if (! $user) {
            throw new NotFoundException('User not found');
        }

        $perPage = min((int) $request->input('per_page', 10), 50);

        $articles = ArticleModel::where('author_id', $user->id)
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

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

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $user->update([
            'name' => $request->validated('name'),
            'bio' => $request->validated('bio'),
        ]);

        $user->refresh();

        return response()->json([
            'data' => [
                'id' => $user->uuid,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
            ],
        ]);
    }

    public function follow(Request $request, string $username): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $targetUser = UserModel::where('username', $username)->first();

        if (! $targetUser) {
            throw new NotFoundException('User not found');
        }

        if ($user->id === $targetUser->id) {
            return response()->json([
                'message' => '자기 자신을 팔로우할 수 없습니다.',
            ], 422);
        }

        $isFollowing = $targetUser->isFollowedBy($user->id);

        if ($isFollowing) {
            // Unfollow
            $user->following()->detach($targetUser->id);
            $targetUser->decrement('follower_count');
            $user->decrement('following_count');
        } else {
            // Follow
            $user->following()->attach($targetUser->id);
            $targetUser->increment('follower_count');
            $user->increment('following_count');
        }

        $targetUser->refresh();

        return response()->json([
            'data' => [
                'is_following' => ! $isFollowing,
                'follower_count' => $targetUser->follower_count,
            ],
        ]);
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
