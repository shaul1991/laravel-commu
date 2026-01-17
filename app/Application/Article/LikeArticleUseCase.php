<?php

declare(strict_types=1);

namespace App\Application\Article;

use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Exceptions\Http\NotFoundException;
use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use Illuminate\Support\Facades\DB;

final class LikeArticleUseCase
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    public function execute(string $userId, string $slug): LikeArticleResult
    {
        $article = $this->articleRepository->findBySlug(new Slug($slug));

        if ($article === null || ! $article->isPublished()) {
            throw new NotFoundException('Article not found');
        }

        $userIdVO = UserId::fromString($userId);

        // Get the article model for like operations
        $articleModel = ArticleModel::where('uuid', $article->id()->value())->firstOrFail();
        $userModel = \App\Infrastructure\Persistence\Eloquent\UserModel::where('uuid', $userId)->firstOrFail();

        $isLiked = $articleModel->isLikedBy($userModel->id);

        if ($isLiked) {
            // Unlike
            DB::table('article_likes')
                ->where('article_id', $articleModel->id)
                ->where('user_id', $userModel->id)
                ->delete();

            $articleModel->decrement('like_count');
            $newLikeCount = max(0, $articleModel->like_count);
        } else {
            // Like
            DB::table('article_likes')->insert([
                'article_id' => $articleModel->id,
                'user_id' => $userModel->id,
                'created_at' => now(),
            ]);

            $articleModel->increment('like_count');
            $newLikeCount = $articleModel->like_count;
        }

        return new LikeArticleResult(
            liked: ! $isLiked,
            likeCount: $newLikeCount
        );
    }
}
