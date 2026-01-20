# /home-server

홈 서버 인프라 관리 및 문서화를 수행한다.

## Arguments
- $ARGUMENTS: 작업 유형 및 세부 정보
  - 작업 유형 (필수): 아래 작업 중 하나 선택
  - 세부 정보 (작업에 따라 필수)

## Reference Document
- **Confluence Page ID**: 2719840
- **URL**: https://laravel-commu.atlassian.net/wiki/spaces/DOCS/pages/2719840
- **제목**: 홈 서버 인프라 구축 가이드

> **중요**: 이 문서는 항상 최신 상태로 유지해야 한다. 변경사항이 발생하면 반드시 문서를 업데이트한다.

## 작업 유형

### 1. `조회` - 현재 구성 조회
현재 홈 서버 인프라 구성을 조회한다.

```
/home-server 조회

# 특정 섹션 조회
/home-server 조회 네트워크
/home-server 조회 서비스 목록
/home-server 조회 Blue-Green
```

### 2. `서비스 추가` - 새 서비스 등록
새로운 서비스를 추가하고 문서를 업데이트한다.

```
/home-server 서비스 추가

서비스명: gitea
내부 포트: 3000
도메인: gitea.shaul.link
Cloudflare 프록시: Proxied
설명: Git 저장소 서비스
```

### 3. `서비스 제거` - 서비스 삭제
서비스를 제거하고 문서를 업데이트한다.

```
/home-server 서비스 제거

서비스명: gitea
```

### 4. `설정 변경` - 구성 변경
기존 설정을 변경하고 문서를 업데이트한다.

```
/home-server 설정 변경

서비스명: blogs
변경 내용: 포트 10001 -> 10002
```

### 5. `이슈 추가` - 알려진 이슈 등록
새로운 이슈를 발견하여 문서에 등록한다.

```
/home-server 이슈 추가

이슈 ID: ISS-003
이슈 내용: SSL 인증서 갱신 실패
심각도: 높음
상태: 해결 중
해결 방안: Caddy 재시작 필요
```

### 6. `이슈 해결` - 이슈 상태 업데이트
이슈를 해결하고 문서를 업데이트한다.

```
/home-server 이슈 해결

이슈 ID: ISS-003
해결 방법: Caddy 서비스 재시작으로 해결
```

### 7. `피드백 추가` - 운영 피드백 기록
운영 경험 및 피드백을 기록한다.

```
/home-server 피드백 추가

피드백: GPU 가속이 Immich 성능에 큰 영향
조치: VA-API 설정 최적화
```

### 8. `Caddy 설정` - Caddy 설정 생성
새 서비스를 위한 Caddy 설정을 생성한다.

```
/home-server Caddy 설정

서비스명: gitea
도메인: gitea.shaul.link
내부 포트: 3000
Blue-Green: false
```

### 9. `Docker Compose` - Docker Compose 생성
서비스를 위한 Docker Compose 파일을 생성한다.

```
/home-server Docker Compose

서비스명: gitea
이미지: gitea/gitea:latest
포트: 3000:3000
볼륨: /opt/gitea/data:/data
환경변수: USER_UID=1000, USER_GID=1000
```

### 10. `개선 제안` - 개선 계획 추가
인프라 개선 계획을 추가한다.

```
/home-server 개선 제안

우선순위: 중간
항목: Grafana 대시보드 도입
현재 상태: Uptime Kuma만 사용
목표: Prometheus + Grafana 스택
비고: 상세 메트릭 수집
```

## Instructions

### Step 1: 참고 문서 조회

먼저 현재 Confluence 문서를 조회하여 최신 상태를 확인한다.

```
confluence_get_page(page_id='2719840')
```

### Step 2: 작업 유형 파악

Arguments에서 작업 유형을 파악한다:
- `조회`: 문서 내용 표시 (수정 없음)
- `서비스 추가/제거/설정 변경`: 서비스 관련 변경
- `이슈 추가/해결`: 이슈 관련 변경
- `피드백 추가`: 피드백 기록
- `Caddy 설정`: 설정 파일 생성
- `Docker Compose`: Docker 파일 생성
- `개선 제안`: 개선 계획 추가

### Step 3: 작업 수행

#### 조회 작업
1. 문서 내용을 파싱하여 요청된 섹션 정보 표시
2. 표 형식으로 보기 좋게 정리

#### 문서 업데이트 작업
1. 현재 문서 내용 확인
2. 변경 사항 적용 위치 파악
3. 수정된 Markdown 콘텐츠 생성
4. Confluence 문서 업데이트

```
confluence_update_page(
  page_id='2719840',
  title='홈 서버 인프라 구축 가이드',
  content='{수정된 Markdown 내용}'
)
```

#### 설정 파일 생성 작업
1. 요청된 설정에 맞는 파일 생성
2. 기존 패턴 참고하여 일관성 유지
3. 사용자에게 파일 내용 출력

