# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 기반 커뮤니티 프로젝트. PHP 8.4, Tailwind CSS 4, Vite 7 사용.

## Commands

```bash
# 개발 환경 실행 (서버, 큐, 로그, Vite 동시 실행)
composer dev

# 프로젝트 초기 설정
composer setup

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

# Docker (Makefile 사용)
make up          # 컨테이너 시작
make down        # 컨테이너 중지
make logs        # 로그 확인
make sh          # WAS 컨테이너 접속
make migrate     # 마이그레이션
make test        # 테스트
make pint        # 코드 스타일
make xdebug-off      # 비활성화
make xdebug-debug    # IDE 스텝 디버깅
make xdebug-develop  # 향상된 에러 출력
make xdebug-coverage # 코드 커버리지
```

## Git Hooks

```bash
# Git hooks 설치
composer hooks:install
```

**pre-push**: Ollama가 실행 중일 때 push 전 로컬 CI 검사 수행
- 코드 스타일 (Pint)
- 단위 테스트
- 아키텍처 테스트

## Architecture

- **Routes**: `routes/web.php` (웹), `routes/console.php` (CLI 명령어)
- **Bootstrap**: `bootstrap/app.php`에서 라우팅, 미들웨어, 예외 처리 설정
- **Database**: PostgreSQL (docker)
- **Cache/Queue**: Redis (docker)
- **Storage**: MinIO (docker, S3 호환)
- **Frontend**: Vite + Tailwind CSS, 엔트리포인트는 `resources/css/app.css`, `resources/js/app.js`

## Database 규칙 (PostgreSQL)

### 약한 결합 (Soft Reference) 정책

Foreign Key 제약조건을 사용하지 않고 **약한 결합** 방식으로 테이블 간 관계를 구성합니다.

**이유**:
- 마이크로서비스 전환 시 유연성 확보
- 데이터 마이그레이션 용이
- 삭제/수정 시 cascade 문제 방지
- 데이터베이스 간 의존성 감소

**Migration 작성 규칙**:

```php
// ❌ 사용하지 않음
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');

// ✅ 약한 결합 방식 사용
$table->uuid('user_id')->comment('users 테이블의 id 참조');
$table->unsignedBigInteger('category_id')->comment('categories 테이블의 id 참조');
```

### SoftDeletes 필수 사용

모든 테이블에 `SoftDeletes`를 적용합니다.

**예외 케이스** (SoftDeletes 미적용):
- 중간 관계 테이블 (pivot tables)
- 로그 테이블 (insert only)
- 히스토리 테이블 (insert only)
- 임시 데이터 테이블

**Migration 작성**:

```php
// 일반 테이블
$table->softDeletes();

// Model에서 trait 사용
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

## Skill 사용 규칙

### /new-feature, /bugfix 스킬

상세 규칙은 각 스킬 파일 참조:
- `.claude/commands/new-feature.md`
- `.claude/commands/bugfix.md`

**핵심 규칙**:
- feature/bugfix 브랜치 생성하여 작업
- **직접 master push 금지** - 반드시 PR을 통해 머지
