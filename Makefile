# ==========================================
# Laravel Docker 개발 환경
# ==========================================

.PHONY: help dev up down build restart logs sh composer artisan test pint migrate fresh vite vite-build xdebug xdebug-off xdebug-debug xdebug-develop xdebug-coverage xdebug-profile passport-init

# 기본 명령어
help:
	@echo "사용 가능한 명령어:"
	@echo ""
	@echo "  개발:"
	@echo "    make dev         - 개발 환경 시작 (Docker + Vite)"
	@echo "    make vite        - Vite 개발 서버 (HMR)"
	@echo "    make vite-build  - 프론트엔드 빌드"
	@echo ""
	@echo "  Docker:"
	@echo "    make up          - 컨테이너 시작"
	@echo "    make down        - 컨테이너 중지"
	@echo "    make build       - 컨테이너 빌드"
	@echo "    make restart     - 컨테이너 재시작"
	@echo "    make logs        - 로그 확인"
	@echo "    make sh          - WAS 컨테이너 접속"
	@echo ""
	@echo "  Laravel:"
	@echo "    make composer      - Composer 명령 (예: make composer cmd='install')"
	@echo "    make artisan       - Artisan 명령 (예: make artisan cmd='migrate')"
	@echo "    make migrate       - 마이그레이션 실행"
	@echo "    make fresh         - DB 초기화 + 시딩"
	@echo "    make test          - 테스트 실행"
	@echo "    make pint          - 코드 스타일 정리"
	@echo "    make passport-init - Passport OAuth 키 및 클라이언트 초기화"
	@echo ""
	@echo "  Xdebug:"
	@echo "    make xdebug-off      - 비활성화"
	@echo "    make xdebug-debug    - IDE 스텝 디버깅"
	@echo "    make xdebug-develop  - 향상된 에러 출력"
	@echo "    make xdebug-coverage - 코드 커버리지"
	@echo "    make xdebug-profile  - 성능 분석"
	@echo "    make xdebug mode='debug,profile' - 커스텀 조합"

# ==========================================
# 개발 환경
# ==========================================

# Docker + Vite 동시 실행
dev:
	@make restart
	@echo "Vite 개발 서버 시작... (Ctrl+C로 종료)"
	npm run dev

vite:
	npm run dev

vite-build:
	npm run build

# ==========================================
# Docker 명령어
# ==========================================

up:
	cd docker && docker compose up -d

down:
	cd docker && docker compose down

build:
	cd docker && docker compose build

restart:
	cd docker && docker compose down && docker compose up -d

logs:
	cd docker && docker compose logs -f

sh:
	cd docker && docker compose exec was sh

# ==========================================
# Laravel 명령어
# ==========================================

composer:
	cd docker && docker compose exec was composer $(cmd)

artisan:
	cd docker && docker compose exec was php artisan $(cmd)

migrate:
	cd docker && docker compose exec was php artisan migrate

fresh:
	cd docker && docker compose exec was php artisan migrate:fresh --seed

test:
	cd docker && docker compose exec was php artisan test

pint:
	cd docker && docker compose exec was ./vendor/bin/pint

passport-init:
	@echo "Passport OAuth 키 생성..."
	cd docker && docker compose exec was php artisan passport:keys --force
	@echo "키 권한 설정..."
	cd docker && docker compose exec was chown www-data:www-data storage/oauth-private.key storage/oauth-public.key
	cd docker && docker compose exec was chmod 600 storage/oauth-private.key
	cd docker && docker compose exec was chmod 660 storage/oauth-public.key
	@echo "Personal Access Client 생성..."
	cd docker && docker compose exec was php artisan passport:client --personal --name="Local Personal Access Client" || true
	@echo "Passport 초기화 완료!"

# ==========================================
# Xdebug
# - off: 비활성화
# - develop: var_dump 개선, 에러 메시지 향상
# - debug: IDE 스텝 디버깅
# - coverage: 코드 커버리지 (PHPUnit)
# - profile: 성능 프로파일링
# - trace: 함수 호출 추적
# ==========================================

xdebug-off:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=off/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: off"

xdebug-debug:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=debug/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: debug (IDE 스텝 디버깅)"

xdebug-develop:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=develop/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: develop (향상된 에러 출력)"

xdebug-coverage:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=coverage/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: coverage (코드 커버리지)"

xdebug-profile:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=profile/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: profile (성능 분석)"

# 커스텀 모드 설정 (예: make xdebug mode='debug,profile')
xdebug:
	@sed -i '' 's/XDEBUG_MODE=.*/XDEBUG_MODE=$(mode)/' docker/.env
	cd docker && docker compose restart was
	@echo "Xdebug: $(mode)"
