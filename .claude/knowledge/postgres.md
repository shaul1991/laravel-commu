# PostgreSQL 18 운영 가이드

## 개요
- **버전**: PostgreSQL 18
- **용도**: Primary Database
- **컨테이너**: `postgres:18-alpine`

## Docker 설정

### docker-compose.yml
```yaml
postgres:
  image: postgres:18-alpine
  environment:
    POSTGRES_DB: ${DB_DATABASE}
    POSTGRES_USER: ${DB_USERNAME}
    POSTGRES_PASSWORD: ${DB_PASSWORD}
  volumes:
    - postgres-data:/var/lib/postgresql/data
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME}"]
    interval: 10s
    timeout: 5s
    retries: 5
  restart: unless-stopped
```

## 운영 명령어

### 접속
```bash
docker compose exec postgres psql -U ${DB_USERNAME} -d ${DB_DATABASE}
```

### 백업/복원
```bash
# 백업
docker compose exec -T postgres pg_dump -U ${DB_USERNAME} ${DB_DATABASE} | gzip > backup.sql.gz

# 복원
gunzip -c backup.sql.gz | docker compose exec -T postgres psql -U ${DB_USERNAME} -d ${DB_DATABASE}
```

### 유지보수
```bash
# VACUUM
docker compose exec postgres vacuumdb -U ${DB_USERNAME} -d ${DB_DATABASE} --analyze
```

## 모니터링 쿼리

```sql
-- 연결 수
SELECT count(*) FROM pg_stat_activity;

-- 슬로우 쿼리
SELECT pid, now() - query_start AS duration, query
FROM pg_stat_activity
WHERE (now() - query_start) > interval '5 seconds';

-- 테이블 크기
SELECT relname, pg_size_pretty(pg_total_relation_size(relid))
FROM pg_statio_user_tables ORDER BY pg_total_relation_size(relid) DESC LIMIT 10;
```

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 연결 거부 | `docker compose logs postgres` | 컨테이너 재시작 |
| 디스크 부족 | 테이블 크기 확인 | VACUUM FULL |
| 성능 저하 | EXPLAIN ANALYZE | 인덱스 추가 |
