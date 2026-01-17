<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_알림_설정을_조회할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/settings/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'email_on_comment',
                    'email_on_reply',
                    'email_on_follow',
                    'email_on_like',
                    'push_enabled',
                ],
            ]);
    }

    public function test_인증되지_않은_사용자는_알림_설정을_조회할_수_없다(): void
    {
        $response = $this->getJson('/api/settings/notifications');

        $response->assertUnauthorized();
    }

    public function test_인증된_사용자는_알림_설정을_수정할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/notifications', [
            'email_on_comment' => false,
            'email_on_reply' => true,
            'email_on_follow' => false,
            'email_on_like' => false,
            'push_enabled' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email_on_comment', false)
            ->assertJsonPath('data.email_on_reply', true)
            ->assertJsonPath('data.push_enabled', true);
    }

    public function test_인증되지_않은_사용자는_알림_설정을_수정할_수_없다(): void
    {
        $response = $this->putJson('/api/settings/notifications', [
            'email_on_comment' => false,
        ]);

        $response->assertUnauthorized();
    }

    public function test_부분_업데이트가_가능하다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/notifications', [
            'email_on_comment' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email_on_comment', false);
    }
}
