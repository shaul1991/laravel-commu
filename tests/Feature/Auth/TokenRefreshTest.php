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

describe('Token Refresh API', function () {
    it('유효한 Refresh Token으로 새 Access Token을 발급받을 수 있다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 먼저 토큰 발급
        $tokenResponse = $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $tokenResponse->assertStatus(200);

        // Refresh Token Cookie 가져오기
        $refreshTokenCookie = collect($tokenResponse->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'refresh_token');

        // Refresh Token으로 새 Access Token 요청 (call 메서드로 쿠키 직접 전달)
        $response = $this->call(
            method: 'POST',
            uri: '/api/auth/refresh',
            parameters: [],
            cookies: ['refresh_token' => $refreshTokenCookie->getValue()],
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);

        // 새 Access Token이 발급됨
        expect($response->json('token_type'))->toBe('Bearer');
        // 15분 = 900초 (토큰 생성 후 응답까지 약간의 시간 차이 허용)
        expect($response->json('expires_in'))->toBeGreaterThanOrEqual(895);
        expect($response->json('expires_in'))->toBeLessThanOrEqual(900);

        // 새 Refresh Token Cookie도 발급됨 (Refresh Token Rotation)
        $response->assertCookie('refresh_token');
    });

    it('Refresh Token이 없으면 401 에러를 반환한다', function () {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Refresh token is missing',
            ]);
    });

    it('유효하지 않은 Refresh Token으로 요청하면 401 에러를 반환한다', function () {
        $response = $this->call(
            method: 'POST',
            uri: '/api/auth/refresh',
            parameters: [],
            cookies: ['refresh_token' => 'invalid-token'],
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid refresh token',
            ]);
    });

    it('Refresh Token Rotation으로 기존 Refresh Token이 무효화된다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 첫 번째 토큰 발급
        $firstTokenResponse = $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $firstRefreshToken = collect($firstTokenResponse->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'refresh_token')
            ->getValue();

        // 첫 번째 Refresh Token으로 갱신
        $secondTokenResponse = $this->call(
            method: 'POST',
            uri: '/api/auth/refresh',
            parameters: [],
            cookies: ['refresh_token' => $firstRefreshToken],
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );
        $secondTokenResponse->assertStatus(200);

        // 기존 Refresh Token으로 다시 시도하면 실패
        $thirdResponse = $this->call(
            method: 'POST',
            uri: '/api/auth/refresh',
            parameters: [],
            cookies: ['refresh_token' => $firstRefreshToken],
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );

        $thirdResponse->assertStatus(401);
    });

    it('새로 발급된 Access Token은 유효하다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 토큰 발급
        $tokenResponse = $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $refreshTokenCookie = collect($tokenResponse->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'refresh_token');

        // Refresh로 새 토큰 발급
        $refreshResponse = $this->call(
            method: 'POST',
            uri: '/api/auth/refresh',
            parameters: [],
            cookies: ['refresh_token' => $refreshTokenCookie->getValue()],
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );

        $newAccessToken = $refreshResponse->json('access_token');

        // 새 토큰으로 API 요청
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$newAccessToken,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->uuid,
                    'email' => $user->email,
                ],
            ]);
    });
});
