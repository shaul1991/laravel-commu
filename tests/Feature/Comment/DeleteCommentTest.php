<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DeleteCommentTest extends TestCase
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
            'author_id' => $this->user->id,
        ]);
    }

    public function test_작성자는_댓글을_삭제할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/comments/{$this->comment->id}");

        $response->assertOk()
            ->assertJsonPath('message', '댓글이 삭제되었습니다.');

        $this->assertSoftDeleted('comments', [
            'id' => $this->comment->id,
        ]);
    }

    public function test_인증되지_않은_사용자는_댓글을_삭제할_수_없다(): void
    {
        $response = $this->deleteJson("/api/comments/{$this->comment->id}");

        $response->assertUnauthorized();
    }

    public function test_작성자가_아닌_사용자는_댓글을_삭제할_수_없다(): void
    {
        $otherUser = UserModel::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/comments/{$this->comment->id}");

        $response->assertForbidden();
    }

    public function test_존재하지_않는_댓글은_삭제할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/comments/99999');

        $response->assertNotFound();
    }

    public function test_대댓글이_있는_댓글을_삭제하면_삭제된_댓글로_표시된다(): void
    {
        Sanctum::actingAs($this->user);

        // 대댓글 생성
        CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'parent_id' => $this->comment->id,
        ]);

        $response = $this->deleteJson("/api/comments/{$this->comment->id}");

        $response->assertOk();

        // is_deleted가 true로 설정되지만 soft delete는 아님
        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'is_deleted' => true,
            'deleted_at' => null,
        ]);
    }

    public function test_대댓글이_없는_댓글을_삭제하면_완전히_삭제된다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/comments/{$this->comment->id}");

        $response->assertOk();

        $this->assertSoftDeleted('comments', [
            'id' => $this->comment->id,
        ]);
    }
}
