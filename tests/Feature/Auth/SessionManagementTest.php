<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    // Passport 키 생성
    if (! file_exists(storage_path('oauth-private.key'))) {
        Artisan::call('passport:keys', ['--force' => true]);
    }

    // Personal Access Client 생성
    Artisan::call('passport:client', [
        '--personal' => true,
        '--name' => 'Test Personal Access Client',
    ]);
});

describe('Session List API', function () {
    it('활성 세션 목록을 조회할 수 있다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 여러 토큰 발급 (세션 생성)
        $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $this->actingAs($user, 'api')->postJson('/api/auth/token');

        // 세션 목록 조회
        $response = $this->actingAs($user, 'api')->getJson('/api/auth/sessions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'last_used_at',
                        'is_current',
                    ],
                ],
            ]);

        // 최소 3개의 access_token 세션이 있어야 함
        expect(count($response->json('data')))->toBeGreaterThanOrEqual(3);
    });

    it('비인증 사용자는 세션 목록을 조회할 수 없다', function () {
        $response = $this->getJson('/api/auth/sessions');

        $response->assertStatus(401);
    });
});

describe('Session Revoke API', function () {
    it('특정 세션을 해지할 수 있다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 토큰 발급
        $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $this->actingAs($user, 'api')->postJson('/api/auth/token');

        // 세션 목록 조회
        $sessionsResponse = $this->actingAs($user, 'api')->getJson('/api/auth/sessions');
        $sessions = $sessionsResponse->json('data');

        // 첫 번째 세션 해지
        $sessionId = $sessions[0]['id'];
        $response = $this->actingAs($user, 'api')->deleteJson("/api/auth/sessions/{$sessionId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Session revoked successfully',
            ]);

        // 세션 목록 다시 조회 - 해지된 세션이 없어야 함
        $newSessionsResponse = $this->actingAs($user, 'api')->getJson('/api/auth/sessions');
        $sessionIds = collect($newSessionsResponse->json('data'))->pluck('id')->toArray();

        expect($sessionIds)->not->toContain($sessionId);
    });

    it('다른 사용자의 세션은 해지할 수 없다', function () {
        /** @var UserModel $user1 */
        $user1 = UserModel::factory()->create();
        /** @var UserModel $user2 */
        $user2 = UserModel::factory()->create();

        // user1의 토큰 발급
        $this->actingAs($user1, 'api')->postJson('/api/auth/token');

        // user1의 세션 ID 가져오기
        $sessionsResponse = $this->actingAs($user1, 'api')->getJson('/api/auth/sessions');
        $sessionId = $sessionsResponse->json('data.0.id');

        // user2가 user1의 세션 해지 시도
        $response = $this->actingAs($user2, 'api')->deleteJson("/api/auth/sessions/{$sessionId}");

        $response->assertStatus(404);
    });

    it('존재하지 않는 세션은 해지할 수 없다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson('/api/auth/sessions/non-existent-id');

        $response->assertStatus(404);
    });
});

describe('Revoke All Sessions API', function () {
    it('현재 세션을 제외한 모든 세션을 해지할 수 있다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 여러 토큰 발급
        $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $this->actingAs($user, 'api')->postJson('/api/auth/token');

        // 세션 목록 조회 - 발급 전 세션 수 확인
        $beforeResponse = $this->actingAs($user, 'api')->getJson('/api/auth/sessions');
        $beforeCount = count($beforeResponse->json('data'));
        expect($beforeCount)->toBeGreaterThanOrEqual(3);

        // 모든 세션 해지
        // Note: actingAs()는 세션 기반 인증이므로 currentAccessToken()이 없음
        // 따라서 모든 토큰이 해지됨
        $response = $this->actingAs($user, 'api')->postJson('/api/auth/sessions/revoke-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All other sessions revoked successfully',
            ]);

        // 세션 목록 다시 조회 - 모든 토큰이 해지됨 (세션 기반 인증이므로)
        $sessionsResponse = $this->actingAs($user, 'api')->getJson('/api/auth/sessions');
        $afterCount = count($sessionsResponse->json('data'));

        // 모든 토큰이 해지됨 (currentAccessToken이 없으므로)
        expect($afterCount)->toBe(0);
    });

    it('비인증 사용자는 전체 세션 해지를 할 수 없다', function () {
        $response = $this->postJson('/api/auth/sessions/revoke-all');

        $response->assertStatus(401);
    });
});
