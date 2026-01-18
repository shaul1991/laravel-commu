#!/bin/bash
# ==========================================
# Blue-Green 배포 스크립트
# ==========================================

set -e

# 설정
PROJECT_NAME="laravel-commu"
DEPLOY_PATH="/var/www/laravel-commu"
STORAGE_PATH="/var/www/laravel-commu-storage"
BLUE_PORT="10000"
GREEN_PORT="10001"
HEALTH_CHECK_TIMEOUT=60
HEALTH_CHECK_INTERVAL=2
# php:8.4-fpm-alpine의 www-data UID/GID
WWW_DATA_UID=82
WWW_DATA_GID=82

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

# 현재 활성 컨테이너 확인
get_current_env() {
    if docker ps -q -f name="${PROJECT_NAME}-blue" | grep -q .; then
        echo "blue"
    elif docker ps -q -f name="${PROJECT_NAME}-green" | grep -q .; then
        echo "green"
    else
        echo "none"
    fi
}

# Storage 디렉토리 권한 설정
setup_storage_permissions() {
    log_info "Setting up storage directory permissions..."

    # storage 디렉토리 생성 (없는 경우)
    if [ ! -d "${STORAGE_PATH}" ]; then
        log_info "Creating storage directory: ${STORAGE_PATH}"
        mkdir -p "${STORAGE_PATH}"
    fi

    # storage 하위 디렉토리 생성
    local storage_dirs=(
        "app/public"
        "framework/cache"
        "framework/sessions"
        "framework/views"
        "logs"
    )

    for dir in "${storage_dirs[@]}"; do
        if [ ! -d "${STORAGE_PATH}/${dir}" ]; then
            mkdir -p "${STORAGE_PATH}/${dir}"
        fi
    done

    # 권한 설정 (www-data가 쓸 수 있도록)
    chown -R ${WWW_DATA_UID}:${WWW_DATA_GID} "${STORAGE_PATH}"
    chmod -R 775 "${STORAGE_PATH}"

    log_info "Storage permissions configured successfully"
}

# 헬스체크
health_check() {
    local port=$1
    local timeout=$HEALTH_CHECK_TIMEOUT
    local interval=$HEALTH_CHECK_INTERVAL
    local elapsed=0

    log_info "Health check on port ${port}..."

    while [ $elapsed -lt $timeout ]; do
        if curl -sf "http://localhost:${port}/up" > /dev/null 2>&1; then
            log_info "Health check passed!"
            return 0
        fi
        sleep $interval
        elapsed=$((elapsed + interval))
        echo -n "."
    done

    echo ""
    log_error "Health check failed after ${timeout} seconds"
    return 1
}

# 배포 실행
deploy() {
    local branch=${1:-master}

    log_info "Starting deployment..."
    log_info "Branch: ${branch}"

    # 현재 환경 확인
    local current_env=$(get_current_env)
    local target_env
    local target_port

    if [ "$current_env" == "blue" ]; then
        target_env="green"
        target_port=$GREEN_PORT
    else
        target_env="blue"
        target_port=$BLUE_PORT
    fi

    log_info "Current: ${current_env} -> Target: ${target_env}"

    # 코드 업데이트
    cd "${DEPLOY_PATH}"
    log_info "Pulling latest code..."
    git fetch origin
    git checkout ${branch}
    git pull origin ${branch}

    # 버전 태그 생성
    local version=$(git rev-parse --short HEAD)
    local timestamp=$(date +%Y%m%d-%H%M%S)
    local image_tag="${timestamp}-${version}"

    log_info "Building image: ${PROJECT_NAME}:${image_tag}"

    # Docker 이미지 빌드
    docker build \
        -f docker/Dockerfile.prod \
        -t "${PROJECT_NAME}:${image_tag}" \
        -t "${PROJECT_NAME}:latest" \
        .

    # Storage 디렉토리 권한 설정
    setup_storage_permissions

    # 기존 대상 컨테이너 중지
    log_info "Stopping old ${target_env} container..."
    docker stop "${PROJECT_NAME}-${target_env}" 2>/dev/null || true
    docker rm "${PROJECT_NAME}-${target_env}" 2>/dev/null || true

    # 새 컨테이너 시작
    log_info "Starting new ${target_env} container..."
    docker compose -f docker/docker-compose.prod.yml \
        --env-file "${DEPLOY_PATH}/.env.prod" \
        up -d "${target_env}"

    # 헬스체크
    if ! health_check $target_port; then
        log_error "Deployment failed! Rolling back..."
        docker stop "${PROJECT_NAME}-${target_env}" 2>/dev/null || true
        docker rm "${PROJECT_NAME}-${target_env}" 2>/dev/null || true
        exit 1
    fi

    # 트래픽 전환
    log_info "Switching traffic to ${target_env}..."
    "${DEPLOY_PATH}/deploy/switch-traffic.sh" $target_port

    # 버전 기록
    echo "${image_tag}" >> "${DEPLOY_PATH}/deploy/versions.log"
    tail -5 "${DEPLOY_PATH}/deploy/versions.log" > "${DEPLOY_PATH}/deploy/versions.tmp"
    mv "${DEPLOY_PATH}/deploy/versions.tmp" "${DEPLOY_PATH}/deploy/versions.log"

    # 마이그레이션 실행 (필요한 경우)
    log_info "Running migrations..."
    docker exec "${PROJECT_NAME}-${target_env}" php artisan migrate --force

    log_info "Deployment completed successfully!"
    log_info "Version: ${image_tag}"
    log_info "Active: ${target_env} (port ${target_port})"

    # 이전 이미지 정리 (최근 5개 유지)
    log_info "Cleaning up old images..."
    docker images "${PROJECT_NAME}" --format '{{.Tag}}' | \
        grep -v latest | \
        sort -r | \
        tail -n +6 | \
        xargs -r -I {} docker rmi "${PROJECT_NAME}:{}" 2>/dev/null || true
}

# 사용법
usage() {
    echo "Usage: $0 [branch]"
    echo "  branch: Git branch or tag to deploy (default: master)"
    exit 1
}

# 메인
if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
    usage
fi

deploy "$@"
