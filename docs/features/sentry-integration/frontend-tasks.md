# Frontend Tasks - Sentry Integration

[← 기능 개요로 돌아가기](./README.md)

## 개요
- **기능**: Sentry Integration
- **팀**: Frontend
- **상태**: 진행중
- **의존성**: DevOps (DSN 발급 필요)

## Tasks

| # | Task | 담당자 | 상태 | 비고 |
|---|------|--------|------|------|
| 1 | @sentry/browser 패키지 설치 | | 완료 | npm install |
| 2 | Sentry.init() 설정 | | 완료 | resources/js/app.js |
| 3 | 환경별 DSN 설정 | | 완료 | .env |
| 4 | Source Map 업로드 설정 | | 대기 | vite.config.js |
| 5 | 에러 바운더리 테스트 | | 대기 | |

## 상세 내용

### 1. @sentry/browser 패키지 설치 ✅
```bash
npm install @sentry/browser
```

### 2. Sentry.init() 설정 ✅
```javascript
// resources/js/app.js
import * as Sentry from '@sentry/browser';

Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    environment: import.meta.env.VITE_APP_ENV || 'local',
    tracesSampleRate: parseFloat(import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE || '1.0'),
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});
```

### 3. 환경별 DSN 설정 ✅
```env
# .env
VITE_APP_ENV="${APP_ENV}"
VITE_SENTRY_DSN="${SENTRY_LARAVEL_DSN}"
VITE_SENTRY_TRACES_SAMPLE_RATE="${SENTRY_TRACES_SAMPLE_RATE}"
```

### 4. Source Map 업로드 설정
```javascript
// vite.config.js
import { sentryVitePlugin } from '@sentry/vite-plugin';

export default defineConfig({
  plugins: [
    sentryVitePlugin({
      org: 'your-org',
      project: 'laravel-commu-frontend',
    }),
  ],
});
```

### 5. 에러 바운더리 테스트
- [ ] 의도적 에러 발생 테스트
- [ ] Sentry 대시보드에서 에러 확인
