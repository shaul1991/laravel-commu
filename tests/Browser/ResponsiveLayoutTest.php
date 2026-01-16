<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * 반응형 레이아웃 테스트 - Tech Blog
 *
 * Breakpoints:
 * - Mobile: 375px
 * - Tablet: 768px
 * - Desktop: 1280px
 *
 * 테스트 시나리오:
 * 1. 정상 케이스: 각 브레이크포인트에서 올바른 레이아웃
 * 2. 예외 케이스: 극단적인 뷰포트 크기
 * 3. 엣지 케이스: 브레이크포인트 경계값
 */
class ResponsiveLayoutTest extends DuskTestCase
{
    #[Test]
    public function desktop_shows_two_column_layout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                // 메인 컨텐츠
                ->assertVisible('main')
                // 사이드바 (시리즈, 카테고리, 태그, 뉴스레터)
                ->assertSee('인기 시리즈')
                ->assertSee('카테고리')
                ->assertSee('인기 태그')
                ->assertSee('뉴스레터 구독');
        });
    }

    #[Test]
    public function tablet_layout_adjusts_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(768, 1024)
                ->visit('/')
                ->assertSee('Featured')
                ->assertSee('최신 아티클');
        });
    }

    #[Test]
    public function mobile_shows_single_column_layout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812)
                ->visit('/')
                // 모바일 카테고리 pills
                ->assertSee('전체')
                ->assertSee('Backend')
                // 메인 컨텐츠
                ->assertSee('최신 아티클')
                ->assertSee('PostgreSQL 18 성능 튜닝 완벽 가이드');
        });
    }

    #[Test]
    public function mobile_header_shows_hamburger_menu(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812)
                ->visit('/')
                ->assertPresent('button[aria-label="Open menu"]');
        });
    }

    #[Test]
    public function desktop_header_shows_full_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertVisible('nav')
                ->assertSeeLink('Home')
                ->assertSeeLink('Discussions')
                ->assertSeeLink('Categories')
                ->assertSeeLink('Members');
        });
    }

    #[Test]
    public function hero_section_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            // Desktop - Featured article with large layout
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('Laravel 12와 PHP 8.4로 만드는 현대적인 웹 애플리케이션');

            // Mobile - Still visible but adjusted
            $browser->resize(375, 812)
                ->visit('/')
                ->assertSee('Laravel 12와 PHP 8.4로 만드는 현대적인 웹 애플리케이션');
        });
    }

    #[Test]
    public function category_tabs_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            // Desktop - Tab buttons
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('전체')
                ->assertSee('Backend')
                ->assertSee('Frontend')
                ->assertSee('DevOps');

            // Mobile - Category pills (horizontal scroll)
            $browser->resize(375, 812)
                ->visit('/')
                ->assertSee('전체')
                ->assertSee('Backend')
                ->assertSee('AI/ML');
        });
    }

    #[Test]
    public function footer_adjusts_for_mobile(): void
    {
        $this->browse(function (Browser $browser) {
            // Desktop - Multi column grid
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('Community')
                ->assertSee('Resources')
                ->assertSee('Legal');

            // Mobile - Simplified layout
            $browser->resize(375, 812)
                ->visit('/')
                ->assertSee('© 2026 Community');
        });
    }

    #[Test]
    public function edge_case_very_small_viewport(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(320, 480)
                ->visit('/')
                // 페이지가 여전히 렌더링되어야 함
                ->assertSee('Tech Blog')
                ->assertSee('Featured');
        });
    }

    #[Test]
    public function edge_case_very_large_viewport(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(2560, 1440)
                ->visit('/')
                // 컨텐츠가 max-width 내에서 렌더링
                ->assertSee('Laravel 12와 PHP 8.4로 만드는 현대적인 웹 애플리케이션')
                ->assertPresent('.container-main');
        });
    }

    #[Test]
    public function breakpoint_boundary_lg(): void
    {
        $this->browse(function (Browser $browser) {
            // Just below lg (1024px)
            $browser->resize(1023, 768)
                ->visit('/')
                ->assertSee('최신 아티클');

            // At lg breakpoint
            $browser->resize(1024, 768)
                ->visit('/')
                ->assertSee('최신 아티클');
        });
    }

    #[Test]
    public function article_grid_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            // Desktop - 2 column grid
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertPresent('.grid.sm\\:grid-cols-2');

            // Mobile - 1 column
            $browser->resize(375, 812)
                ->visit('/')
                ->assertSee('PostgreSQL 18 성능 튜닝 완벽 가이드')
                ->assertSee('Tailwind CSS 4 마이그레이션 가이드');
        });
    }
}
