<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CommentModel>
 */
final class CommentModelFactory extends Factory
{
    protected $model = CommentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'article_id' => ArticleModel::factory(),
            'author_id' => UserModel::factory(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'like_count' => fake()->numberBetween(0, 50),
            'reply_count' => 0,
            'is_deleted' => false,
        ];
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
        ]);
    }

    public function withParent(CommentModel $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'article_id' => $parent->article_id,
        ]);
    }
}
