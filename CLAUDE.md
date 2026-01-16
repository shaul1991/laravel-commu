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
