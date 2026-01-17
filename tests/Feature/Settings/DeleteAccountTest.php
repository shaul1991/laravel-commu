<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create([
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_인증된_사용자는_계정을_삭제할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/settings/account', [
            'password' => 'password123',
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
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_잘못된_비밀번호로_계정을_삭제할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/settings/account', [
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', '비밀번호가 일치하지 않습니다.');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_비밀번호_없이_계정을_삭제할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/settings/account', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
