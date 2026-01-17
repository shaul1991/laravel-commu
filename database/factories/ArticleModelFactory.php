<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ArticleModel>
 */
final class ArticleModelFactory extends Factory
{
    protected $model = ArticleModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $content = fake()->paragraphs(3, true);

        return [
            'uuid' => (string) Str::uuid(),
            'author_id' => UserModel::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'content_markdown' => $content,
            'content_html' => '<p>'.nl2br(e($content)).'</p>',
            'category' => fake()->randomElement(['tech', 'life', 'career', 'news']),
            'status' => 'published',
            'view_count' => fake()->numberBetween(0, 1000),
            'like_count' => fake()->numberBetween(0, 100),
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
