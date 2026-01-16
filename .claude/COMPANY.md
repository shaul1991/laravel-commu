# Company Profile

소규모 기업. 자체 온프레미스 서버 운영.

## Infrastructure

- 온프레미스 단일 서버 환경
- OS: Ubuntu 24.04 LTS
- 클라우드 서비스 미사용 (자체 호스팅)

## Services

| 서비스     | 용도                        |
|------------|-----------------------------|
| Jenkins    | CI/CD                       |
| MinIO      | 오브젝트 스토리지 (S3 호환) |
| Sentry     | 에러 트래킹/모니터링        |
| PostgreSQL | 메인 데이터베이스           |
| Redis      | 캐시/세션/큐                |
| MongoDB    | 도큐먼트 스토리지           |

## Notes

- 상세 서버 스펙 및 URL: `.claude/COMPANY.local.md` 참조
- CI/CD: Jenkins 파이프라인
- 파일 스토리지: MinIO (S3 driver)
- 에러 모니터링: Sentry (Laravel SDK)
