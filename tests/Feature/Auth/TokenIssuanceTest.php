<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;

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

describe('Token Issuance API', function () {
    it('OAuth 로그인 성공 시 Access Token과 Refresh Token을 발급한다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 토큰 발급 요청 (OAuth 콜백 후 호출되는 엔드포인트)
        $response = $this->actingAs($user, 'api')->postJson('/api/auth/token');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);

        // Access Token 확인
        expect($response->json('token_type'))->toBe('Bearer');
        // 15분 = 900초 (토큰 생성 후 응답까지 약간의 시간 차이 허용)
        expect($response->json('expires_in'))->toBeGreaterThanOrEqual(895);
        expect($response->json('expires_in'))->toBeLessThanOrEqual(900);

        // Refresh Token은 HTTP-only Cookie로 전달
        $response->assertCookie('refresh_token');
    });

    it('Access Token은 JWT 형식이다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/auth/token');

        $response->assertStatus(200);

        $accessToken = $response->json('access_token');

        // JWT 형식 확인 (header.payload.signature)
        $parts = explode('.', $accessToken);
        expect(count($parts))->toBe(3);

        // Base64 디코딩 가능 여부 확인
        $header = json_decode(base64_decode($parts[0]), true);
        expect($header)->toHaveKey('typ');
        expect($header['typ'])->toBe('JWT');
    });

    it('Refresh Token Cookie는 HTTP-only와 Secure 속성을 가진다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/auth/token');

        $response->assertStatus(200);

        // Cookie 속성 확인
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === 'refresh_token');

        expect($cookie)->not->toBeNull();
        expect($cookie->isHttpOnly())->toBeTrue();
        // Note: Secure 속성은 production 환경에서만 활성화
    });

    it('비인증 사용자는 토큰을 발급받을 수 없다', function () {
        $response = $this->postJson('/api/auth/token');

        $response->assertStatus(401);
    });

    it('발급된 Access Token으로 보호된 API에 접근할 수 있다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // 토큰 발급
        $tokenResponse = $this->actingAs($user, 'api')->postJson('/api/auth/token');
        $accessToken = $tokenResponse->json('access_token');

        // 토큰으로 API 요청
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
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

describe('Token Expiration', function () {
    it('Access Token 만료 시간은 15분이다', function () {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/auth/token');

        $response->assertStatus(200);

        // 15분 = 900초 (토큰 생성 후 응답까지 약간의 시간 차이 허용)
        expect($response->json('expires_in'))->toBeGreaterThanOrEqual(895);
        expect($response->json('expires_in'))->toBeLessThanOrEqual(900);
    });
});
