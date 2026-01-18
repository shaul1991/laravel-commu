<?php

declare(strict_types=1);

namespace Tests\Feature\AccessControl;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests for guest-only routes (OAuth-only authentication).
 * Note: Email/password authentication has been removed.
 */
final class GuestMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);
    }

    #[Test]
    public function guest_can_access_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_is_redirected_from_login_page(): void
    {
        $response = $this->actingAs($this->user)->get('/login');

        $response->assertRedirect('/');
    }
}
