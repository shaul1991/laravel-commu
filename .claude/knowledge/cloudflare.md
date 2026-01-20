# Cloudflare 운영 가이드

## 개요
- **용도**: CDN, DNS, DDoS 방어, WAF
- **플랜**: Free / Pro / Business

## DNS 설정

### 기본 레코드
| 타입 | 이름 | 값 | 프록시 |
|------|------|-----|--------|
| A | @ | 서버 IP | 프록시됨 (주황색) |
| A | www | 서버 IP | 프록시됨 |
| CNAME | api | @ | 프록시됨 |
| MX | @ | mail.example.com | DNS only |
| TXT | @ | v=spf1... | DNS only |

### 프록시 모드
- **프록시됨 (주황색)**: Cloudflare CDN 경유, IP 숨김
- **DNS only (회색)**: 직접 연결, IP 노출

## SSL/TLS 설정

### 권장 설정
- **SSL 모드**: Full (strict)
- **최소 TLS 버전**: TLS 1.2
- **Always Use HTTPS**: 켜기
- **Automatic HTTPS Rewrites**: 켜기

### Caddy와 연동
```caddyfile
# Cloudflare 프록시 사용 시
example.com {
    # Cloudflare에서 이미 HTTPS 처리
    # Origin 서버는 Cloudflare Origin Certificate 사용
    tls /etc/ssl/cloudflare-origin.pem /etc/ssl/cloudflare-origin-key.pem
}
```

## 캐시 설정

### 캐시 규칙
| 경로 | 캐시 TTL | 설명 |
|------|----------|------|
| /build/* | 1년 | Vite 빌드 파일 |
| *.js, *.css | 1개월 | 정적 자산 |
| /api/* | 없음 | API 응답 |
| / | 2시간 | HTML |

### 캐시 무효화
```bash
# 전체 캐시 삭제
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
  -H "Authorization: Bearer {api_token}" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'

# 특정 URL만
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
  -H "Authorization: Bearer {api_token}" \
  -H "Content-Type: application/json" \
  --data '{"files":["https://example.com/style.css"]}'
```

## 보안 설정

### WAF 규칙
- **OWASP Core Ruleset**: 켜기
- **Rate Limiting**: API 엔드포인트에 적용

### Rate Limiting 예시
| 규칙 | 조건 | 제한 |
|------|------|------|
| 로그인 | /login | 5회/분 |
| API | /api/* | 100회/분 |
| 전체 | * | 1000회/분 |

### Firewall 규칙
```
# 특정 국가 차단
(ip.geoip.country in {"CN" "RU"}) -> Block

# 알려진 봇 허용
(cf.client.bot) -> Allow

# 관리자 페이지 IP 제한
(http.request.uri.path contains "/admin" and not ip.src in {1.2.3.4}) -> Block
```

## Page Rules (레거시) / Rules

### 리다이렉트
```
www.example.com/* -> https://example.com/$1 (301)
```

### 캐시 설정
```
example.com/build/* -> Cache Level: Cache Everything, Edge TTL: 1 month
example.com/api/* -> Cache Level: Bypass
```

## Laravel과 연동

### 실제 IP 가져오기
```php
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*';

protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

### Cloudflare IP 확인
```php
// CF-Connecting-IP 헤더 사용
$realIp = $request->header('CF-Connecting-IP') ?? $request->ip();
```

## 모니터링

### Analytics 확인 항목
- 요청 수 / 대역폭
- 캐시 적중률 (70%+ 권장)
- 위협 차단 수
- 성능 (TTFB)

### 알림 설정
- DDoS 공격 감지
- SSL 인증서 만료
- Origin 서버 다운

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 521 Error | Origin 서버 다운 | 서버 상태 확인 |
| 522 Error | 연결 시간 초과 | 방화벽, 포트 확인 |
| 523 Error | Origin 연결 불가 | DNS 설정 확인 |
| 524 Error | 타임아웃 | PHP 실행 시간 확인 |
| 526 Error | SSL 인증서 오류 | Origin 인증서 확인 |

### 디버깅
```bash
# Cloudflare 우회 테스트
curl -H "Host: example.com" http://{origin-ip}/

# 헤더 확인
curl -I https://example.com/
# cf-ray, cf-cache-status 확인
```
