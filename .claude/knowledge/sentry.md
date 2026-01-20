# Sentry 운영 가이드

## 개요
- **용도**: 에러 모니터링, 성능 추적
- **연동**: Laravel + JavaScript

## Laravel 설정

### 설치
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=${SENTRY_LARAVEL_DSN}
```

### .env
```env
SENTRY_LARAVEL_DSN=https://xxx@xxx.ingest.sentry.io/xxx
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1
```

### config/sentry.php
```php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'release' => env('SENTRY_RELEASE'),
    'environment' => env('APP_ENV'),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),
    'send_default_pii' => false,
];
```

## MCP 도구 활용

### 이슈 조회
```
mcp__sentry__get_issue_details(issueUrl='https://xxx.sentry.io/issues/XXX')
```

### 이슈 검색
```
mcp__sentry__list_issues(organizationSlug='org', query='is:unresolved')
```

### 이슈 분석
```
mcp__sentry__analyze_issue_with_seer(issueUrl='https://xxx.sentry.io/issues/XXX')
```

## 에러 처리 패턴

### 수동 캡처
```php
try {
    // 위험한 코드
} catch (\Exception $e) {
    \Sentry\captureException($e);
    // 사용자에게 친절한 에러 표시
}
```

### 컨텍스트 추가
```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setUser(['id' => auth()->id()]);
    $scope->setTag('feature', 'payment');
    $scope->setExtra('order_id', $orderId);
});
```

### 브레드크럼
```php
\Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
    \Sentry\Breadcrumb::LEVEL_INFO,
    \Sentry\Breadcrumb::TYPE_USER,
    'user.action',
    'User clicked checkout'
));
```

## 릴리스 추적

### 배포 시 릴리스 등록
```bash
# Sentry CLI
sentry-cli releases new ${VERSION}
sentry-cli releases set-commits ${VERSION} --auto
sentry-cli releases finalize ${VERSION}
sentry-cli releases deploys ${VERSION} new -e production
```

## 알림 설정

### 권장 알림 규칙
| 조건 | 알림 |
|------|------|
| 새 이슈 발생 | Slack 즉시 |
| 이슈 재발 | Slack 즉시 |
| 이슈 급증 (1시간 100건+) | Slack + 이메일 |

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 이벤트 미전송 | DSN 확인 | .env 설정 검토 |
| 과다 이벤트 | Rate limit 확인 | sample_rate 조정 |
| 민감정보 노출 | PII 설정 확인 | send_default_pii=false |