### Step 4: 문서 업데이트 (변경 작업 시)

**중요**: 변경 작업 수행 시 반드시 Confluence 문서를 업데이트한다.

업데이트 시 주의사항:
- 기존 문서 구조 유지
- 마지막 업데이트 날짜 변경
- 관련 섹션만 수정 (다른 섹션 손상 금지)

### Step 5: 결과 보고

작업 완료 후 결과를 보고한다.

## 문서 섹션별 업데이트 가이드

### 서비스 추가 시 업데이트 위치

1. **Section 2.4**: 도메인별 라우팅 규칙 표에 추가
```markdown
| 도메인 | 내부 포트 | 서비스 | Cloudflare 프록시 |
| --- | --- | --- | --- |
| {새 도메인} | {포트} | {서비스명} | {Proxied/DNS only} |
```

2. **Section 3.1**: Docker 디렉토리 구조에 추가 (필요시)
```markdown
/opt/
├── {새 서비스}/
```

### 이슈 추가 시 업데이트 위치

**Section 12**: 알려진 이슈 표에 추가
```markdown
| ID | 이슈 | 심각도 | 상태 | 해결 방안 |
| --- | --- | --- | --- | --- |
| {ID} | {이슈 내용} | {심각도} | {상태} | {해결 방안} |
```

### 피드백 추가 시 업데이트 위치

**Section 13.1**: 운영 피드백 표에 추가
```markdown
| 날짜 | 피드백 | 조치 |
| --- | --- | --- |
| {YYYY-MM} | {피드백} | {조치} |
```

### 개선 계획 추가 시 업데이트 위치

**Section 11.1**: 인프라 개선 표에 추가
```markdown
| 우선순위 | 항목 | 현재 상태 | 목표 | 비고 |
| --- | --- | --- | --- | --- |
| {우선순위} | {항목} | {현재} | {목표} | {비고} |
```

## Caddy 설정 템플릿

### 기본 리버스 프록시
```
{도메인} {
    import remove_headers
    reverse_proxy localhost:{포트} {
        header_down -Server
        header_down -Via
        header_up Host {host}
        header_up X-Real-IP {remote_host}
        header_up X-Forwarded-For {remote_host}
        header_up X-Forwarded-Proto {scheme}
    }
}
```

### Blue-Green 배포
```
{도메인} {
    import remove_headers

    @blue header X-Slot blue
    @green header X-Slot green

    handle @blue {
        reverse_proxy localhost:{blue_port} {
            header_up Host {host}
            header_up X-Real-IP {remote_host}
            header_up X-Forwarded-For {remote_host}
            header_up X-Forwarded-Proto {scheme}
        }
    }

    handle @green {
        reverse_proxy localhost:{green_port} {
            header_up Host {host}
            header_up X-Real-IP {remote_host}
            header_up X-Forwarded-For {remote_host}
            header_up X-Forwarded-Proto {scheme}
        }
    }

    # DEFAULT_SLOT:{current_slot}
    handle {
        reverse_proxy localhost:{current_port} {
            header_up Host {host}
            header_up X-Real-IP {remote_host}
            header_up X-Forwarded-For {remote_host}
            header_up X-Forwarded-Proto {scheme}
        }
    }
}
```

## Docker Compose 템플릿

### 기본 서비스
```yaml
services:
  {서비스명}:
    image: {이미지}
    container_name: {서비스명}
    ports:
      - "{외부포트}:{내부포트}"
    environment:
      - {환경변수}
    volumes:
      - {볼륨 마운트}
    restart: unless-stopped
    networks:
      - app-network

networks:
  app-network:
    external: true
```

### Blue-Green 배포 서비스
```yaml
services:
  {서비스명}-blue:
    image: {이미지}
    container_name: {서비스명}-blue
    ports:
      - "{blue_port}:{내부포트}"
    environment:
      - APP_ENV=production
    volumes:
      - {볼륨}
    restart: unless-stopped
    networks:
      - app-network

  {서비스명}-green:
    image: {이미지}
    container_name: {서비스명}-green
    ports:
      - "{green_port}:{내부포트}"
    environment:
      - APP_ENV=production
    volumes:
      - {볼륨}
    restart: unless-stopped
    networks:
      - app-network

networks:
  app-network:
    external: true
```

## MCP Tools

- **Confluence**: confluence_get_page, confluence_update_page
- **Bash**: 설정 파일 생성/수정 시 활용

## Workflow

