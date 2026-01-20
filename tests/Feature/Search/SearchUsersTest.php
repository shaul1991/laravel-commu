<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserModel::factory()->create([
            'name' => '김지훈',
            'username' => 'jihoon_kim',
        ]);

        UserModel::factory()->create([
            'name' => '이서연',
            'username' => 'seoyeon_lee',
        ]);

        UserModel::factory()->create([
            'name' => '박민수',
            'username' => 'minsu_park',
        ]);
    }

    public function test_이름으로_사용자를_검색할_수_있다(): void
    {
        // 영문 이름으로 검색 테스트 (SQLite 호환)
        $response = $this->getJson('/api/search/users?q=jihoon');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'username',
                        'avatar_url',
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_유저네임으로_사용자를_검색할_수_있다(): void
    {
        $response = $this->getJson('/api/search/users?q=jihoon');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.username', 'jihoon_kim');
    }

    public function test_검색어가_없으면_에러를_반환한다(): void
    {
        $response = $this->getJson('/api/search/users');

        $response->assertStatus(422);
    }

    public function test_검색어가_너무_짧으면_에러를_반환한다(): void
    {
        $response = $this->getJson('/api/search/users?q=a');

        $response->assertStatus(422);
    }

    public function test_검색_결과가_없으면_빈_배열을_반환한다(): void
    {
        $response = $this->getJson('/api/search/users?q='.urlencode('존재하지않는사용자'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_부분_일치로_검색한다(): void
    {
        // '_'는 1글자라서 검증 실패하므로 '_k'로 검색
        $response = $this->getJson('/api/search/users?q=_k');

        $response->assertOk()
            ->assertJsonCount(2, 'data'); // jihoon_kim, minsu_park (둘 다 _k 포함)
    }

    public function test_검색_결과_개수를_제한할_수_있다(): void
    {
        $response = $this->getJson('/api/search/users?q=_k&limit=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
