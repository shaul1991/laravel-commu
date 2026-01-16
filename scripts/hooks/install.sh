#!/bin/bash

# =============================================================================
# Git Hooks 설치 스크립트
# 사용법: ./scripts/hooks/install.sh
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
HOOKS_DIR="$PROJECT_ROOT/.git/hooks"

echo "Git hooks 설치 중..."

# pre-push hook 설치
if [ -f "$SCRIPT_DIR/pre-push" ]; then
    cp "$SCRIPT_DIR/pre-push" "$HOOKS_DIR/pre-push"
    chmod +x "$HOOKS_DIR/pre-push"
    echo "✓ pre-push hook 설치 완료"
fi

echo ""
echo "설치 완료!"
echo ""
echo "Hook 동작:"
echo "  - pre-push: Ollama 실행 시 push 전 로컬 CI 검사 수행"
echo "              (Pint, Unit Tests, Architecture Tests)"
