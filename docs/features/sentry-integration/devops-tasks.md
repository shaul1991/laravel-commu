# DevOps Tasks - Sentry Integration

[← 기능 개요로 돌아가기](./README.md)

## 개요
- **기능**: Sentry Integration
- **팀**: DevOps
- **상태**: 완료
- **Sentry**: Self-hosted
- **Organization**: home-shaul
- **Project ID**: 4

## Tasks

| # | Task | 담당자 | 상태 | 비고 |
|---|------|--------|------|------|
| 1 | Sentry 프로젝트 생성 | | 완료 | project=4 |
| 2 | DSN 발급 (Backend) | | 완료 | .env 설정됨 |
| 3 | DSN 발급 (Frontend) | | 완료 | Backend DSN 공유 |
| 4 | 알림 채널 설정 | | 대기 | Sentry 대시보드 |
| 5 | 알림 규칙 설정 | | 대기 | Sentry 대시보드 |
| 6 | 환경별 설정 분리 | | 완료 | .env.*.example |

## 상세 내용

### 1. Sentry 프로젝트 생성 ✅
- [x] Self-hosted Sentry 접속
- [x] 프로젝트 생성 (Project ID: 4)
- [x] Backend/Frontend 동일 프로젝트 사용

### 2. DSN 발급 (Backend) ✅
- [x] Backend DSN 발급 및 공유
- [x] `.env` 설정 완료

### 3. DSN 발급 (Frontend) ✅
- [x] Backend DSN 공유 사용 (`VITE_SENTRY_DSN="${SENTRY_LARAVEL_DSN}"`)
- [x] Source Map 업로드 설정 완료

### 4. 알림 채널 설정 (Sentry 대시보드)
**설정 경로:** Settings → Integrations

**Slack 연동:**
1. Integrations → Slack → Add Workspace
2. Slack 인증 후 채널 선택
3. Alert Rules에서 Slack 액션 추가

**Email 알림:**
1. Settings → Notifications
2. Workflow Notifications 활성화
3. 이메일 주소 확인

### 5. 알림 규칙 설정 (Sentry 대시보드)
**설정 경로:** Alerts → Create Alert Rule

**권장 규칙:**
| 규칙 | 조건 | 액션 |
|------|------|------|
| 새 에러 | `A new issue is created` | Slack/Email |
| 에러 급증 | `Number of events > 100 in 1 hour` | Slack/Email |
| Critical | `level:fatal OR level:error` + `is:unresolved` | 즉시 알림 |

**환경별 필터:**
```
environment:production
```

### 6. 환경별 설정 분리 ✅

| 환경 | 파일 | 설정 |
|------|------|------|
| local | `.env` | PII 전송, 샘플링 100% |
| staging | `.env.staging.example` | PII 전송, 샘플링 100% |
| production | `.env.production.example` | PII 비전송, 샘플링 조정 |

**샘플링 설정:**
```env
# Local/Staging (전체 수집)
SENTRY_SAMPLE_RATE=1.0
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0

# Production (비용 최적화)
SENTRY_SAMPLE_RATE=1.0
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_PROFILES_SAMPLE_RATE=0.1
```
