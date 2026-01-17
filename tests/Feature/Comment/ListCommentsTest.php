<?php

declare(strict_types=1);

namespace Tests\Feature\Comment;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\CommentModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListCommentsTest extends TestCase
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

    public function test_게시글의_댓글_목록을_조회할_수_있다(): void
    {
        $comments = CommentModel::factory()->count(5)->create([
            'article_id' => $this->article->id,
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'author' => ['id', 'name', 'username', 'avatar_url'],
                        'like_count',
                        'reply_count',
                        'is_liked',
                        'replies',
                        'created_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_댓글은_최신순으로_정렬된다(): void
    {
        $oldComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'created_at' => now()->subDays(2),
        ]);

        $newComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals($newComment->id, $data[0]['id']);
        $this->assertEquals($oldComment->id, $data[1]['id']);
    }

    public function test_댓글은_좋아요순으로_정렬할_수_있다(): void
    {
        $popularComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'like_count' => 100,
        ]);

        $normalComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'like_count' => 10,
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments?sort=popular");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals($popularComment->id, $data[0]['id']);
    }

    public function test_대댓글이_포함된_댓글_목록을_조회할_수_있다(): void
    {
        $parentComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
        ]);

        $reply = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'parent_id' => $parentComment->id,
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments");

        $response->assertOk();
        $data = $response->json('data');

        // 최상위 댓글만 반환 (대댓글은 replies에 포함)
        $this->assertCount(1, $data);
        $this->assertCount(1, $data[0]['replies']);
        $this->assertEquals($reply->id, $data[0]['replies'][0]['id']);
    }

    public function test_존재하지_않는_게시글의_댓글_조회시_404를_반환한다(): void
    {
        $response = $this->getJson('/api/articles/non-existent-slug/comments');

        $response->assertNotFound();
    }

    public function test_페이지네이션이_적용된다(): void
    {
        CommentModel::factory()->count(25)->create([
            'article_id' => $this->article->id,
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments?per_page=10");

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonCount(10, 'data');
    }

    public function test_삭제된_댓글은_삭제된_댓글로_표시된다(): void
    {
        $deletedComment = CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'is_deleted' => true,
        ]);

        // 대댓글이 있어야 삭제된 댓글이 표시됨
        CommentModel::factory()->create([
            'article_id' => $this->article->id,
            'parent_id' => $deletedComment->id,
        ]);

        $response = $this->getJson("/api/articles/{$this->article->slug}/comments");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals('삭제된 댓글입니다.', $data[0]['content']);
    }
}
