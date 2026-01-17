<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_사용자_프로필을_조회할_수_있다(): void
    {
        $user = UserModel::factory()->create([
            'name' => '김지훈',
            'username' => 'jihoon_kim',
            'bio' => '백엔드 개발자입니다.',
        ]);

        $response = $this->getJson("/api/users/{$user->username}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'username',
                    'avatar_url',
                    'bio',
                    'article_count',
                    'follower_count',
                    'following_count',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.username', 'jihoon_kim')
            ->assertJsonPath('data.name', '김지훈')
            ->assertJsonPath('data.bio', '백엔드 개발자입니다.');
    }

    public function test_존재하지_않는_사용자_조회시_404를_반환한다(): void
    {
        $response = $this->getJson('/api/users/nonexistent_user');

        $response->assertNotFound();
    }

    public function test_사용자_프로필에_게시글_수가_포함된다(): void
    {
        $user = UserModel::factory()->create();
        ArticleModel::factory()->count(5)->published()->create(['author_id' => $user->id]);
        ArticleModel::factory()->count(2)->draft()->create(['author_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->username}");

        $response->assertOk()
            ->assertJsonPath('data.article_count', 5); // 발행된 글만 카운트
    }
}
