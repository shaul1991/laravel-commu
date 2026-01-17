<?php

declare(strict_types=1);

namespace Tests\Feature\AccessControl;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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

    #[Test]
    public function guest_can_access_register_page(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_is_redirected_from_register_page(): void
    {
        $response = $this->actingAs($this->user)->get('/register');

        $response->assertRedirect('/');
    }

    #[Test]
    public function guest_can_access_forgot_password_page(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_is_redirected_from_forgot_password_page(): void
    {
        $response = $this->actingAs($this->user)->get('/forgot-password');

        $response->assertRedirect('/');
    }

    #[Test]
    public function user_can_access_login_page_after_logout_via_api(): void
    {
        // Step 1: Login via API (this establishes session and creates token)
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'hong@example.com',
            'password' => 'Password123!',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        // Verify we are now authenticated
        $this->assertAuthenticated('web');

        // Step 2: Logout via API with token
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // Step 3: Simulate a fresh browser request by refreshing the application
        // This clears the test's internal auth state
        $this->refreshApplication();

        // Step 4: Make a fresh request to login page
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('로그인');
    }

    #[Test]
    public function user_can_access_register_page_after_logout_via_api(): void
    {
        // Step 1: Login via API
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'hong@example.com',
            'password' => 'Password123!',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        // Step 2: Logout via API
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);

        // Step 3: Simulate a fresh browser request
        $this->refreshApplication();

        // Step 4: Access register page
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('회원가입');
    }
}
