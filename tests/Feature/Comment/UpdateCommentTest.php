<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class UpdateCommentTest extends TestCase
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

    public function test_작성자는_댓글을_수정할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/comments/{$this->comment->id}", [
            'content' => '수정된 댓글 내용입니다.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.content', '수정된 댓글 내용입니다.');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'content' => '수정된 댓글 내용입니다.',
        ]);
    }

    public function test_인증되지_않은_사용자는_댓글을_수정할_수_없다(): void
    {
        $response = $this->putJson("/api/comments/{$this->comment->id}", [
            'content' => '수정된 내용',
        ]);

        $response->assertUnauthorized();
    }

    public function test_작성자가_아닌_사용자는_댓글을_수정할_수_없다(): void
    {
        $otherUser = UserModel::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/comments/{$this->comment->id}", [
            'content' => '수정된 내용',
        ]);

        $response->assertForbidden();
    }

    public function test_존재하지_않는_댓글은_수정할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/comments/99999', [
            'content' => '수정된 내용',
        ]);

        $response->assertNotFound();
    }

    public function test_빈_내용으로_댓글을_수정할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/comments/{$this->comment->id}", [
            'content' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_1000자를_초과하는_내용으로_댓글을_수정할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/comments/{$this->comment->id}", [
            'content' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
