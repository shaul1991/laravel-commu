<?php

declare(strict_types=1);

namespace Tests\Browser\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * 댓글 섹션 브라우저 테스트
 *
 * QA 체크리스트:
 * - [x] 비로그인 사용자: 로그인 버튼 표시
 * - [x] 로그인 사용자: 댓글 작성 폼 표시
 * - [x] 로그인 사용자: 댓글 작성 가능
 * - [x] 본인 댓글: 수정/삭제 버튼 표시
 * - [x] 타인 댓글: 수정/삭제 버튼 미표시
 * - [x] 로그인 사용자: 좋아요 기능 동작
 * - [x] 로그인 사용자: 답글 작성 가능
 *
 * Note: 이 테스트는 실제 데이터베이스의 데이터를 사용합니다.
 * 테스트 전에 적어도 하나의 published 게시글이 존재해야 합니다.
 */
class CommentSectionTest extends DuskTestCase
{
    private ?ArticleModel $article = null;

    private ?UserModel $testUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        // 테스트용 게시글 조회 (실제 데이터베이스에서)
        $this->article = ArticleModel::where('status', 'published')->first();

        // 테스트용 사용자 조회
        $this->testUser = UserModel::where('email', 'shaul@kakao.com')->first();
    }

    #[Test]
    public function guest_sees_login_button_in_comment_section(): void
    {
        if (! $this->article) {
            $this->markTestSkipped('No published article found in database');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/articles/'.$this->article->slug)
                ->waitForText('댓글', 10)
                ->assertSee('댓글을 작성하려면 로그인이 필요합니다.')
                ->assertSeeLink('로그인');
        });
    }

    #[Test]
    public function authenticated_user_sees_comment_form(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->testUser)
                ->visit('/articles/'.$this->article->slug)
                ->waitFor('textarea[placeholder="댓글을 작성하세요..."]', 10)
                ->assertSee('댓글 작성');
        });
    }

    #[Test]
    public function authenticated_user_can_create_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        $commentContent = 'Dusk 테스트 댓글 '.time();

        $this->browse(function (Browser $browser) use ($commentContent) {
            $browser->loginAs($this->testUser)
                ->visit('/articles/'.$this->article->slug)
                ->waitFor('textarea[placeholder="댓글을 작성하세요..."]', 10)
                ->type('textarea[placeholder="댓글을 작성하세요..."]', $commentContent)
                ->click('button[type="submit"]')
                ->waitForText($commentContent, 10)
                ->assertSee($commentContent);
        });

        // 테스트 후 정리
        CommentModel::where('content', $commentContent)->delete();
    }

    #[Test]
    public function user_sees_edit_delete_buttons_on_own_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        // 테스트용 댓글 생성
        $comment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $this->article->id,
            'author_id' => $this->testUser->id,
            'content' => 'Dusk 수정삭제 테스트 '.time(),
        ]);

        try {
            $this->browse(function (Browser $browser) use ($comment) {
                $browser->loginAs($this->testUser)
                    ->visit('/articles/'.$this->article->slug)
                    ->waitForText($comment->content, 10)
                    ->assertSee($comment->content)
                    // 메뉴 버튼 클릭 (dusk selector 사용)
                    ->click('[dusk="comment-menu-'.$comment->id.'"]')
                    ->waitForText('수정')
                    ->assertSee('수정')
                    ->assertSee('삭제');
            });
        } finally {
            // 테스트 후 정리
            $comment->delete();
        }
    }

    #[Test]
    public function user_does_not_see_edit_delete_on_others_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        // 다른 사용자 찾기 또는 생성
        $otherUser = UserModel::where('id', '!=', $this->testUser->id)->first();

        if (! $otherUser) {
            $this->markTestSkipped('No other user found for test');
        }

        // 다른 사용자의 댓글 생성
        $otherUserComment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $this->article->id,
            'author_id' => $otherUser->id,
            'content' => 'Dusk 타인 댓글 테스트 '.time(),
        ]);

        try {
            $this->browse(function (Browser $browser) use ($otherUserComment) {
                $browser->loginAs($this->testUser)
                    ->visit('/articles/'.$this->article->slug)
                    ->waitForText($otherUserComment->content, 10)
                    ->assertSee($otherUserComment->content)
                    // 메뉴 버튼 클릭
                    ->click('[dusk="comment-menu-'.$otherUserComment->id.'"]')
                    // 신고 버튼이 보이는지 확인 (dusk selector로 확인)
                    ->waitFor('[dusk="comment-report-'.$otherUserComment->id.'"]')
                    ->assertPresent('[dusk="comment-report-'.$otherUserComment->id.'"]')
                    // 수정/삭제 버튼이 없는지 확인 (dusk selector로)
                    ->assertMissing('[dusk="comment-edit-'.$otherUserComment->id.'"]')
                    ->assertMissing('[dusk="comment-delete-'.$otherUserComment->id.'"]');
            });
        } finally {
            // 테스트 후 정리
            $otherUserComment->delete();
        }
    }

    #[Test]
    public function user_can_like_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        // 테스트용 댓글 생성
        $comment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $this->article->id,
            'author_id' => $this->testUser->id,
            'content' => 'Dusk 좋아요 테스트 '.time(),
            'like_count' => 0,
        ]);

        try {
            $this->browse(function (Browser $browser) use ($comment) {
                $browser->loginAs($this->testUser)
                    ->visit('/articles/'.$this->article->slug)
                    ->waitForText($comment->content, 10)
                    ->assertSee($comment->content)
                    // 좋아요 버튼 클릭
                    ->click('[dusk="comment-like-button-'.$comment->id.'"]')
                    // 좋아요 카운트가 1로 변경되었는지 확인
                    ->waitFor('[dusk="comment-like-button-'.$comment->id.'"].text-red-500', 5);
            });
        } finally {
            // 테스트 후 정리
            $comment->likedByUsers()->detach();
            $comment->delete();
        }
    }

    #[Test]
    public function user_can_reply_to_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        // 테스트용 댓글 생성
        $comment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $this->article->id,
            'author_id' => $this->testUser->id,
            'content' => 'Dusk 답글 테스트 원본 '.time(),
        ]);

        $replyContent = 'Dusk 답글 테스트 '.time();

        try {
            $this->browse(function (Browser $browser) use ($comment, $replyContent) {
                $browser->loginAs($this->testUser)
                    ->visit('/articles/'.$this->article->slug)
                    ->waitForText($comment->content, 10)
                    ->assertSee($comment->content)
                    // 답글 버튼 클릭
                    ->click('[dusk="comment-reply-button-'.$comment->id.'"]')
                    // 답글 폼이 나타날 때까지 대기
                    ->waitFor('textarea[placeholder="답글을 작성하세요..."]', 5)
                    ->type('textarea[placeholder="답글을 작성하세요..."]', $replyContent)
                    // 답글 작성 버튼 클릭 (dusk selector 사용)
                    ->click('[dusk="comment-reply-submit-'.$comment->id.'"]')
                    // 답글이 표시될 때까지 대기
                    ->waitForText($replyContent, 10)
                    ->assertSee($replyContent);
            });
        } finally {
            // 테스트 후 정리 (답글 먼저 삭제)
            CommentModel::where('parent_id', $comment->id)->delete();
            $comment->delete();
        }
    }

    #[Test]
    public function user_can_delete_own_comment(): void
    {
        if (! $this->article || ! $this->testUser) {
            $this->markTestSkipped('No published article or test user found');
        }

        $commentContent = 'Dusk 삭제 테스트 '.time();

        // 테스트용 댓글 생성
        $comment = CommentModel::create([
            'uuid' => (string) Str::uuid(),
            'article_id' => $this->article->id,
            'author_id' => $this->testUser->id,
            'content' => $commentContent,
        ]);

        $this->browse(function (Browser $browser) use ($comment, $commentContent) {
            $browser->loginAs($this->testUser)
                ->visit('/articles/'.$this->article->slug)
                ->waitForText($commentContent, 10)
                ->assertSee($commentContent)
                // 메뉴 버튼 클릭
                ->click('[dusk="comment-menu-'.$comment->id.'"]')
                // Alpine.js 상태 업데이트 대기
                ->pause(300)
                // 삭제 버튼이 보일 때까지 대기
                ->waitForText('삭제', 5)
                // 삭제 버튼 클릭 (dusk selector 사용)
                ->click('[dusk="comment-delete-'.$comment->id.'"]')
                // confirm 다이얼로그 수락
                ->acceptDialog()
                // 댓글이 삭제되었는지 확인 (대댓글 없는 댓글은 완전 삭제됨)
                ->pause(1000)
                ->assertDontSee($commentContent);
        });
    }
}
