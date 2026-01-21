#!/bin/bash
#
# Claude Code Hook: MediaWiki 자동 동기화
# 프로젝트 변경 시 wiki.shaul.kr에 문서를 자동 업데이트합니다.
#
# 환경 변수:
#   WIKI_URL      - MediaWiki URL (예: https://wiki.shaul.kr)
#   WIKI_USER     - Bot 사용자명 (예: Username@BotName)
#   WIKI_PASS     - Bot 비밀번호 (Special:BotPasswords에서 생성)
#   WIKI_PAGE     - 업데이트할 Wiki 페이지 제목
#

set -e

# 설정
WIKI_URL="${WIKI_URL:-https://wiki.shaul.kr}"
WIKI_API="${WIKI_URL}/api.php"
WIKI_USER="${WIKI_USER:-}"
WIKI_PASS="${WIKI_PASS:-}"
WIKI_PAGE="${WIKI_PAGE:-Blogs/Changelog}"
PROJECT_DIR="${CLAUDE_PROJECT_DIR:-$(pwd)}"
COOKIE_FILE="/tmp/mediawiki_cookies_$$.txt"

# 색상 출력
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[Wiki]${NC} $1"; }
log_error() { echo -e "${RED}[Wiki Error]${NC} $1" >&2; }

cleanup() {
    rm -f "$COOKIE_FILE"
}
trap cleanup EXIT

# 환경 변수 검증
if [[ -z "$WIKI_USER" || -z "$WIKI_PASS" ]]; then
    log_error "WIKI_USER 또는 WIKI_PASS가 설정되지 않았습니다."
    exit 0
fi

# JSON에서 토큰 추출 (python3 사용)
extract_token() {
    local json="$1"
    local key="$2"
    echo "$json" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['query']['tokens']['${key}'])" 2>/dev/null
}

# 변경된 파일 목록 가져오기
get_changed_files() {
    cd "$PROJECT_DIR"
    git diff --name-only HEAD 2>/dev/null || echo ""
}

# 최근 커밋 메시지 가져오기
get_recent_commits() {
    cd "$PROJECT_DIR"
    git log --oneline -5 2>/dev/null || echo "No commits"
}

# Step 1: 로그인 토큰 가져오기
get_login_token() {
    local result
    result=$(curl -fsSL -X POST \
        -d "action=query" \
        -d "meta=tokens" \
        -d "type=login" \
        -d "format=json" \
        -c "$COOKIE_FILE" \
        -b "$COOKIE_FILE" \
        "$WIKI_API")

    extract_token "$result" "logintoken"
}

# Step 2: 로그인
wiki_login() {
    local login_token="$1"
    local result

    result=$(curl -fsSL -X POST \
        -d "action=login" \
        -d "lgname=$WIKI_USER" \
        -d "lgpassword=$WIKI_PASS" \
        --data-urlencode "lgtoken=$login_token" \
        -d "format=json" \
        -c "$COOKIE_FILE" \
        -b "$COOKIE_FILE" \
        "$WIKI_API")

    if echo "$result" | grep -q '"result":"Success"'; then
        return 0
    else
        log_error "로그인 실패: $result"
        return 1
    fi
}

# Step 3: CSRF 토큰 가져오기
get_csrf_token() {
    local result
    result=$(curl -fsSL -X POST \
        -d "action=query" \
        -d "meta=tokens" \
        -d "format=json" \
        -c "$COOKIE_FILE" \
        -b "$COOKIE_FILE" \
        "$WIKI_API")

    extract_token "$result" "csrftoken"
}

# Step 4: 섹션 추가 (페이지 끝에 추가)
append_section() {
    local csrf_token="$1"
    local page_title="$2"
    local section_text="$3"
    local summary="$4"

    local result
    result=$(curl -fsSL -X POST \
        -d "action=edit" \
        --data-urlencode "title=$page_title" \
        --data-urlencode "appendtext=$section_text" \
        -d "summary=$summary" \
        --data-urlencode "token=$csrf_token" \
        -d "format=json" \
        -d "bot=1" \
        -c "$COOKIE_FILE" \
        -b "$COOKIE_FILE" \
        "$WIKI_API")

    if echo "$result" | grep -q '"result":"Success"'; then
        return 0
    else
        log_error "섹션 추가 실패: $result"
        return 1
    fi
}

# 메인 실행
main() {
    log_info "MediaWiki 동기화 시작..."

    # 변경 사항 확인
    local changed_files
    changed_files=$(get_changed_files)

    if [[ -z "$changed_files" ]]; then
        log_info "변경된 파일이 없습니다. 건너뜁니다."
        exit 0
    fi

    # 로그인
    log_info "로그인 중..."
    local login_token
    login_token=$(get_login_token)

    if [[ -z "$login_token" ]]; then
        log_error "로그인 토큰을 가져올 수 없습니다."
        exit 0
    fi

    if ! wiki_login "$login_token"; then
        exit 0
    fi
    log_info "로그인 성공"

    # CSRF 토큰 가져오기
    local csrf_token
    csrf_token=$(get_csrf_token)

    if [[ -z "$csrf_token" ]]; then
        log_error "CSRF 토큰을 가져올 수 없습니다."
        exit 0
    fi

    # 변경 로그 생성
    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local project_name
    project_name=$(basename "$PROJECT_DIR")

    local recent_commits
    recent_commits=$(get_recent_commits)

    local update_content
    update_content="

== $timestamp ==
'''프로젝트''': $project_name

'''변경된 파일''':
$(echo "$changed_files" | sed 's/^/* /')

'''최근 커밋''':
<pre>
$recent_commits
</pre>

----"

    # 위키 페이지 업데이트
    log_info "페이지 업데이트 중: $WIKI_PAGE"
    if append_section "$csrf_token" "$WIKI_PAGE" "$update_content" "Claude Code 자동 업데이트: $project_name"; then
        log_info "Wiki 동기화 완료!"
    fi
}

main "$@"
