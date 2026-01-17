<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Tests client-side authentication redirect behavior.
 *
 * These tests verify that unauthenticated users are redirected to the login page
 * when accessing protected pages. The redirect is handled by JavaScript (auth.js)
 * which checks localStorage for auth tokens.
 *
 * @see \Tests\Feature\AccessControl\AuthMiddlewareTest for server-side tests
 */
class AuthRedirectTest extends DuskTestCase
{
    public function test_unauthenticated_user_is_redirected_from_write_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/write')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertQueryStringHas('redirect', '/write');
        });
    }

    public function test_unauthenticated_user_is_redirected_from_settings_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/settings')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertQueryStringHas('redirect', '/settings');
        });
    }

    public function test_unauthenticated_user_is_redirected_from_my_articles_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/me/articles')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertQueryStringHas('redirect', '/me/articles');
        });
    }

    public function test_unauthenticated_user_is_redirected_from_article_edit_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/articles/test-slug/edit')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertQueryStringHas('redirect', '/articles/test-slug/edit');
        });
    }
}
