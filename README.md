# Blogs

Laravel 12 기반의 커뮤니티 블로그 플랫폼입니다.

## Tech Stack

| Category | Technology |
|----------|------------|
| **Backend** | PHP 8.4, Laravel 12 |
| **Frontend** | Blade, Tailwind CSS 4, Vite 7 |
| **Database** | PostgreSQL, MongoDB |
| **Cache/Queue** | Redis |
| **Storage** | MinIO (S3 Compatible) |
| **Authentication** | Laravel Passport (OAuth2) |
| **Testing** | Pest, Playwright |
| **Monitoring** | Sentry |

## Architecture

### Domain-Driven Design (DDD)

프로젝트는 DDD 패턴을 따르며, 다음과 같은 레이어로 구성됩니다:

```
app/
├── Application/           # Use Cases (비즈니스 로직 조합)
│   └── Article/
├── Domain/                # 핵심 비즈니스 로직
│   ├── Core/              # 도메인 엔티티 및 Value Objects
│   │   ├── Article/
│   │   ├── Comment/
│   │   ├── User/
│   │   ├── Tag/
│   │   ├── Notification/
│   │   └── Shared/
│   └── Aggregator/        # 크로스 도메인 서비스
│       ├── ArticleFeed/
│       ├── SocialGraph/
│       └── Search/
├── Infrastructure/        # 인프라 구현체
│   ├── Persistence/       # Eloquent 리포지토리
│   └── Services/          # 외부 서비스 구현
├── Http/                  # 컨트롤러 및 Request
│   ├── Controllers/
│   └── Requests/
└── Providers/             # 서비스 프로바이더
```

### Database

- **Soft Reference 정책**: Foreign Key 제약조건을 사용하지 않고 약한 결합으로 테이블 관계 구성
- **SoftDeletes 필수**: 모든 테이블에 SoftDeletes 적용 (pivot/log 테이블 제외)

## Features

### Authentication
- OAuth2 소셜 로그인 (Google, GitHub 등)
- Laravel Passport 기반 API 토큰 인증
- 세션 관리 (다중 세션 조회/해제)
- 소셜 계정 연동/해제

### Article
- Markdown 에디터
- 임시 저장 (Draft)
- 발행/보관 상태 관리
- 카테고리 및 태그
- 조회수/좋아요
- 이미지 업로드 (MinIO S3)

### Social
- 사용자 팔로우/언팔로우
- 댓글 및 대댓글
- 댓글 좋아요
- 알림 시스템
- 사용자 프로필

### Search
- 아티클 검색
- 사용자 검색

## Getting Started

### Requirements

- PHP 8.4+
- Composer 2.x
- Node.js 20+
- Docker & Docker Compose

### Local Development

```bash
# 1. 저장소 클론
git clone <repository-url>
cd blogs

# 2. 프로젝트 초기 설정
composer setup

# 3. Docker 컨테이너 시작
make up

# 4. Passport OAuth 초기화
make passport-init

# 5. 개발 환경 실행 (서버 + 큐 + 로그 + Vite)
composer dev
```

### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| Nginx | 80 | 웹서버 |
| WAS | 9002 | PHP-FPM |
| PostgreSQL | 5432 | 메인 데이터베이스 |
| Redis | 6379 | 캐시/큐 |
| MongoDB | 27017 | NoSQL 데이터베이스 |
| MinIO | 9000, 9001 | S3 호환 스토리지 |

## Development

### Commands

```bash
# 개발 환경 실행 (서버, 큐, 로그, Vite 동시 실행)
composer dev

# 테스트 실행
composer test

# 단일 테스트 파일 실행
php artisan test tests/Feature/ExampleTest.php

# 단일 테스트 메서드 실행
php artisan test --filter test_method_name

# 코드 스타일 정리
./vendor/bin/pint

# 마이그레이션
php artisan migrate

# 프론트엔드 빌드
npm run build
```

### Makefile

