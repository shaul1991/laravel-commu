<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DraftArticleTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
    }

    public function test_사용자는_자신의_임시저장글_목록을_조회할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        // Create drafts
        ArticleModel::factory()->count(3)->create([
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // Create published articles (should not appear)
        ArticleModel::factory()->count(2)->create([
            'author_id' => $this->user->id,
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/articles/drafts');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'category',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_인증되지_않은_사용자는_임시저장글_목록을_조회할_수_없다(): void
    {
        $response = $this->getJson('/api/articles/drafts');

        $response->assertUnauthorized();
    }

    public function test_다른_사용자의_임시저장글은_보이지_않는다(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = UserModel::factory()->create();

        // Other user's drafts
        ArticleModel::factory()->count(2)->create([
            'author_id' => $otherUser->id,
            'status' => 'draft',
        ]);

        // My drafts
        ArticleModel::factory()->create([
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/articles/drafts');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_임시저장글_목록은_최신순으로_정렬된다(): void
    {
        Sanctum::actingAs($this->user);

        $old = ArticleModel::factory()->create([
            'author_id' => $this->user->id,
            'status' => 'draft',
            'updated_at' => now()->subDay(),
        ]);

        $new = ArticleModel::factory()->create([
            'author_id' => $this->user->id,
            'status' => 'draft',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/articles/drafts');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals($new->uuid, $data[0]['id']);
        $this->assertEquals($old->uuid, $data[1]['id']);
    }

    public function test_임시저장글을_발행할_수_있다(): void
    {
        Sanctum::actingAs($this->user);

        $draft = ArticleModel::factory()->create([
            'author_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/articles/{$draft->slug}/publish");

        $response->assertOk()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.slug', $draft->slug);

        $this->assertDatabaseHas('articles', [
            'id' => $draft->id,
            'status' => 'published',
        ]);
    }

    public function test_다른_사용자의_임시저장글은_발행할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = UserModel::factory()->create();
        $draft = ArticleModel::factory()->create([
            'author_id' => $otherUser->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/articles/{$draft->slug}/publish");

        $response->assertForbidden();
    }

    public function test_이미_발행된_글은_다시_발행할_수_없다(): void
    {
        Sanctum::actingAs($this->user);

        $published = ArticleModel::factory()->create([
            'author_id' => $this->user->id,
            'status' => 'published',
        ]);

        $response = $this->postJson("/api/articles/{$published->slug}/publish");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Article is already published');
    }
}
