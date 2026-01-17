<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Infrastructure\Persistence\Eloquent\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ReadNotificationTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_알림을_읽음_처리할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $notification = NotificationModel::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_인증되지_않은_사용자는_알림을_읽음_처리할_수_없다(): void
    {
        $notification = NotificationModel::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertUnauthorized();
    }

    public function test_다른_사용자의_알림을_읽음_처리할_수_없다(): void
    {
        $otherUser = UserModel::factory()->create();
        $notification = NotificationModel::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertForbidden();
    }

    public function test_존재하지_않는_알림을_읽음_처리할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/notifications/99999/read');

        $response->assertNotFound();
    }

    public function test_모든_알림을_읽음_처리할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        NotificationModel::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $response = $this->postJson('/api/notifications/read-all');

        $response->assertOk()
            ->assertJsonPath('message', '모든 알림을 읽음 처리했습니다.');

        $this->assertEquals(
            0,
            NotificationModel::where('user_id', $this->user->id)
                ->where('is_read', false)
                ->count()
        );
    }
}
