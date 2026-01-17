<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_사용자의_게시글_목록을_조회할_수_있다(): void
    {
        $user = UserModel::factory()->create();
        ArticleModel::factory()->count(3)->published()->create(['author_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->username}/articles");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'category',
                        'view_count',
                        'like_count',
                        'published_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 3);
    }

    public function test_임시저장_게시글은_목록에_포함되지_않는다(): void
    {
        $user = UserModel::factory()->create();
        ArticleModel::factory()->count(2)->published()->create(['author_id' => $user->id]);
        ArticleModel::factory()->count(3)->draft()->create(['author_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->username}/articles");

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_존재하지_않는_사용자의_게시글_조회시_404를_반환한다(): void
    {
        $response = $this->getJson('/api/users/nonexistent_user/articles');

        $response->assertNotFound();
    }

    public function test_페이지네이션이_적용된다(): void
    {
        $user = UserModel::factory()->create();
        ArticleModel::factory()->count(15)->published()->create(['author_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->username}/articles?per_page=5");

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonCount(5, 'data');
    }
}
