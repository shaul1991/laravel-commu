<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Card 컴포넌트 테스트 - Tech Blog
 *
 * 테스트 항목:
 * - 기본 렌더링
 * - Header/Body slots
 * - Hover 효과
 * - Article Card 컴포넌트
 */
class CardComponentTest extends DuskTestCase
{
    #[Test]
    public function card_renders_with_correct_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('.card');
        });
    }

    #[Test]
    public function card_header_renders_when_provided(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertPresent('.card-header');
        });
    }

    #[Test]
    public function article_cards_display_category_badge(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Backend')
                ->assertSee('Frontend')
                ->assertSee('DevOps')
                ->assertSee('AI');
        });
    }

    #[Test]
    public function article_cards_display_author_info(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('김디비')
                ->assertSee('박스타일')
                ->assertSee('이데브옵스');
        });
    }

    #[Test]
    public function article_cards_display_read_time(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('10분')
                ->assertSee('7분')
                ->assertSee('8분');
        });
    }

    #[Test]
    public function article_cards_display_tags(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('postgresql')
                ->assertSee('tailwindcss')
                ->assertSee('docker');
        });
    }

    #[Test]
    public function sidebar_cards_have_header_slot(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('인기 시리즈')
                ->assertSee('카테고리')
                ->assertSee('인기 태그')
                ->assertSee('뉴스레터 구독');
        });
    }

    #[Test]
    public function cards_have_hover_shadow_effect(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('.card.hover\\:shadow-lg');
        });
    }
}
