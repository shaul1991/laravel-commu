<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for account deletion (OAuth-only authentication).
 * Note: Uses confirmation phrase instead of password verification.
 */
final class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_인증된_사용자는_계정을_삭제할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->deleteJson('/api/settings/account', [
            'confirmation' => '삭제합니다',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', '계정이 삭제되었습니다.');

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    public function test_인증되지_않은_사용자는_계정을_삭제할_수_없다(): void
    {
        $response = $this->deleteJson('/api/settings/account', [
            'confirmation' => '삭제합니다',
        ]);

        $response->assertUnauthorized();
    }

    public function test_잘못된_확인_문구로_계정을_삭제할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->deleteJson('/api/settings/account', [
            'confirmation' => '잘못된문구',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_확인_문구_없이_계정을_삭제할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->deleteJson('/api/settings/account', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }
}
