<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    private string $token;

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

        $this->user = UserModel::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'username' => 'honggildong',
            'password' => Hash::make('Password123!'),
        ]);

        $this->token = $this->user->createToken('auth-token')->accessToken;
    }

    #[Test]
    public function authenticated_user_can_create_article(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Laravel 12 새로운 기능 소개',
                'content' => '# Laravel 12\n\nLaravel 12는 많은 새로운 기능을 제공합니다.',
                'category' => 'tech',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'content_html',
                    'category',
                    'status',
                    'view_count',
                    'like_count',
                    'author' => [
                        'id',
                        'name',
                        'username',
                        'avatar_url',
                    ],
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Laravel 12 새로운 기능 소개')
            ->assertJsonPath('data.category', 'tech')
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('articles', [
            'title' => 'Laravel 12 새로운 기능 소개',
            'category' => 'tech',
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_draft_article(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => '임시 저장 글',
                'content' => '아직 작성 중인 글입니다.',
                'category' => 'tech',
                'is_draft' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);

        $this->assertDatabaseHas('articles', [
            'title' => '임시 저장 글',
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function article_creation_fails_with_missing_title(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'content' => '본문만 있는 글',
                'category' => 'tech',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function article_creation_fails_with_missing_content(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => '제목만 있는 글',
                'category' => 'tech',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function article_creation_fails_with_invalid_category(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => '잘못된 카테고리 글',
                'content' => '본문입니다.',
                'category' => 'invalid_category',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    #[Test]
    public function article_creation_fails_with_too_long_title(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => str_repeat('가', 201), // 200자 초과
                'content' => '본문입니다.',
                'category' => 'tech',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_article(): void
    {
        $response = $this->postJson('/api/articles', [
            'title' => 'Test Article',
            'content' => 'Test content',
            'category' => 'tech',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function article_slug_is_auto_generated_from_title(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/articles', [
                'title' => 'Hello World Test',
                'content' => 'This is test content.',
                'category' => 'tech',
            ]);

        $response->assertStatus(201);

        $slug = $response->json('data.slug');
        $this->assertStringContainsString('hello-world-test', $slug);
    }
}
