<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Core\Article\Entities\Article;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Article\ValueObjects\ArticleStatus;
use App\Domain\Core\Article\ValueObjects\Category;
use App\Domain\Core\Article\ValueObjects\Content;
use App\Domain\Core\Article\ValueObjects\Slug;
use App\Domain\Core\User\ValueObjects\UserId;
use DateTimeImmutable;

final class EloquentArticleRepository implements ArticleRepositoryInterface
{
    public function find(ArticleId $id): ?Article
    {
        $model = ArticleModel::where('uuid', $id->value())->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(Slug $slug): ?Article
    {
        $model = ArticleModel::where('slug', $slug->value())->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByIds(array $ids): array
    {
        $uuids = array_map(fn (ArticleId $id) => $id->value(), $ids);

        return ArticleModel::whereIn('uuid', $uuids)
            ->get()
            ->map(fn (ArticleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findByAuthor(UserId $authorId, int $limit = 10, int $offset = 0): array
    {
        $user = UserModel::where('uuid', $authorId->value())->first();

        if (! $user) {
            return [];
        }

        return ArticleModel::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (ArticleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findPublished(
        ?Category $category = null,
        int $limit = 10,
        int $offset = 0,
        string $sortBy = 'published_at',
        string $sortOrder = 'desc'
    ): array {
        $query = ArticleModel::published();

        if ($category !== null) {
            $query->byCategory($category->value);
        }

        // Map sortBy to database column
        $column = match ($sortBy) {
            'popular' => 'like_count',
            'views' => 'view_count',
            default => 'published_at',
        };

        return $query
            ->orderBy($column, $sortOrder)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (ArticleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findDraftsByAuthor(UserId $authorId): array
    {
        $user = UserModel::where('uuid', $authorId->value())->first();

        if (! $user) {
            return [];
        }

        return ArticleModel::draft()
            ->byAuthor($user->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (ArticleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function save(Article $article): void
    {
        $author = UserModel::where('uuid', $article->authorId()->value())->firstOrFail();

        $model = ArticleModel::firstOrNew(['uuid' => $article->id()->value()]);

        $model->author_id = $author->id;
        $model->title = $article->title();
        $model->slug = $article->slug()->value();
        $model->content_markdown = $article->content()->markdown();
        $model->content_html = $article->content()->html();
        $model->category = $article->category()->value;
        $model->status = $article->status()->value;
        $model->view_count = $article->viewCount();
        $model->like_count = $article->likeCount();
        $model->published_at = $article->publishedAt();

        $model->save();
    }

    public function delete(Article $article): void
    {
        ArticleModel::where('uuid', $article->id()->value())->delete();
    }

    public function existsBySlug(Slug $slug): bool
    {
        return ArticleModel::where('slug', $slug->value())->exists();
    }

    public function search(string $keyword, int $limit = 10): array
    {
        return ArticleModel::published()
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('content_markdown', 'like', "%{$keyword}%");
            })
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (ArticleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function countByStatus(ArticleStatus $status): int
    {
        return ArticleModel::where('status', $status->value)->count();
    }

    public function countByAuthor(UserId $authorId): int
    {
        $user = UserModel::where('uuid', $authorId->value())->first();

        if (! $user) {
            return 0;
        }

        return ArticleModel::where('author_id', $user->id)->count();
    }

    public function countPublished(?Category $category = null): int
    {
        $query = ArticleModel::published();

        if ($category !== null) {
            $query->byCategory($category->value);
        }

        return $query->count();
    }

    private function toEntity(ArticleModel $model): Article
    {
        return Article::reconstitute(
            id: ArticleId::fromString($model->uuid),
            authorId: UserId::fromString($model->author->uuid),
            title: $model->title,
            slug: new Slug($model->slug),
            content: new Content($model->content_markdown, $model->content_html),
            category: Category::from($model->category),
            status: ArticleStatus::from($model->status),
            viewCount: $model->view_count,
            likeCount: $model->like_count,
            publishedAt: $model->published_at ? new DateTimeImmutable($model->published_at->toDateTimeString()) : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
