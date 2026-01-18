<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FollowUserTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private UserModel $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
        $this->targetUser = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_다른_사용자를_팔로우할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/users/{$this->targetUser->username}/follow");

        $response->assertOk()
            ->assertJsonPath('data.is_following', true)
            ->assertJsonPath('data.follower_count', 1);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);
    }

    public function test_인증되지_않은_사용자는_팔로우할_수_없다(): void
    {
        $response = $this->postJson("/api/users/{$this->targetUser->username}/follow");

        $response->assertUnauthorized();
    }

    public function test_이미_팔로우한_사용자를_다시_팔로우하면_언팔로우된다(): void
    {
        $this->actingAs($this->user, 'api');

        // 먼저 팔로우
        $this->postJson("/api/users/{$this->targetUser->username}/follow");

        // 다시 팔로우 (토글)
        $response = $this->postJson("/api/users/{$this->targetUser->username}/follow");

        $response->assertOk()
            ->assertJsonPath('data.is_following', false)
            ->assertJsonPath('data.follower_count', 0);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);
    }

    public function test_자기_자신을_팔로우할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/users/{$this->user->username}/follow");

        $response->assertStatus(422)
            ->assertJsonPath('message', '자기 자신을 팔로우할 수 없습니다.');
    }

    public function test_존재하지_않는_사용자를_팔로우할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson('/api/users/nonexistent_user/follow');

        $response->assertNotFound();
    }
}
