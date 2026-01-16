# QA 체크리스트: Backend Architecture - Domain Layer

> 도메인 기반 아키텍처(DDD) 품질 검증을 위한 QA 체크리스트

---

## 목차

1. [Core Domain 검증](#1-core-domain-검증)
2. [Aggregator 검증](#2-aggregator-검증)
3. [아키텍처 경계 검증](#3-아키텍처-경계-검증)
4. [단위 테스트 커버리지](#4-단위-테스트-커버리지)
5. [통합 테스트 시나리오](#5-통합-테스트-시나리오)
6. [성능 테스트](#6-성능-테스트)
7. [보안 검증](#7-보안-검증)

---

## 1. Core Domain 검증

### 1.1 User Domain

#### Entity: User

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 사용자 등록 시 UserRegistered 이벤트 발생 | PASS | |
| ✅ 이메일 인증 처리 | PASS | |
| ✅ 이메일 중복 인증 방지 | PASS | |
| ✅ 프로필 업데이트 시 UserUpdated 이벤트 발생 | PASS | |
| ✅ 아바타 URL 업데이트 | PASS | |
| ✅ 비밀번호 변경 | PASS | |
| ✅ 이메일 변경 시 인증 상태 초기화 | PASS | |

#### Value Objects

| Value Object | 테스트 케이스 | 상태 |
|-------------|--------------|------|
| Email | 유효한 이메일 생성 | ✅ PASS |
| Email | 도메인 추출 | ✅ PASS |
| Email | 이메일 비교 | ✅ PASS |
| Email | 무효한 이메일 예외 처리 (6개 케이스) | ✅ PASS |
| Username | 유효한 사용자명 생성 | ✅ PASS |
| Username | 사용자명 비교 | ✅ PASS |
| Username | 무효한 사용자명 예외 처리 (5개 케이스) | ✅ PASS |
| Password | 평문에서 해시 생성 | ✅ PASS |
| Password | 해시에서 생성 | ✅ PASS |
| Password | 비밀번호 검증 | ✅ PASS |
| Password | 약한 비밀번호 예외 처리 (4개 케이스) | ✅ PASS |

---

### 1.2 Article Domain

#### Entity: Article

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 발행된 아티클 생성 시 ArticlePublished 이벤트 발생 | PASS | |
| ✅ 임시저장 아티클 생성 시 이벤트 없음 | PASS | |
| ✅ 임시저장 아티클 발행 | PASS | |
| ✅ 이미 발행된 아티클 재발행 시 예외 | PASS | |
| ✅ 아티클 업데이트 | PASS | |
| ✅ 아카이브된 아티클 업데이트 시 예외 | PASS | |
| ✅ 조회수 증가 | PASS | |
| ✅ 좋아요 및 ArticleLiked 이벤트 | PASS | |
| ✅ 좋아요 취소 | PASS | |
| ✅ 수정 권한 확인 | PASS | |
| ✅ 삭제 권한 확인 | PASS | |
| ✅ 아티클 아카이브 | PASS | |

#### Value Objects

| Value Object | 테스트 케이스 | 상태 |
|-------------|--------------|------|
| Slug | 직접 생성 | ✅ PASS |
| Slug | 제목에서 슬러그 생성 (6개 케이스) | ✅ PASS |
| Slug | 타임스탬프 접미사 추가 | ✅ PASS |
| Slug | 슬러그 비교 | ✅ PASS |

---

### 1.3 Comment Domain

#### Entity: Comment

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 댓글 생성 시 CommentCreated 이벤트 발생 | PASS | |
| ✅ 답글(대댓글) 생성 | PASS | |
| ✅ 댓글 내용 업데이트 | PASS | |
| ✅ 댓글 좋아요 | PASS | |
| ✅ 댓글 좋아요 취소 | PASS | |
| ✅ 좋아요 수 0 이하 방지 | PASS | |
| ✅ 수정 권한 확인 | PASS | |
| ✅ 삭제 권한 확인 | PASS | |

---

### 1.4 Tag Domain

#### Entity: Tag

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 태그 생성 | PASS | |
| ✅ 슬러그 자동 생성 (7개 케이스) | PASS | |
| ✅ 아티클 수 증가 | PASS | |
| ✅ 아티클 수 감소 | PASS | |
| ✅ 아티클 수 0 이하 방지 | PASS | |

---

### 1.5 Notification Domain

#### Entity: Notification

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 알림 생성 | PASS | |
| ✅ 읽음 처리 | PASS | |
| ✅ 중복 읽음 처리 방지 | PASS | |
| ✅ 다양한 알림 유형 생성 (6개 유형) | PASS | |

#### Value Objects: NotificationType

| 테스트 케이스 | 상태 |
|--------------|------|
| ✅ 한글 라벨 반환 (6개 유형) | PASS |
| ✅ 아이콘 반환 (6개 유형) | PASS |
| ✅ 문자열 값에서 생성 | PASS |
| ✅ 문자열 값 반환 | PASS |

---

## 2. Aggregator 검증

### 2.1 SocialGraph Aggregator

#### Service: FollowService

| 테스트 케이스 | 상태 | 비고 |
|--------------|------|------|
| ✅ 팔로우 시 UserFollowed 이벤트 발생 | PASS | |
| ✅ 이미 팔로우한 사용자 중복 팔로우 방지 | PASS | |
| ✅ 자기 자신 팔로우 시 예외 | PASS | |
| ✅ 언팔로우 시 UserUnfollowed 이벤트 발생 | PASS | |
| ✅ 팔로우하지 않은 사용자 언팔로우 무시 | PASS | |
| ✅ 팔로우 상태 확인 | PASS | |
| ✅ 팔로워/팔로잉 통계 조회 | PASS | |

---

## 3. 아키텍처 경계 검증

### 3.1 의존성 규칙

| 검증 항목 | 상태 | 설명 |
|----------|------|------|
| ✅ Core Domain → Infrastructure 의존 금지 | PASS | |
| ✅ Core Domain → Eloquent 직접 의존 금지 | PASS | |
| ✅ Core Domain → Laravel Facade 의존 금지 | PASS | |
| ✅ Core Domain → HTTP 계층 의존 금지 | PASS | |
| ✅ Aggregator → Infrastructure 구현체 의존 금지 | PASS | |
| ✅ Aggregator → Eloquent Model 직접 의존 금지 | PASS | |

### 3.2 DDD 패턴 준수

| 검증 항목 | 상태 | 설명 |
|----------|------|------|
| ✅ Entity private constructor 패턴 | PASS | 팩토리 메서드 강제 |
| ✅ Entity factory 메서드 정의 | PASS | create/register/reconstitute |
| ✅ Value Object final class 선언 | PASS | 상속 금지 |
| ✅ Value Object 불변성 (readonly/private) | PASS | |
| ✅ Repository Interface만 Domain 계층에 정의 | PASS | |
| ✅ Domain Event interface 구현 | PASS | DomainEvent |
| ✅ Domain Event final class 선언 | PASS | |

---

## 4. 단위 테스트 커버리지

### 4.1 테스트 통계

| 항목 | 수치 |
|------|------|
| 총 테스트 수 | 116 |
| 통과 | 116 |
| 실패 | 0 |
| Assertions | 219+ |
| 실행 시간 | ~2초 |

### 4.2 도메인별 커버리지

| 도메인 | 테스트 수 | 커버리지 목표 |
|--------|----------|--------------|
| User Domain | 48 | ✅ 달성 |
| Article Domain | 21 | ✅ 달성 |
| Comment Domain | 8 | ✅ 달성 |
| Tag Domain | 11 | ✅ 달성 |
| Notification Domain | 18 | ✅ 달성 |
| SocialGraph Aggregator | 7 | ✅ 달성 |
| Architecture Tests | 6 | ✅ 달성 |

---

## 5. 통합 테스트 시나리오

### 5.1 사용자 등록 → 아티클 작성 흐름

| 시나리오 | 테스트 필요 | 상태 |
|----------|------------|------|
| 사용자 등록 | ⬜ TODO | |
| 이메일 인증 | ⬜ TODO | |
| 로그인 | ⬜ TODO | |
| 아티클 작성 (Draft) | ⬜ TODO | |
| 아티클 발행 | ⬜ TODO | |
| 팔로워 알림 발송 확인 | ⬜ TODO | |

### 5.2 아티클 상호작용 흐름

| 시나리오 | 테스트 필요 | 상태 |
|----------|------------|------|
| 아티클 조회 + 조회수 증가 | ⬜ TODO | |
| 아티클 좋아요 + 알림 | ⬜ TODO | |
| 댓글 작성 + 알림 | ⬜ TODO | |
| 대댓글 작성 + 알림 | ⬜ TODO | |

### 5.3 팔로우 흐름

| 시나리오 | 테스트 필요 | 상태 |
|----------|------------|------|
| 사용자 팔로우 + 알림 | ⬜ TODO | |
| 팔로워 피드 갱신 | ⬜ TODO | |
| 언팔로우 | ⬜ TODO | |

---

## 6. 성능 테스트

### 6.1 Entity 생성 성능

| 테스트 케이스 | 목표 | 상태 |
|--------------|------|------|
| Article 1,000개 생성 | < 100ms | ⬜ TODO |
| User 1,000개 생성 | < 100ms | ⬜ TODO |
| Comment 1,000개 생성 | < 50ms | ⬜ TODO |

### 6.2 Value Object 생성 성능

| 테스트 케이스 | 목표 | 상태 |
|--------------|------|------|
| Email 10,000개 검증 | < 50ms | ⬜ TODO |
| Slug 10,000개 생성 | < 100ms | ⬜ TODO |
| Password 100개 해싱 | < 1s | ⬜ TODO |

---

## 7. 보안 검증

### 7.1 Value Object 검증

| 검증 항목 | 상태 | 설명 |
|----------|------|------|
| ✅ Email 형식 검증 | PASS | 잘못된 형식 거부 |
| ✅ Username 특수문자 거부 | PASS | 영숫자, 언더스코어만 허용 |
| ✅ Password 정책 강제 | PASS | 8자+, 영문, 숫자, 특수문자 |
| ✅ Password 평문 노출 방지 | PASS | 해시만 저장 |

### 7.2 Entity 권한 검증

| 검증 항목 | 상태 | 설명 |
|----------|------|------|
| ✅ Article 수정 권한 | PASS | 작성자만 허용 |
| ✅ Article 삭제 권한 | PASS | 작성자만 허용 |
| ✅ Comment 수정 권한 | PASS | 작성자만 허용 |
| ✅ Comment 삭제 권한 | PASS | 작성자만 허용 |

### 7.3 비즈니스 로직 보호

| 검증 항목 | 상태 | 설명 |
|----------|------|------|
| ✅ 이미 발행된 아티클 재발행 방지 | PASS | AlreadyPublishedException |
| ✅ 아카이브된 아티클 수정 방지 | PASS | ArticleNotEditableException |
| ✅ 자기 자신 팔로우 방지 | PASS | CannotFollowSelfException |
| ✅ 좋아요 수 음수 방지 | PASS | 0 미만 불가 |

---

## 테스트 실행 명령어

```bash
# 전체 테스트 실행
php artisan test

# Unit 테스트만 실행
php artisan test tests/Unit

# Domain 테스트만 실행
php artisan test tests/Unit/Domain

# 아키텍처 테스트 실행
php artisan test tests/Architecture

# 커버리지 포함 실행
php artisan test --coverage
```

---

## 체크리스트 요약

| 구분 | 통과 | 미완료 | 총계 |
|------|------|--------|------|
| Core Domain | 62 | 0 | 62 |
| Aggregator | 7 | 0 | 7 |
| 아키텍처 검증 | 13 | 0 | 13 |
| 단위 테스트 | 116 | 0 | 116 |
| 통합 테스트 | 0 | 10 | 10 |
| 성능 테스트 | 0 | 5 | 5 |
| 보안 검증 | 11 | 0 | 11 |
| **총계** | **209** | **15** | **224** |

---

*문서 버전: 1.0*
*최종 업데이트: 2026-01-17*
