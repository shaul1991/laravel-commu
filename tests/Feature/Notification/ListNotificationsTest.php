<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Infrastructure\Persistence\Eloquent\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_알림_목록을_조회할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        NotificationModel::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'message',
                        'data',
                        'is_read',
                        'created_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 5);
    }

    public function test_인증되지_않은_사용자는_알림_목록을_조회할_수_없다(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized();
    }

    public function test_다른_사용자의_알림은_조회되지_않는다(): void
    {
        $this->actingAs($this->user, 'api');

        $otherUser = UserModel::factory()->create();
        NotificationModel::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);
        NotificationModel::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_알림은_최신순으로_정렬된다(): void
    {
        $this->actingAs($this->user, 'api');

        $oldNotification = NotificationModel::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay(),
        ]);

        $newNotification = NotificationModel::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals($newNotification->id, $data[0]['id']);
    }

    public function test_읽지_않은_알림만_필터링할_수_있다(): void
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

        $response = $this->getJson('/api/notifications?unread_only=true');

        $response->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_페이지네이션이_적용된다(): void
    {
        $this->actingAs($this->user, 'api');

        NotificationModel::factory()->count(15)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/notifications?per_page=5');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonCount(5, 'data');
    }
}
