#!/bin/bash
# ==========================================
# 롤백 스크립트
# ==========================================

set -e

# 설정
PROJECT_NAME="laravel-commu"
DEPLOY_PATH="/var/www/laravel-commu"
BLUE_PORT="10000"
GREEN_PORT="10001"

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 현재 활성 포트 확인
get_current_port() {
    if [ -f "${DEPLOY_PATH}/deploy/current-port.txt" ]; then
        cat "${DEPLOY_PATH}/deploy/current-port.txt"
    else
        echo "$BLUE_PORT"
    fi
}

# 롤백 실행
rollback() {
    local current_port=$(get_current_port)
    local rollback_port
    local rollback_env

    if [ "$current_port" == "$BLUE_PORT" ]; then
        rollback_port=$GREEN_PORT
        rollback_env="green"
    else
        rollback_port=$BLUE_PORT
        rollback_env="blue"
    fi

    log_info "Starting rollback..."
    log_info "Current: port ${current_port} -> Rollback to: ${rollback_env} (port ${rollback_port})"

    # 롤백 대상 컨테이너 상태 확인
    if ! docker ps -q -f name="${PROJECT_NAME}-${rollback_env}" | grep -q .; then
        log_error "No previous version available for rollback!"
        log_error "Container ${PROJECT_NAME}-${rollback_env} is not running."
        exit 1
    fi

    # 헬스체크
    log_info "Checking ${rollback_env} container health..."
    if ! curl -sf "http://localhost:${rollback_port}/up" > /dev/null 2>&1; then
        log_error "Rollback target is not healthy!"
        exit 1
    fi

    # 트래픽 전환
    log_info "Switching traffic to ${rollback_env}..."
    "${DEPLOY_PATH}/deploy/switch-traffic.sh" $rollback_port

    log_info "Rollback completed successfully!"
    log_info "Active: ${rollback_env} (port ${rollback_port})"
}

# 버전 목록 표시
list_versions() {
    log_info "Available versions:"
    if [ -f "${DEPLOY_PATH}/deploy/versions.log" ]; then
        cat -n "${DEPLOY_PATH}/deploy/versions.log"
    else
        log_warn "No version history found."
    fi
}

# 사용법
usage() {
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  (없음)    이전 버전으로 롤백"
    echo "  list      배포된 버전 목록 표시"
    echo ""
    exit 1
}

# 메인
case "${1:-}" in
    list)
        list_versions
        ;;
    -h|--help)
        usage
        ;;
    *)
        rollback
        ;;
esac
