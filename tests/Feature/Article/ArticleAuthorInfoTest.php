<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ECS-170: 아티클 상세 API에서 is_author 필드 반환 테스트
 */
final class ArticleAuthorInfoTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $author;

    private UserModel $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Passport 키 생성
        if (! file_exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys', ['--force' => true]);
        }

        // Personal Access Client 생성
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);

        $this->author = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440100',
            'name' => '아티클 작성자',
            'email' => 'author@example.com',
            'username' => 'author',
            'password' => Hash::make('Password123!'),
        ]);

        $this->otherUser = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440101',
            'name' => '다른 사용자',
            'email' => 'other@example.com',
            'username' => 'otheruser',
            'password' => Hash::make('Password123!'),
        ]);
    }

    #[Test]
    public function article_detail_includes_is_author_field_for_authenticated_author(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440100',
            'author_id' => $this->author->id,
            'title' => '작성자의 아티클',
            'slug' => 'author-article-123456',
            'content_markdown' => '# Test\n\n테스트 내용입니다.',
            'content_html' => '<h1>Test</h1><p>테스트 내용입니다.</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 10,
            'like_count' => 5,
            'published_at' => now(),
        ]);

        $token = $this->author->createToken('auth-token')->accessToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'is_author',
                    'author' => [
                        'id',
                        'name',
                        'username',
                    ],
                ],
            ])
            ->assertJsonPath('data.is_author', true);
    }

    #[Test]
    public function article_detail_includes_is_author_false_for_other_user(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440101',
            'author_id' => $this->author->id,
            'title' => '작성자의 아티클',
            'slug' => 'author-article-234567',
            'content_markdown' => '# Test\n\n테스트 내용입니다.',
            'content_html' => '<h1>Test</h1><p>테스트 내용입니다.</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 10,
            'like_count' => 5,
            'published_at' => now(),
        ]);

        $token = $this->otherUser->createToken('auth-token')->accessToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_author', false);
    }

    #[Test]
    public function article_detail_includes_is_author_false_for_guest(): void
    {
        $article = ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440102',
            'author_id' => $this->author->id,
            'title' => '작성자의 아티클',
            'slug' => 'author-article-345678',
            'content_markdown' => '# Test\n\n테스트 내용입니다.',
            'content_html' => '<h1>Test</h1><p>테스트 내용입니다.</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 10,
            'like_count' => 5,
            'published_at' => now(),
        ]);

        $response = $this->getJson("/api/articles/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_author', false);
    }

    #[Test]
    public function draft_articles_not_included_in_public_list(): void
    {
        // 발행된 아티클
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440103',
            'author_id' => $this->author->id,
            'title' => '발행된 아티클',
            'slug' => 'published-article-123456',
            'content_markdown' => '발행된 내용',
            'content_html' => '<p>발행된 내용</p>',
            'category' => 'tech',
            'status' => 'published',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => now(),
        ]);

        // 임시 저장 아티클
        ArticleModel::create([
            'uuid' => '660e8400-e29b-41d4-a716-446655440104',
            'author_id' => $this->author->id,
            'title' => '임시 저장 아티클',
            'slug' => 'draft-article-456789',
            'content_markdown' => '임시 내용',
            'content_html' => '<p>임시 내용</p>',
            'category' => 'tech',
            'status' => 'draft',
            'view_count' => 0,
            'like_count' => 0,
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200);

        $articles = $response->json('data');
        $this->assertCount(1, $articles);
        $this->assertEquals('발행된 아티클', $articles[0]['title']);
    }
}
