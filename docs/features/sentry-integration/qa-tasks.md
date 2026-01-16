# QA Tasks - Sentry Integration

[← 기능 개요로 돌아가기](./README.md)

## 개요
- **기능**: Sentry Integration
- **팀**: QA
- **상태**: 완료
- **의존성**: Backend/Frontend 연동 완료 필요

## Tasks

| # | Task | 담당자 | 상태 | 비고 |
|---|------|--------|------|------|
| 1 | 에러 발생 시나리오 정의 | | 완료 | |
| 2 | Frontend 에러 캡처 검증 | | 완료 | window.testSentry() |
| 3 | Backend 에러 캡처 검증 | | 완료 | php artisan sentry:test |
| 4 | Sentry 대시보드 에러 확인 | | 완료 | |
| 5 | 알림 수신 테스트 | | 대기 | Sentry 대시보드 설정 필요 |

## 상세 내용

### 1. 에러 발생 시나리오 정의 ✅

| 시나리오 | 유형 | 예상 결과 | 테스트 결과 |
|----------|------|-----------|-------------|
| JS 런타임 에러 | Frontend | Sentry에 캡처 | ✅ 확인 |
| API 500 에러 | Backend | Sentry에 캡처 | ✅ 확인 |
| 404 에러 | Backend | 캡처 제외 (설정에 따라) | - |
| 인증 실패 | Backend | 캡처 제외 | - |

### 2. Frontend 에러 캡처 검증 ✅
```javascript
// 브라우저 콘솔에서 테스트 (개발 환경)
window.testSentry()
// → "Sentry Frontend Test Error" 발생 및 캡처 확인
```

### 3. Backend 에러 캡처 검증 ✅
```bash
php artisan sentry:test
# → Test event sent with ID: 2d2f05f6067443a9b0d4c3ec4b8542c8
```

### 4. Sentry 대시보드 에러 확인 ✅
- [x] Self-hosted Sentry 접속
- [x] 프로젝트 선택 (laravel-commu)
- [x] Issues 탭에서 에러 확인
- [x] 에러 상세 정보 확인 (스택트레이스, 컨텍스트)
- [x] Source Map으로 원본 코드 위치 확인

### 5. 알림 수신 테스트
- [ ] 에러 발생 시 Slack 알림 수신 확인 (Sentry 대시보드 설정 필요)
- [ ] 에러 발생 시 Email 알림 수신 확인 (Sentry 대시보드 설정 필요)
- [ ] 알림 내용 적절성 확인

> **Note**: 알림 설정은 Sentry 대시보드에서 수동으로 설정해야 합니다.
> 설정 가이드: [devops-tasks.md](./devops-tasks.md#4-알림-채널-설정-sentry-대시보드)
