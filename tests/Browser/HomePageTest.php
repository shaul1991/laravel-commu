<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Tech Blog - 메인 페이지 UI 테스트
 *
 * QA 체크리스트:
 * - [x] 페이지 로딩 및 타이틀 확인
 * - [x] Hero 섹션 (Featured Article) 렌더링
 * - [x] PC 레이아웃 (2-column grid: 메인 + 사이드바)
 * - [x] 모바일 레이아웃 (1-column stack)
 * - [x] 네비게이션 컴포넌트
 * - [x] Article 카드 컴포넌트
 * - [x] 사이드바 위젯 (시리즈, 카테고리, 태그, 뉴스레터)
 */
class HomePageTest extends DuskTestCase
{
    #[Test]
    public function home_page_loads_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertTitle('Tech Blog - 개발자를 위한 기술 블로그')
                ->assertPresent('header')
                ->assertPresent('main')
                ->assertPresent('footer');
        });
    }

    #[Test]
    public function hero_section_displays_featured_article(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Featured')
                ->assertSee('Laravel 12와 PHP 8.4로 만드는 현대적인 웹 애플리케이션')
                ->assertSee('김지훈')
                ->assertSee('12분');
        });
    }

    #[Test]
    public function secondary_featured_posts_display(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Redis를 활용한 효율적인 캐싱 전략')
                ->assertSee('React Server Components 실전 가이드')
                ->assertSee('Backend')
                ->assertSee('Frontend');
        });
    }

    #[Test]
    public function navigation_contains_required_links(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSeeLink('Home')
                ->assertSeeLink('Discussions')
                ->assertSeeLink('Categories')
                ->assertSeeLink('Members')
                ->assertSeeLink('Sign In')
                ->assertSeeLink('Sign Up');
        });
    }

    #[Test]
    public function category_tabs_display(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('최신 아티클')
                ->assertSee('전체')
                ->assertSee('Backend')
                ->assertSee('Frontend')
                ->assertSee('DevOps');
        });
    }

    #[Test]
    public function article_cards_display_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('PostgreSQL 18 성능 튜닝 완벽 가이드')
                ->assertSee('Tailwind CSS 4 마이그레이션 가이드')
                ->assertSee('Docker Compose로 개발 환경 구축하기')
                ->assertSee('Claude API를 활용한 코드 리뷰 자동화');
        });
    }

    #[Test]
    public function article_cards_show_metadata(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                // Author names
                ->assertSee('김디비')
                ->assertSee('박스타일')
                // Read time
                ->assertSee('10분')
                ->assertSee('7분')
                // Tags
                ->assertSee('postgresql')
                ->assertSee('tailwindcss');
        });
    }

    #[Test]
    public function series_sidebar_displays_on_desktop(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('인기 시리즈')
                ->assertSee('Laravel 마스터 클래스')
                ->assertSee('실전 Docker & Kubernetes')
                ->assertSee('AI 개발자 되기');
        });
    }

    #[Test]
    public function categories_sidebar_displays(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('카테고리')
                ->assertSee('Backend')
                ->assertSee('Frontend')
                ->assertSee('DevOps')
                ->assertSee('AI/ML')
                ->assertSee('Database');
        });
    }

    #[Test]
    public function popular_tags_widget_displays(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('인기 태그')
                ->assertSee('laravel')
                ->assertSee('php')
                ->assertSee('javascript')
                ->assertSee('docker')
                ->assertSee('postgresql');
        });
    }

    #[Test]
    public function newsletter_widget_displays(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertSee('뉴스레터 구독')
                ->assertSee('매주 엄선된 기술 아티클을 받아보세요')
                ->assertSee('구독하기')
                ->assertPresent('input[type="email"]');
        });
    }

    #[Test]
    public function footer_contains_required_elements(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('© 2026 Community')
                ->assertSee('Privacy Policy')
                ->assertSee('Terms of Service');
        });
    }

    #[Test]
    public function mobile_layout_shows_category_pills(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812)
                ->visit('/')
                // 모바일 카테고리 pill 표시
                ->assertSee('전체')
                ->assertSee('Backend')
                ->assertSee('Frontend')
                ->assertSee('AI/ML')
                // 메인 컨텐츠
                ->assertSee('최신 아티클');
        });
    }

    #[Test]
    public function mobile_menu_button_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812)
                ->visit('/')
                ->assertPresent('button[aria-label="Open menu"]');
        });
    }

    #[Test]
    public function skip_to_content_link_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('a[href="#main-content"]');
        });
    }

    #[Test]
    public function search_input_is_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)
                ->visit('/')
                ->assertPresent('input[type="search"]');
        });
    }

    #[Test]
    public function load_more_button_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('더 많은 아티클 보기');
        });
    }
}
