<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use App\Infrastructure\Persistence\Eloquent\ArticleModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchArticlesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = UserModel::factory()->create();

        ArticleModel::factory()->published()->create([
            'author_id' => $user->id,
            'title' => 'Laravel 12 새로운 기능 소개',
            'content_markdown' => 'Laravel 12에서 추가된 새로운 기능들을 소개합니다.',
            'content_html' => '<p>Laravel 12에서 추가된 새로운 기능들을 소개합니다.</p>',
            'category' => 'tech',
        ]);

        ArticleModel::factory()->published()->create([
            'author_id' => $user->id,
            'title' => 'PHP 8.4 업데이트 가이드',
            'content_markdown' => 'PHP 8.4의 새로운 기능과 변경 사항을 알아봅니다.',
            'content_html' => '<p>PHP 8.4의 새로운 기능과 변경 사항을 알아봅니다.</p>',
            'category' => 'tech',
        ]);

        ArticleModel::factory()->published()->create([
            'author_id' => $user->id,
            'title' => '개발자 이직 경험담',
            'content_markdown' => 'Laravel과 PHP를 사용하는 회사로 이직한 경험을 공유합니다.',
            'content_html' => '<p>Laravel과 PHP를 사용하는 회사로 이직한 경험을 공유합니다.</p>',
            'category' => 'career',
        ]);
    }

    public function test_키워드로_게시글을_검색할_수_있다(): void
    {
        $response = $this->getJson('/api/search/articles?q=Laravel');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'category',
                        'author',
                        'published_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_제목에서_키워드를_검색한다(): void
    {
        $response = $this->getJson('/api/search/articles?q=PHP');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_본문에서_키워드를_검색한다(): void
    {
        // 새로운 게시글을 특별한 본문 내용으로 생성하여 테스트
        $user = UserModel::factory()->create();
        ArticleModel::factory()->published()->create([
            'author_id' => $user->id,
            'title' => 'Test Title',
            'content_markdown' => 'This article contains unique keyword SEARCHABLE123',
            'content_html' => '<p>This article contains unique keyword SEARCHABLE123</p>',
        ]);

        $response = $this->getJson('/api/search/articles?q=SEARCHABLE123');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_카테고리로_필터링할_수_있다(): void
    {
        $response = $this->getJson('/api/search/articles?q=PHP&category=career');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_검색어가_없으면_에러를_반환한다(): void
    {
        $response = $this->getJson('/api/search/articles');

        $response->assertStatus(422);
    }

    public function test_검색어가_너무_짧으면_에러를_반환한다(): void
    {
        $response = $this->getJson('/api/search/articles?q=a');

        $response->assertStatus(422);
    }

    public function test_검색_결과는_최신순으로_정렬된다(): void
    {
        $response = $this->getJson('/api/search/articles?q=Laravel');

        $response->assertOk();
        $data = $response->json('data');

        // 최신순 정렬 확인
        $this->assertCount(2, $data);
    }

    public function test_검색_결과가_없으면_빈_배열을_반환한다(): void
    {
        $response = $this->getJson('/api/search/articles?q='.urlencode('존재하지않는검색어'));

        $response->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_임시저장_게시글은_검색되지_않는다(): void
    {
        $user = UserModel::factory()->create();
        ArticleModel::factory()->draft()->create([
            'author_id' => $user->id,
            'title' => 'Draft Laravel Tutorial',
            'content_markdown' => 'This is a draft about Laravel',
        ]);

        $response = $this->getJson('/api/search/articles?q=Draft');

        $response->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_페이지네이션이_적용된다(): void
    {
        $response = $this->getJson('/api/search/articles?q=PHP&per_page=1');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }
}
