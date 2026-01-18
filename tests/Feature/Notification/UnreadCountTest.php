<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Infrastructure\Persistence\Eloquent\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

final class UnreadCountTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_읽지_않은_알림_수를_조회할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        NotificationModel::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        NotificationModel::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJsonPath('data.count', 3);
    }

    public function test_인증되지_않은_사용자는_읽지_않은_알림_수를_조회할_수_없다(): void
    {
        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertUnauthorized();
    }

    public function test_알림이_없으면_0을_반환한다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJsonPath('data.count', 0);
    }
}
