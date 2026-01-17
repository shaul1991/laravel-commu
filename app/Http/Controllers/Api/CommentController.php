<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CommentController extends Controller
{
    public function index(Request $request, string $slug): JsonResponse
    {
        $article = ArticleModel::where('slug', $slug)
            ->published()
            ->first();

        if (! $article) {
            throw new NotFoundException('Article not found');
        }

        $query = CommentModel::byArticle($article->id)
            ->rootComments()
            ->with(['author', 'replies.author']);

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'popular' => $query->orderBy('like_count', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $perPage = min((int) $request->input('per_page', 20), 50);
        $comments = $query->paginate($perPage);

        $currentUserId = $request->user()?->id;

        return response()->json([
            'data' => $comments->map(fn ($comment) => $this->formatComment($comment, $currentUserId)),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ]);
    }

    public function store(CreateCommentRequest $request, string $slug): JsonResponse
    {
        $article = ArticleModel::where('slug', $slug)
            ->published()
            ->first();

        if (! $article) {
            throw new NotFoundException('Article not found');
        }

        /** @var UserModel $user */
        $user = $request->user();

        $comment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $article->id,
            'author_id' => $user->id,
            'content' => $request->validated('content'),
        ]);

        $comment->load('author');

        return response()->json([
            'data' => $this->formatComment($comment, $user->id),
        ], 201);
    }

    public function reply(CreateCommentRequest $request, int $commentId): JsonResponse
    {
        $parentComment = CommentModel::find($commentId);

        if (! $parentComment) {
            throw new NotFoundException('Comment not found');
        }

        if ($parentComment->is_deleted) {
            return response()->json([
                'message' => '삭제된 댓글에는 답글을 작성할 수 없습니다.',
            ], 422);
        }

        /** @var UserModel $user */
        $user = $request->user();

        // 2단계 깊이 제한: 대댓글의 대댓글은 원본 댓글의 대댓글로 생성
        $actualParentId = $parentComment->parent_id ?? $parentComment->id;

        $reply = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $parentComment->article_id,
            'author_id' => $user->id,
            'parent_id' => $actualParentId,
            'content' => $request->validated('content'),
        ]);

        // 실제 부모 댓글의 reply_count 증가
        CommentModel::where('id', $actualParentId)->increment('reply_count');

        $reply->load('author');

        return response()->json([
            'data' => $this->formatReply($reply, $user->id),
        ], 201);
    }

    public function update(UpdateCommentRequest $request, int $commentId): JsonResponse
    {
        $comment = CommentModel::find($commentId);

        if (! $comment) {
            throw new NotFoundException('Comment not found');
        }

        /** @var UserModel $user */
        $user = $request->user();

        if ($comment->author_id !== $user->id) {
            throw new ForbiddenException('You are not authorized to update this comment');
        }

        $comment->update([
            'content' => $request->validated('content'),
        ]);

        $comment->load('author');

        return response()->json([
            'data' => $this->formatComment($comment, $user->id),
        ]);
    }

    public function destroy(Request $request, int $commentId): JsonResponse
    {
        $comment = CommentModel::find($commentId);

        if (! $comment) {
            throw new NotFoundException('Comment not found');
        }

        /** @var UserModel $user */
        $user = $request->user();

        if ($comment->author_id !== $user->id) {
            throw new ForbiddenException('You are not authorized to delete this comment');
        }

        // 대댓글이 있는 경우 is_deleted 플래그만 설정
        $hasReplies = CommentModel::where('parent_id', $comment->id)->exists();

        if ($hasReplies) {
            $comment->update(['is_deleted' => true]);
        } else {
            $comment->delete();
        }

        return response()->json([
            'message' => '댓글이 삭제되었습니다.',
        ]);
    }

    public function like(Request $request, int $commentId): JsonResponse
    {
        $comment = CommentModel::find($commentId);

        if (! $comment) {
            throw new NotFoundException('Comment not found');
        }

        if ($comment->is_deleted) {
            return response()->json([
                'message' => '삭제된 댓글에는 좋아요를 누를 수 없습니다.',
            ], 422);
        }

        /** @var UserModel $user */
        $user = $request->user();

        $isLiked = $comment->isLikedBy($user->id);

        if ($isLiked) {
            // 좋아요 취소
            $comment->likedByUsers()->detach($user->id);
            $comment->decrement('like_count');
        } else {
            // 좋아요 추가
            $comment->likedByUsers()->attach($user->id);
            $comment->increment('like_count');
        }

        $comment->refresh();

        return response()->json([
            'data' => [
                'is_liked' => ! $isLiked,
                'like_count' => $comment->like_count,
            ],
        ]);
    }

    private function formatComment(CommentModel $comment, ?int $currentUserId): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->getDisplayContent(),
            'author' => $this->formatAuthor($comment->author),
            'like_count' => $comment->like_count,
            'reply_count' => $comment->reply_count,
            'is_liked' => $comment->isLikedBy($currentUserId),
            'is_mine' => $currentUserId !== null && $comment->author_id === $currentUserId,
            'replies' => $comment->replies->map(fn ($reply) => $this->formatReply($reply, $currentUserId))->toArray(),
            'created_at' => $comment->created_at->toIso8601String(),
        ];
    }

    private function formatReply(CommentModel $reply, ?int $currentUserId): array
    {
        return [
            'id' => $reply->id,
            'content' => $reply->getDisplayContent(),
            'author' => $this->formatAuthor($reply->author),
            'parent_id' => $reply->parent_id,
            'like_count' => $reply->like_count,
            'is_liked' => $reply->isLikedBy($currentUserId),
            'is_mine' => $currentUserId !== null && $reply->author_id === $currentUserId,
            'created_at' => $reply->created_at->toIso8601String(),
        ];
    }

    private function formatAuthor(UserModel $author): array
    {
        return [
            'id' => $author->id,
            'name' => $author->name,
            'username' => $author->username,
            'avatar_url' => $author->avatar_url,
        ];
    }
}
