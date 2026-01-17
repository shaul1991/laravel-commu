<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('currentPassword123'),
        ]);
    }

    public function test_인증된_사용자는_이메일을_변경할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/email', [
            'email' => 'new-email@example.com',
            'password' => 'currentPassword123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'new-email@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'new-email@example.com',
        ]);
    }

    public function test_인증되지_않은_사용자는_이메일을_변경할_수_없다(): void
    {
        $response = $this->putJson('/api/settings/account/email', [
            'email' => 'new-email@example.com',
            'password' => 'currentPassword123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_잘못된_비밀번호로_이메일을_변경할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/email', [
            'email' => 'new-email@example.com',
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', '비밀번호가 일치하지 않습니다.');
    }

    public function test_이미_사용중인_이메일로_변경할_수_없다(): void
    {
        $otherUser = UserModel::factory()->create(['email' => 'existing@example.com']);

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/email', [
            'email' => 'existing@example.com',
            'password' => 'currentPassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_인증된_사용자는_비밀번호를_변경할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/password', [
            'current_password' => 'currentPassword123',
            'password' => 'newPassword456',
            'password_confirmation' => 'newPassword456',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', '비밀번호가 변경되었습니다.');

        // 새 비밀번호로 로그인 확인
        $this->assertTrue(Hash::check('newPassword456', $this->user->fresh()->password));
    }

    public function test_현재_비밀번호가_틀리면_변경할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/password', [
            'current_password' => 'wrongPassword',
            'password' => 'newPassword456',
            'password_confirmation' => 'newPassword456',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', '현재 비밀번호가 일치하지 않습니다.');
    }

    public function test_비밀번호_확인이_일치하지_않으면_변경할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/password', [
            'current_password' => 'currentPassword123',
            'password' => 'newPassword456',
            'password_confirmation' => 'differentPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_비밀번호는_8자_이상이어야_한다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/settings/account/password', [
            'current_password' => 'currentPassword123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
