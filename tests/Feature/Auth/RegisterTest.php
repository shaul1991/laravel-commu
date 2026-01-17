<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'username',
                    'created_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'hong@example.com',
            'username' => 'honggildong',
        ]);
    }

    #[Test]
    public function registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'invalid-email',
            'username' => 'honggildong',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function registration_fails_with_duplicate_email(): void
    {
        // First registration
        $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong1',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Second registration with same email
        $response = $this->postJson('/api/auth/register', [
            'name' => '김철수',
            'email' => 'hong@example.com',
            'username' => 'kimchulsu',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function registration_fails_with_duplicate_username(): void
    {
        // First registration
        $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Second registration with same username
        $response = $this->postJson('/api/auth/register', [
            'name' => '김철수',
            'email' => 'kim@example.com',
            'username' => 'honggildong',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    #[Test]
    public function registration_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function registration_fails_with_password_mismatch(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function registration_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'username', 'password']);
    }

    #[Test]
    public function registration_fails_with_invalid_username_format(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'invalid username!', // spaces and special chars
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    #[Test]
    public function registration_fails_with_too_short_username(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'ab', // too short
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }
}
