<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private ArticleModel $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
        $this->article = ArticleModel::factory()->published()->create();
    }

    public function test_인증된_사용자는_댓글을_작성할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'content' => '좋은 글이네요!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'author' => ['id', 'name', 'username', 'avatar_url'],
                    'like_count',
                    'reply_count',
                    'is_liked',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.content', '좋은 글이네요!')
            ->assertJsonPath('data.author.id', $this->user->id);

        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'author_id' => $this->user->id,
            'content' => '좋은 글이네요!',
        ]);
    }

    public function test_인증되지_않은_사용자는_댓글을_작성할_수_없다(): void
    {
        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'content' => '댓글 내용',
        ]);

        $response->assertUnauthorized();
    }

    public function test_댓글_내용이_없으면_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'content' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_댓글_내용이_1000자를_초과하면_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'content' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_존재하지_않는_게시글에_댓글을_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson('/api/articles/non-existent-slug/comments', [
            'content' => '댓글 내용',
        ]);

        $response->assertNotFound();
    }

    public function test_임시저장_게시글에_댓글을_작성할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $draftArticle = ArticleModel::factory()->draft()->create();

        $response = $this->postJson("/api/articles/{$draftArticle->slug}/comments", [
            'content' => '댓글 내용',
        ]);

        $response->assertNotFound();
    }

    public function test_마크다운_문법을_사용할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $markdownContent = '**굵은 글씨**와 `코드`를 사용합니다.';

        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'content' => $markdownContent,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', $markdownContent);
    }
}