```bash
make help          # 사용 가능한 명령어 확인

# Docker
make up            # 컨테이너 시작
make down          # 컨테이너 중지
make logs          # 로그 확인
make sh            # WAS 컨테이너 접속
make migrate       # 마이그레이션
make test          # 테스트
make pint          # 코드 스타일

# Xdebug
make xdebug-off      # 비활성화
make xdebug-debug    # IDE 스텝 디버깅
make xdebug-develop  # 향상된 에러 출력
make xdebug-coverage # 코드 커버리지
```

### Git Hooks

```bash
# Git hooks 설치
composer hooks:install
```

**pre-push**: Ollama가 실행 중일 때 push 전 로컬 CI 검사 수행
- 코드 스타일 (Pint)
- 단위 테스트
- 아키텍처 테스트

## API Endpoints

### Public Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/articles` | 아티클 목록 |
| GET | `/api/articles/{slug}` | 아티클 상세 |
| GET | `/api/articles/{slug}/comments` | 댓글 목록 |
| GET | `/api/search/articles` | 아티클 검색 |
| GET | `/api/search/users` | 사용자 검색 |
| GET | `/api/users/{username}` | 사용자 프로필 |
| GET | `/api/users/{username}/articles` | 사용자 아티클 |

### Auth Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/auth/oauth/{provider}/redirect` | OAuth 로그인 리다이렉트 |
| GET | `/api/auth/oauth/{provider}/callback` | OAuth 콜백 |
| POST | `/api/auth/refresh` | 토큰 갱신 |

### Protected Routes (인증 필요)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/logout` | 로그아웃 |
| GET | `/api/auth/me` | 내 정보 |
| GET | `/api/auth/sessions` | 세션 목록 |
| DELETE | `/api/auth/sessions/{id}` | 세션 해제 |
| GET | `/api/articles/drafts` | 내 임시글 |
| POST | `/api/articles` | 아티클 생성 |
| PUT | `/api/articles/{slug}` | 아티클 수정 |
| DELETE | `/api/articles/{slug}` | 아티클 삭제 |
| POST | `/api/articles/{slug}/like` | 좋아요 |
| POST | `/api/articles/{slug}/publish` | 발행 |
| POST | `/api/images/upload` | 이미지 업로드 |
| POST | `/api/articles/{slug}/comments` | 댓글 작성 |
| POST | `/api/comments/{comment}/replies` | 대댓글 작성 |
| PUT | `/api/comments/{comment}` | 댓글 수정 |
| DELETE | `/api/comments/{comment}` | 댓글 삭제 |
| POST | `/api/comments/{comment}/like` | 댓글 좋아요 |
| PUT | `/api/users/me` | 프로필 수정 |
| POST | `/api/users/{username}/follow` | 팔로우 |
| GET | `/api/notifications` | 알림 목록 |
| GET | `/api/notifications/unread-count` | 읽지 않은 알림 수 |
| POST | `/api/notifications/read-all` | 전체 읽음 처리 |

## Project Structure

```
.
├── app/                    # 애플리케이션 코드
├── bootstrap/              # 프레임워크 부트스트랩
├── config/                 # 설정 파일
├── database/               # 마이그레이션 및 시더
├── docker/                 # Docker 설정
│   ├── docker-compose.yml
│   ├── nginx/
│   └── was/
├── public/                 # 웹 루트
├── resources/              # 뷰, CSS, JS
│   ├── css/
│   ├── js/
│   └── views/
├── routes/                 # 라우트 정의
│   ├── api.php
│   ├── web.php
│   └── console.php
├── scripts/                # 스크립트
├── storage/                # 스토리지
├── tests/                  # 테스트
├── .claude/                # Claude Code 설정
│   ├── agents/             # 에이전트 컨텍스트
│   └── commands/           # 스킬 명령어
├── CLAUDE.md               # Claude Code 지침
├── Makefile                # Make 명령어
├── composer.json           # PHP 의존성
└── package.json            # Node.js 의존성
```

## License

This project is proprietary software.
