# Caddy 운영 가이드

## 개요
- **용도**: 웹 서버, 리버스 프록시, 자동 HTTPS
- **컨테이너**: `caddy:alpine`

## Docker 설정

### docker-compose.yml
```yaml
caddy:
  image: caddy:alpine
  ports:
    - "80:80"
    - "443:443"
    - "443:443/udp"  # HTTP/3
  volumes:
    - ./docker/caddy/Caddyfile:/etc/caddy/Caddyfile:ro
    - caddy-data:/data
    - caddy-config:/config
    - ./public:/var/www/html/public:ro
  depends_on:
    app:
      condition: service_healthy
  restart: unless-stopped

volumes:
  caddy-data:
  caddy-config:
```

## Caddyfile 설정

### 기본 설정
```caddyfile
{
    email admin@example.com
    acme_ca https://acme-v02.api.letsencrypt.org/directory
}

example.com {
    root * /var/www/html/public
    
    # PHP-FPM
    php_fastcgi app:9000
    
    # 정적 파일
    file_server
    
    # Gzip
    encode gzip
    
    # 보안 헤더
    header {
        X-Frame-Options "SAMEORIGIN"
        X-Content-Type-Options "nosniff"
        X-XSS-Protection "1; mode=block"
        Referrer-Policy "strict-origin-when-cross-origin"
    }
    
    # 로그
    log {
        output file /var/log/caddy/access.log
    }
}
```

### Laravel 전용 설정
```caddyfile
example.com {
    root * /var/www/html/public
    
    php_fastcgi app:9000 {
        resolve_root_symlink
    }
    
    file_server
    encode gzip
    
    # Laravel 라우팅
    @notStatic {
        not path /build/* /storage/* *.js *.css *.ico *.png *.jpg *.svg *.woff2
    }
    rewrite @notStatic /index.php
    
    # 캐시 헤더
    @static path /build/* *.js *.css *.ico *.png *.jpg *.svg *.woff2
    header @static Cache-Control "public, max-age=31536000, immutable"
    
    # storage 심볼릭 링크
    handle_path /storage/* {
        root * /var/www/html/storage/app/public
        file_server
    }
}
```

### 개발 환경 (로컬 HTTPS)
```caddyfile
localhost {
    root * /var/www/html/public
    php_fastcgi app:9000
    file_server
    
    tls internal  # 자체 서명 인증서
}
```

### 멀티 도메인
```caddyfile
# API
api.example.com {
    reverse_proxy app:9000
}

# 웹앱
example.com {
    root * /var/www/html/public
    php_fastcgi app:9000
    file_server
}

# 리다이렉트
www.example.com {
    redir https://example.com{uri} permanent
}
```

## 운영 명령어

### 설정 검증
```bash
docker compose exec caddy caddy validate --config /etc/caddy/Caddyfile
```

### 설정 리로드
```bash
docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile
```

### 인증서 확인
```bash
docker compose exec caddy caddy trust
docker compose exec caddy ls -la /data/caddy/certificates/
```

### 로그 확인
```bash
docker compose logs caddy --tail=100 -f
```

## Nginx vs Caddy 비교

| 항목 | Nginx | Caddy |
|------|-------|-------|
| HTTPS | 수동 설정 | 자동 |
| 설정 문법 | 복잡 | 간단 |
| HTTP/3 | 별도 빌드 | 기본 지원 |
| 성능 | 빠름 | 빠름 |
| 메모리 | 적음 | 약간 더 사용 |

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 502 Bad Gateway | app 컨테이너 상태 | `docker compose ps` |
| 인증서 오류 | 도메인 DNS 확인 | A 레코드 확인 |
| 설정 적용 안됨 | 문법 검증 | `caddy validate` |
| 느린 응답 | 로그 확인 | PHP-FPM 설정 조정 |
