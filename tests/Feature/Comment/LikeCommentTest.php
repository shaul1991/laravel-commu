<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LikeCommentTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private ArticleModel $article;

    private CommentModel $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
        $this->article = ArticleModel::factory()->published()->create();
        $this->comment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'like_count' => 0,
        ]);
    }

    public function test_인증된_사용자는_댓글에_좋아요를_누를_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/comments/{$this->comment->id}/like");

        $response->assertOk()
            ->assertJsonPath('data.is_liked', true)
            ->assertJsonPath('data.like_count', 1);

        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $this->comment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_인증되지_않은_사용자는_좋아요를_누를_수_없다(): void
    {
        $response = $this->postJson("/api/comments/{$this->comment->id}/like");

        $response->assertUnauthorized();
    }

    public function test_이미_좋아요한_댓글에_다시_좋아요를_누르면_취소된다(): void
    {
        $this->actingAs($this->user, 'api');

        // 먼저 좋아요
        $this->postJson("/api/comments/{$this->comment->id}/like");

        // 다시 좋아요 (토글)
        $response = $this->postJson("/api/comments/{$this->comment->id}/like");

        $response->assertOk()
            ->assertJsonPath('data.is_liked', false)
            ->assertJsonPath('data.like_count', 0);

        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $this->comment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_존재하지_않는_댓글에_좋아요를_누를_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson('/api/comments/99999/like');

        $response->assertNotFound();
    }

    public function test_좋아요를_누르면_댓글의_like_count가_증가한다(): void
    {
        $this->actingAs($this->user, 'api');

        $initialCount = $this->comment->like_count;

        $this->postJson("/api/comments/{$this->comment->id}/like");

        $this->comment->refresh();
        $this->assertEquals($initialCount + 1, $this->comment->like_count);
    }

    public function test_좋아요를_취소하면_댓글의_like_count가_감소한다(): void
    {
        $this->actingAs($this->user, 'api');

        // 먼저 좋아요
        $this->postJson("/api/comments/{$this->comment->id}/like");
        $this->comment->refresh();
        $countAfterLike = $this->comment->like_count;

        // 좋아요 취소
        $this->postJson("/api/comments/{$this->comment->id}/like");
        $this->comment->refresh();

        $this->assertEquals($countAfterLike - 1, $this->comment->like_count);
    }

    public function test_삭제된_댓글에는_좋아요를_누를_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $deletedComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'is_deleted' => true,
        ]);

        $response = $this->postJson("/api/comments/{$deletedComment->id}/like");

        $response->assertStatus(422)
            ->assertJsonPath('message', '삭제된 댓글에는 좋아요를 누를 수 없습니다.');
    }
}
