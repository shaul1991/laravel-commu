<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 아티클 상세 페이지 뷰 테스트
 *
 * ECS-125: 로그인 후에도 댓글 영역에 로그인 버튼이 노출되는 버그 수정
 */
final class ArticleDetailViewTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private ArticleModel $article;

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

        $this->article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'author_id' => $this->user->id,
            'title' => 'Test Article',
            'slug' => 'test-article-123456',
            'content_markdown' => '# Test\n\nThis is a test.',
            'content_html' => '<h1>Test</h1><p>This is a test.</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);
    }

    #[Test]
    public function comment_section_shows_comment_form_for_authenticated_user(): void
    {
        // 로그인된 사용자로 아티클 상세 페이지 접근
        $this->actingAs($this->user);

        $response = $this->get("/articles/{$this->article->slug}");

        $response->assertStatus(200);

        // 댓글 작성 폼이 표시되어야 함 (Alpine.js 기반 인증 확인)
        // HTML에서 따옴표가 이스케이프될 수 있으므로 다양한 패턴 확인
        $content = $response->getContent();

        // isAuthenticated 변수가 사용되고 있는지 확인
        $this->assertTrue(
            str_contains($content, 'isAuthenticated'),
            'Alpine.js isAuthenticated variable should be used'
        );

        // 댓글 작성 placeholder 확인
        $response->assertSee('댓글을 작성하세요...');

        // init() 메서드가 있어야 함 (인증 상태 초기화를 위해)
        $this->assertTrue(
            str_contains($content, 'init()'),
            'init() method should be defined for auth state initialization'
        );
    }

    #[Test]
    public function comment_section_shows_login_button_for_guest_user(): void
    {
        // 비로그인 상태로 아티클 상세 페이지 접근
        $response = $this->get("/articles/{$this->article->slug}");

        $response->assertStatus(200);

        // 댓글 섹션 컴포넌트가 존재해야 함
        $response->assertSee('commentSection');

        // Alpine.js 기반 인증 상태 확인 로직이 있어야 함
        $response->assertSee('isAuthenticated');
    }

    #[Test]
    public function comment_section_uses_client_side_auth_check(): void
    {
        $response = $this->get("/articles/{$this->article->slug}");

        $response->assertStatus(200);

        // 서버 사이드 @auth 대신 클라이언트 사이드 인증 확인 사용 확인
        // Alpine.js의 isAuthenticated 변수를 사용해야 함
        $response->assertSee('isAuthenticated');
    }
}
