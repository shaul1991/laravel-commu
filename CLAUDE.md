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
- git worktree로 새 브랜치 생성하여 작업
- **직접 git push 금지** - 반드시 PR을 통해 머지
- Worktree 제거는 PR 머지 후

## Git Worktree

### Git Worktree란?

Git Worktree는 하나의 Git 저장소에서 **여러 브랜치를 동시에 체크아웃**할 수 있게 해주는 Git 기능입니다. 각 worktree는 독립된 작업 디렉토리를 가지며, 동일한 `.git` 저장소를 공유합니다.

### 목적 및 장점

1. **브랜치 전환 비용 제거**: stash/commit 없이 다른 브랜치 작업 가능
2. **병렬 작업**: 여러 기능을 동시에 개발
3. **컨텍스트 보존**: IDE 상태, 빌드 캐시, node_modules 등 유지
4. **긴급 버그 수정**: 현재 작업 중단 없이 hotfix 브랜치 작업
5. **코드 리뷰**: PR 검토 시 별도 worktree에서 테스트

### 기본 명령어

```bash
# Worktree 목록 확인
git worktree list

# 새 Worktree 생성 (기존 브랜치)
git worktree add ../laravel-commu-worktrees/feature-name feature/branch-name

# 새 Worktree 생성 (새 브랜치 생성과 함께)
git worktree add -b feature/new-branch ../laravel-commu-worktrees/new-feature

# Worktree 삭제
git worktree remove ../laravel-commu-worktrees/feature-name

# 삭제된 worktree 정리
git worktree prune
```

### 프로젝트 Worktree 구조

```
~/workspace/
├── laravel-commu/                           # 메인 (master)
└── laravel-commu-worktrees/
    ├── bugfix-ECS-125/                      # 버그 수정 브랜치
    └── feature-user-scenarios/              # 기능 개발 브랜치
```

### Claude Code와 함께 활용하기

1. **멀티 세션 병렬 작업**
   - 터미널 1: `cd ~/workspace/laravel-commu` → master 브랜치 작업
   - 터미널 2: `cd ~/workspace/laravel-commu-worktrees/feature-xxx` → 기능 개발
   - 각 터미널에서 별도의 Claude Code 세션 실행 가능

2. **긴급 버그 수정 시나리오**
   ```bash
   # 기능 개발 중 긴급 버그 발생
   # 현재 작업 중단 없이 새 worktree 생성
   git worktree add -b hotfix/critical-bug ../laravel-commu-worktrees/hotfix master

   # 새 터미널에서 Claude Code로 버그 수정
   cd ../laravel-commu-worktrees/hotfix
   claude
   ```

3. **PR 코드 리뷰**
   ```bash
   # PR 브랜치를 별도 worktree로 체크아웃
   git worktree add ../laravel-commu-worktrees/pr-review origin/feature/someone-pr

   # 해당 디렉토리에서 테스트 실행
   cd ../laravel-commu-worktrees/pr-review
   composer test
   ```

4. **Worktree별 환경 설정**
   - 각 worktree에 별도 `.env` 파일 설정 가능
   - DB 이름, 포트 등을 다르게 설정하여 충돌 방지

### 주의사항

- 같은 브랜치를 두 개의 worktree에서 체크아웃할 수 없음
- worktree 삭제 전 변경사항 commit 또는 stash 필요
- `.git` 파일(폴더 아님)이 각 worktree에 생성되어 메인 저장소 참조
