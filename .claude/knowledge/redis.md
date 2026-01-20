# Redis 8 운영 가이드

## 개요
- **버전**: Redis 8
- **용도**: Cache, Queue, Session
- **컨테이너**: `redis:8-alpine`

## Docker 설정

### docker-compose.yml
```yaml
redis:
  image: redis:8-alpine
  command: redis-server --appendonly yes --maxmemory 512mb --maxmemory-policy allkeys-lru
  volumes:
    - redis-data:/data
  healthcheck:
    test: ["CMD", "redis-cli", "ping"]
    interval: 10s
    timeout: 5s
    retries: 5
  restart: unless-stopped
```

## Laravel 설정

### .env
```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 운영 명령어

### 접속
```bash
docker compose exec redis redis-cli
```

### 기본 명령어
```bash
# 모든 키 조회
KEYS *

# 키 패턴 조회
KEYS laravel_cache:*

# 메모리 사용량
INFO memory

# 키 개수
DBSIZE

# 캐시 전체 삭제
FLUSHDB
```

### Laravel Artisan
```bash
# 캐시 삭제
php artisan cache:clear

# 큐 모니터링
php artisan queue:monitor

# 실패한 작업 재시도
php artisan queue:retry all
```

## 모니터링

```bash
# 실시간 명령어 모니터링
docker compose exec redis redis-cli MONITOR

# 통계
docker compose exec redis redis-cli INFO stats

# 메모리
docker compose exec redis redis-cli INFO memory
```

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 연결 실패 | `redis-cli ping` | 컨테이너 재시작 |
| 메모리 부족 | `INFO memory` | maxmemory 조정, FLUSHDB |
| 큐 적체 | `LLEN queues:default` | Worker 수 증가 |
