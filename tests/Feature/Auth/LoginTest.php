<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'hong@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'username',
                    ],
                    'token',
                ],
                'message',
            ]);
    }

    #[Test]
    public function login_fails_with_invalid_password(): void
    {
        UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'hong@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function login_fails_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function login_fails_with_missing_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function user_can_login_with_username(): void
    {
        UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'honggildong', // using username instead of email
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }
}
