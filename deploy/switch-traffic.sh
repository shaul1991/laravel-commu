#!/bin/sh
# ==========================================
# 트래픽 전환 스크립트
# Blue-Green 배포에서 Caddy 트래픽 전환
# ==========================================

TARGET_PORT=$1
DEPLOY_DIR="/var/www/html/deploy"
CADDY_CONFIG="/etc/caddy/Caddyfile"

if [ -z "$TARGET_PORT" ]; then
    echo "Error: TARGET_PORT is required"
    exit 1
fi

echo "Switching traffic to port: $TARGET_PORT"

# 현재 포트 저장
echo "$TARGET_PORT" > "$DEPLOY_DIR/current-port.txt"

# Caddyfile에서 reverse_proxy 포트 변경
# localhost:10000 또는 localhost:10001을 TARGET_PORT로 변경
if [ -f "$CADDY_CONFIG" ]; then
    sed -i "s/localhost:1000[01]/localhost:$TARGET_PORT/g" "$CADDY_CONFIG"
    echo "Caddyfile updated successfully"
else
    echo "Warning: Caddyfile not found at $CADDY_CONFIG"
fi

echo "Traffic switch configuration completed"
echo "Note: Run 'caddy reload' on the host to apply changes"
