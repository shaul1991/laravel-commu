<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReplyCommentTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private ArticleModel $article;

    private CommentModel $parentComment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
        $this->article = ArticleModel::factory()->published()->create();
        $this->parentComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
        ]);
    }

    public function test_인증된_사용자는_대댓글을_작성할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/comments/{$this->parentComment->id}/replies", [
            'content' => '대댓글 내용입니다.',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'author',
                    'parent_id',
                    'like_count',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.content', '대댓글 내용입니다.')
            ->assertJsonPath('data.parent_id', $this->parentComment->id);

        $this->assertDatabaseHas('comments', [
            'parent_id' => $this->parentComment->id,
            'author_id' => $this->user->id,
            'content' => '대댓글 내용입니다.',
        ]);
    }

    public function test_인증되지_않은_사용자는_대댓글을_작성할_수_없다(): void
    {
        $response = $this->postJson("/api/comments/{$this->parentComment->id}/replies", [
            'content' => '대댓글 내용',
        ]);

        $response->assertUnauthorized();
    }

    public function test_2단계_깊이까지만_대댓글을_작성할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        // 1단계 대댓글 생성
        $firstReply = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'parent_id' => $this->parentComment->id,
        ]);

        // 2단계 대댓글 시도 (허용되어야 함 - 단, 1단계 부모를 가리키도록)
        $response = $this->postJson("/api/comments/{$firstReply->id}/replies", [
            'content' => '2단계 대댓글',
        ]);

        // 2단계 대댓글도 1단계 대댓글처럼 원본 댓글의 자식으로 생성됨
        $response->assertCreated()
            ->assertJsonPath('data.parent_id', $this->parentComment->id);
    }

    public function test_존재하지_않는_댓글에_대댓글을_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson('/api/comments/99999/replies', [
            'content' => '대댓글 내용',
        ]);

        $response->assertNotFound();
    }

    public function test_대댓글_작성시_부모_댓글의_reply_count가_증가한다(): void
    {
        $this->actingAs($this->user, 'api');

        $initialCount = $this->parentComment->reply_count;

        $this->postJson("/api/comments/{$this->parentComment->id}/replies", [
            'content' => '대댓글 내용',
        ]);

        $this->parentComment->refresh();
        $this->assertEquals($initialCount + 1, $this->parentComment->reply_count);
    }

    public function test_삭제된_댓글에는_대댓글을_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $deletedComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'is_deleted' => true,
        ]);

        $response = $this->postJson("/api/comments/{$deletedComment->id}/replies", [
            'content' => '대댓글 내용',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', '삭제된 댓글에는 답글을 작성할 수 없습니다.');
    }
}
