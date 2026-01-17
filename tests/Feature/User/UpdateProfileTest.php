<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create([
            'name' => '김지훈',
            'bio' => '기존 소개',
        ]);
    }

    public function test_인증된_사용자는_프로필을_수정할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/me', [
            'name' => '김지훈(수정)',
            'bio' => '수정된 소개입니다.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', '김지훈(수정)')
            ->assertJsonPath('data.bio', '수정된 소개입니다.');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => '김지훈(수정)',
            'bio' => '수정된 소개입니다.',
        ]);
    }

    public function test_인증되지_않은_사용자는_프로필을_수정할_수_없다(): void
    {
        $response = $this->putJson('/api/users/me', [
            'name' => '수정된 이름',
        ]);

        $response->assertUnauthorized();
    }

    public function test_이름은_필수이다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/me', [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_이름은_50자를_초과할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/me', [
            'name' => str_repeat('a', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_소개는_200자를_초과할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/me', [
            'name' => '김지훈',
            'bio' => str_repeat('a', 201),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bio']);
    }

    public function test_소개는_선택사항이다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/me', [
            'name' => '김지훈',
        ]);

        $response->assertOk();
    }
}
