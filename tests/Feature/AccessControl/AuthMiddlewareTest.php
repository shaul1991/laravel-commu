<?php

declare(strict_types=1);

namespace Tests\Feature\AccessControl;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Auth-required pages are protected via client-side JavaScript authentication.
 * The server returns the page (200 OK) and JavaScript checks localStorage
 * for auth tokens, redirecting unauthenticated users to the login page.
 *
 * These tests verify that the pages are accessible (server returns 200).
 *
 * @see \Tests\Browser\AuthRedirectTest for client-side redirect browser tests
 */
final class AuthMiddlewareTest extends TestCase
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
    public function write_page_returns_200(): void
    {
        $response = $this->get('/write');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_can_access_write_page(): void
    {
        $response = $this->actingAs($this->user)->get('/write');

        $response->assertStatus(200);
    }

    #[Test]
    public function article_edit_page_returns_200(): void
    {
        $response = $this->get('/articles/test-slug/edit');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_can_access_article_edit_page(): void
    {
        $response = $this->actingAs($this->user)->get('/articles/test-slug/edit');

        $response->assertStatus(200);
    }

    #[Test]
    public function settings_page_returns_200(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get('/settings');

        $response->assertStatus(200);
    }

    #[Test]
    public function my_articles_page_returns_200(): void
    {
        $response = $this->get('/me/articles');

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_can_access_my_articles_page(): void
    {
        $response = $this->actingAs($this->user)->get('/me/articles');

        $response->assertStatus(200);
    }
}
