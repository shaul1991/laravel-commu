<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Core\Article\ValueObjects\ArticleId;
use App\Domain\Core\Tag\Entities\Tag;
use App\Domain\Core\Tag\Repositories\TagRepositoryInterface;
use App\Domain\Core\Tag\ValueObjects\TagId;
use DateTimeImmutable;

final class EloquentTagRepository implements TagRepositoryInterface
{
    public function find(TagId $id): ?Tag
    {
        $model = TagModel::where('uuid', $id->value())->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByName(string $name): ?Tag
    {
        $model = TagModel::where('name', $name)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Tag
    {
        $model = TagModel::where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByIds(array $ids): array
    {
        $uuids = array_map(fn (TagId $id) => $id->value(), $ids);

        return TagModel::whereIn('uuid', $uuids)
            ->get()
            ->map(fn (TagModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findByNames(array $names): array
    {
        return TagModel::whereIn('name', $names)
            ->get()
            ->map(fn (TagModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findByArticleIds(array $articleIds): array
    {
        $uuids = array_map(fn (ArticleId $id) => $id->value(), $articleIds);

        $articles = ArticleModel::whereIn('uuid', $uuids)->get();
        $articleIdMap = $articles->pluck('id', 'uuid')->toArray();

        $result = [];
        foreach ($articles as $article) {
            $tags = $article->tags->map(fn (TagModel $model) => $this->toEntity($model))->all();
            $result[$article->uuid] = $tags;
        }

        return $result;
    }

    public function findPopular(int $limit = 10): array
    {
        return TagModel::popular($limit)
            ->get()
            ->map(fn (TagModel $model) => $this->toEntity($model))
            ->all();
    }

    public function save(Tag $tag): void
    {
        $model = TagModel::firstOrNew(['uuid' => $tag->id()->value()]);

        $model->name = $tag->name();
        $model->slug = $tag->slug();
        $model->article_count = $tag->articleCount();

        $model->save();
    }

    public function delete(Tag $tag): void
    {
        TagModel::where('uuid', $tag->id()->value())->delete();
    }

    public function search(string $keyword, int $limit = 10): array
    {
        return TagModel::search($keyword)
            ->take($limit)
            ->get()
            ->map(fn (TagModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findOrCreate(string $name): Tag
    {
        $existingTag = $this->findByName($name);

        if ($existingTag !== null) {
            return $existingTag;
        }

        $tag = Tag::create(TagId::generate(), $name);
        $this->save($tag);

        return $tag;
    }

    private function toEntity(TagModel $model): Tag
    {
        return Tag::reconstitute(
            id: TagId::fromString($model->uuid),
            name: $model->name,
            slug: $model->slug,
            articleCount: $model->article_count,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString())
        );
    }
}
