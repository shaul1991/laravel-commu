<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenResult;

beforeEach(function () {
    // Passport 키 생성 (테스트 환경용)
    if (! file_exists(storage_path('oauth-private.key'))) {
        Artisan::call('passport:keys', ['--force' => true]);
    }
});

describe('Passport Setup Verification', function () {
    it('Passport 패키지가 설치되어 있다', function () {
        expect(class_exists(Passport::class))->toBeTrue();
    });

    it('Passport 마이그레이션 테이블이 존재한다', function () {
        // Passport 13에서는 oauth_personal_access_clients 테이블이 제거됨
        // grant_types JSON 컬럼으로 Personal Access Client 관리
        expect(Schema::hasTable('oauth_clients'))->toBeTrue();
        expect(Schema::hasTable('oauth_access_tokens'))->toBeTrue();
        expect(Schema::hasTable('oauth_refresh_tokens'))->toBeTrue();
        expect(Schema::hasTable('oauth_auth_codes'))->toBeTrue();
    });

    it('Personal Access Client를 생성할 수 있다', function () {
        // Personal Access Client 생성
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);

        // Personal Access Client 조회
        $client = Client::query()
            ->whereJsonContains('grant_types', 'personal_access')
            ->first();

        expect($client)->not->toBeNull();
        expect($client->name)->toBe('Test Personal Access Client');
    });

    it('사용자가 Personal Access Token을 발급받을 수 있다', function () {
        // Personal Access Client 생성
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);

        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // Personal Access Token 발급
        $tokenResult = $user->createToken('test-token');

        expect($tokenResult)->toBeInstanceOf(PersonalAccessTokenResult::class);
        expect($tokenResult->accessToken)->toBeString();
        expect($tokenResult->token)->not->toBeNull();
    });

    it('Access Token으로 인증된 API 요청을 할 수 있다', function () {
        // Personal Access Client 생성
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
        ]);

        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        // Personal Access Token 생성 후 실제 토큰으로 요청
        $tokenResult = $user->createToken('test-token');
        $accessToken = $tokenResult->accessToken;

        // 인증된 API 요청 (Bearer Token 사용)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
        ])->getJson('/api/auth/me');

        // ECS-169: auth:api 미들웨어 교체 완료
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    });
});

describe('Passport Token Configuration', function () {
    it('Access Token 만료 시간이 15분으로 설정되어 있다', function () {
        // config에서 token 만료 시간 확인
        $expiresIn = config('passport.tokens_expire_in');

        // 15분
        expect($expiresIn)->toBe(15);
    });

    it('Refresh Token 만료 시간이 7일로 설정되어 있다', function () {
        // config에서 refresh token 만료 시간 확인
        $expiresIn = config('passport.refresh_tokens_expire_in');

        // 7일 = 7 * 24 * 60 = 10080분
        expect($expiresIn)->toBe(10080);
    });
});