```mermaid
flowchart TD
    A[/home-server 실행] --> B[참고 문서 조회]
    B --> C{작업 유형 파악}

    C -->|조회| D[문서 내용 표시]
    D --> Z[결과 출력]

    C -->|서비스 추가| E[서비스 정보 수집]
    E --> F[문서에 서비스 추가]
    F --> G[Confluence 업데이트]
    G --> Z

    C -->|서비스 제거| H[서비스 정보 확인]
    H --> I[문서에서 서비스 제거]
    I --> G

    C -->|설정 변경| J[변경 사항 파악]
    J --> K[문서 내용 수정]
    K --> G

    C -->|이슈 추가/해결| L[이슈 정보 처리]
    L --> G

    C -->|피드백 추가| M[피드백 기록]
    M --> G

    C -->|Caddy 설정| N[템플릿 기반 생성]
    N --> O[설정 파일 출력]
    O --> Z

    C -->|Docker Compose| P[템플릿 기반 생성]
    P --> Q[Compose 파일 출력]
    Q --> Z

    C -->|개선 제안| R[개선 계획 추가]
    R --> G
```

## Example

### 현재 서비스 목록 조회
```
/home-server 조회 서비스 목록
```

**출력**:
```
## 홈 서버 서비스 목록

| 도메인 | 내부 포트 | 서비스 | Cloudflare 프록시 |
|--------|-----------|--------|-------------------|
| api-nest.shaul.link | 3100/3101 | NestJS API (Prod) | Proxied |
| blogs.shaul.link | 10001 | Laravel Blog (Prod) | Proxied |
| jenkins.shaul.link | 8888 | Jenkins CI/CD | Proxied |
| minio.shaul.link | 9000 | MinIO Object Storage | DNS only |
| immich.shaul.link | 2283 | Immich Photo Management | DNS only |
...
```

### 새 서비스 추가
```
/home-server 서비스 추가

서비스명: gitea
내부 포트: 3000
도메인: gitea.shaul.link
Cloudflare 프록시: Proxied
설명: Git 저장소 서비스
```

**출력**:
```
## 서비스 추가 완료

- **서비스명**: gitea
- **도메인**: gitea.shaul.link
- **내부 포트**: 3000
- **Cloudflare 프록시**: Proxied

### Confluence 문서 업데이트됨
- Section 2.4 (도메인별 라우팅 규칙) 에 추가

### 다음 단계
1. Caddy 설정 추가: `/home-server Caddy 설정 서비스명: gitea`
2. Docker Compose 생성: `/home-server Docker Compose 서비스명: gitea`
3. Cloudflare DNS 레코드 추가
```

### Caddy 설정 생성
```
/home-server Caddy 설정

서비스명: gitea
도메인: gitea.shaul.link
내부 포트: 3000
Blue-Green: false
```

**출력**:
```
## Caddy 설정 생성됨

gitea.shaul.link {
    import remove_headers
    reverse_proxy localhost:3000 {
        header_down -Server
        header_down -Via
        header_up Host {host}
        header_up X-Real-IP {remote_host}
        header_up X-Forwarded-For {remote_host}
        header_up X-Forwarded-Proto {scheme}
    }
}

위 설정을 `/etc/caddy/Caddyfile`에 추가하세요.
```

## Output

### 조회 성공 시
```
## 홈 서버 인프라 정보

{요청한 섹션 내용}

---
마지막 업데이트: 2026-01
문서 URL: https://laravel-commu.atlassian.net/wiki/spaces/DOCS/pages/2719840
```

### 업데이트 성공 시
```
## 문서 업데이트 완료

- **작업**: {작업 유형}
- **변경 내용**: {변경 요약}
- **업데이트 섹션**: {섹션 번호/이름}
- **문서 URL**: https://laravel-commu.atlassian.net/wiki/spaces/DOCS/pages/2719840

변경 사항이 Confluence에 반영되었습니다.
```

### 실패 시
```
## 작업 실패

- **오류**: {에러 메시지}
- **원인**: {원인 분석}
- **조치**: {권장 조치}
```

## Notes

- 모든 변경 작업은 Confluence 문서에 반영됨
- 대용량 서비스(MinIO, Immich 등)는 Cloudflare 프록시를 사용하지 않음 (300MB 제한)
- Blue-Green 배포는 Production 환경에서만 사용
- 문서 마지막 업데이트 날짜는 항상 현재 날짜로 갱신
- 문서 구조를 임의로 변경하지 않음 (기존 섹션 구조 유지)

## 홈 서버 주요 정보 요약

### 하드웨어
- CPU: AMD Ryzen 5 2400G (4코어 8스레드)
- RAM: 32GB DDR4
- GPU: AMD Radeon RX 580 8GB (Immich ML 가속용)
- Storage: 1TB NVMe + 400GB SSD + 11TB HDD

### 네트워크
- LAN: 192.168.0.26
- VPN: 10.0.0.1 (WireGuard)
- 도메인: *.shaul.link

### 주요 서비스
- **Web Apps**: Laravel Blog, NestJS API, Commu
- **DevOps**: Jenkins, Sentry, Uptime Kuma
- **Storage**: MinIO, Immich
- **Tools**: n8n, Vikunja, Penpot

### 데이터베이스
- PostgreSQL: 5432 (Prod) / 5433 (Dev)
- Redis: 6379 (Prod) / 6380 (Dev)
- MongoDB: 27017 (Prod) / 27018 (Dev)
