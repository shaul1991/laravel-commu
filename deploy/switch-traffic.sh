#!/bin/bash
# ==========================================
# Caddy 트래픽 전환 스크립트
# ==========================================

set -e

DEPLOY_PATH="/var/www/laravel-commu"
CADDY_CONFIG="/etc/caddy/Caddyfile"
NEW_PORT=$1

# 색상 정의
GREEN='\033[0;32m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

if [ -z "$NEW_PORT" ]; then
    echo "Usage: $0 <port>"
    exit 1
fi

log_info "Switching traffic to port ${NEW_PORT}..."

# Caddy 설정 업데이트
# upstream 포트를 새 포트로 변경
sed -i "s/localhost:1000[01]/localhost:${NEW_PORT}/g" "$CADDY_CONFIG"

# Caddy 리로드 (무중단)
log_info "Reloading Caddy..."
caddy reload --config "$CADDY_CONFIG"

# 현재 포트 기록
echo "$NEW_PORT" > "${DEPLOY_PATH}/deploy/current-port.txt"

log_info "Traffic switched to port ${NEW_PORT}"
