# /blogs

기술 블로그 아티클을 작성하고 blogs.shaul.link에 게시한다.

## Arguments
- $ARGUMENTS: 주제 및 옵션
  - 주제 (필수): 아티클 주제
  - EP (필수): 블로그 API 엔드포인트 URL
  - TOKEN (필수): Bearer 인증 토큰

## API Specification

### Endpoint
```
POST https://blogs.shaul.link/api/articles
```

### Headers
```
Authorization: Bearer {TOKEN}
Content-Type: application/json
Accept: application/json
```

### Request Body
```json
{
  "title": "아티클 제목",
  "content": "Markdown 본문",
  "category": "tech | career | life",
  "tags": ["tag1", "tag2"],
  "status": "published | draft"
}
```

### Categories
| 카테고리 | 설명 |
|----------|------|
| `tech` | 기술/개발 관련 아티클 |
| `career` | 커리어/성장 관련 아티클 |
| `life` | 일상/라이프 관련 아티클 |

## Instructions

### Step 1: 인자 파싱
Arguments에서 다음을 추출한다:
1. **주제**: 아티클 주제 및 키워드
2. **EP**: API 엔드포인트 (예: `https://blogs.shaul.link/api/articles`)
3. **TOKEN**: Bearer 인증 토큰

### Step 2: 주제 분석
1. 입력된 주제와 키워드를 분석한다
2. 대상 독자 수준을 파악한다 (초급/중급/고급)
3. 아티클 유형을 결정한다:
   - **Tutorial**: 단계별 가이드
   - **Deep Dive**: 기술 심층 분석
   - **Comparison**: 기술 비교
   - **Best Practice**: 실무 팁
   - **Case Study**: 실제 적용 사례
   - **Troubleshooting**: 문제 해결 가이드
4. 카테고리를 결정한다 (tech/career/life)

### Step 3: 정보 수집
1. Context7 MCP 서버를 활용하여 최신 문서를 조회한다
   ```
   resolve-library-id: 라이브러리 ID 조회
   query-docs: 문서 내용 검색
   ```
2. WebSearch로 최신 트렌드 및 관련 자료를 검색한다
3. 필요시 codebase를 탐색하여 실제 적용 사례를 확인한다

### Step 4: 아티클 작성

#### 제목 작성 규칙
- 명확하고 구체적인 제목
- 검색 가능한 키워드 포함
- 예시:
  - "Laravel 12에서 Redis 캐싱 구현하기"
  - "Tailwind CSS 4 새로운 기능 완벽 가이드"
  - "PostgreSQL vs MySQL: 실무 비교"

#### 본문 작성 규칙
1. **코드 블록 필수 포함**: 기술 주제에 맞는 예제 코드
2. **단계별 설명**: 복잡한 개념은 순서대로 분해
3. **시각적 요소**: Mermaid 다이어그램, 표 활용
4. **실용적 팁**: 실무에서 바로 적용 가능한 내용

#### Markdown 본문 구조
```markdown
# 아티클 제목

## 개요
// 주제 소개 및 배경

## 본문
// 기술 내용, 코드 예시, 설명

## 결론
// 핵심 요약 및 활용 방안

## 참고 자료
// 출처 링크
```

### Step 5: 품질 검토
1. 기술적 정확성 확인
2. 코드 예시 실행 가능 여부
3. 문법 및 맞춤법 검토
4. 링크 유효성 확인

### Step 6: API로 게시
Bash tool을 사용하여 curl 명령어로 아티클을 게시한다.

**중요**: JSON 본문의 content 필드에 Markdown을 넣을 때 줄바꿈(`\n`)과 특수문자를 올바르게 이스케이프해야 한다.

```bash
curl 'https://blogs.shaul.link/api/articles' \
  -H 'accept: application/json' \
  -H 'authorization: Bearer {TOKEN}' \
  -H 'content-type: application/json' \
  --data-raw '{
    "title": "아티클 제목",
    "content": "Markdown 본문 (줄바꿈은 \\n으로)",
    "category": "tech",
    "tags": ["tag1", "tag2", "tag3"],
    "status": "published"
  }'
```

### Step 7: 결과 확인
API 응답을 확인하고 게시 결과를 사용자에게 보고한다:
- 성공 시: 아티클 ID, URL, 제목 출력
- 실패 시: 에러 메시지 출력 및 원인 분석

## Article Templates

### Tutorial 템플릿
```markdown
# {제목} 완벽 가이드

## 소개
{주제}란 무엇이고 왜 사용하는지 설명합니다.

## 사전 요구사항
- 요구사항 1
- 요구사항 2

## Step 1: 설치 및 설정
// 설치 과정

## Step 2: 기본 사용법
// 기본 코드 예시

## Step 3: 고급 기능
// 심화 내용

## 마무리
// 요약 및 다음 단계

## 참고 자료
- [공식 문서](url)
```

### Deep Dive 템플릿
```markdown
# {주제} 내부 동작 원리

## 개요
{주제}의 핵심 개념을 소개합니다.

## 아키텍처
// 전체 구조 설명 + 다이어그램

## 핵심 컴포넌트
### 컴포넌트 1
// 상세 설명

### 컴포넌트 2
// 상세 설명

## 동작 흐름
// 시퀀스 다이어그램 포함

## 성능 고려사항
// 최적화 팁

## 결론
// 핵심 요약

## 참고 자료
- [소스 코드](url)
- [공식 문서](url)
```

### Comparison 템플릿
```markdown
# {A} vs {B} 비교 분석

## 개요
두 기술의 등장 배경과 목적을 설명합니다.

## 비교 요약

| 항목 | {A} | {B} |
|------|-----|-----|
| 성능 | - | - |
| 학습 곡선 | - | - |
| 생태계 | - | - |
| 사용 사례 | - | - |

## 상세 비교

### 성능
// 벤치마크 결과

### 개발 경험
// DX 비교

### 생태계 및 커뮤니티
// 라이브러리, 지원 현황

## 언제 무엇을 선택할까?
- **{A} 선택 시**: 조건 나열
- **{B} 선택 시**: 조건 나열

## 결론
// 최종 권장사항

## 참고 자료
```

### Best Practice 템플릿
```markdown
# {주제} 베스트 프랙티스

## 소개
이 가이드에서 다루는 내용을 소개합니다.

## 1. DO: 권장 사항
### 1.1 팁 제목
// 설명 + 코드

### 1.2 팁 제목
// 설명 + 코드

## 2. DON'T: 피해야 할 것
### 2.1 안티패턴 제목
// 나쁜 예시 vs 좋은 예시

## 3. 체크리스트
- [ ] 항목 1
- [ ] 항목 2
- [ ] 항목 3

## 결론
// 요약

## 참고 자료
```

### Case Study 템플릿
```markdown
# {주제}: 실제 적용 사례

## 개요
프로젝트 배경과 목적을 설명합니다.

## 프로젝트 구조
// 디렉토리 구조, 아키텍처 다이어그램

## 핵심 구현
### 구현 1
// 코드 예시 + 설명

### 구현 2
// 코드 예시 + 설명

## 실제 활용 사례
### 사례 1
// 상세 설명

### 사례 2
// 상세 설명

## 결론
// 핵심 요약 및 교훈

## 참고 자료
```

### Troubleshooting 템플릿
```markdown
# {주제} 문제 해결 가이드

## 개요
자주 발생하는 문제들과 해결 방법을 정리합니다.

## 문제 1: 에러 메시지
### 증상
// 증상 설명

### 원인
// 원인 분석

### 해결 방법
// 해결 코드/명령어

## 문제 2: 에러 메시지
### 증상
### 원인
### 해결 방법

## 디버깅 팁
// 일반적인 디버깅 접근법

## 도움 받기
- [GitHub Issues](url)
- [Stack Overflow](url)

## 참고 자료
```

## MCP Tools
- **Context7**: 최신 라이브러리 문서 조회
  - `resolve-library-id`: 라이브러리 ID 확인
  - `query-docs`: 문서 내용 검색
- **WebSearch**: 관련 자료 검색
- **Bash**: API 호출 (curl)

## Workflow

```mermaid
flowchart TD
    A[/blogs 실행] --> B[인자 파싱]
    B --> C[주제 분석]
    C --> D{아티클 유형 결정}
    D -->|Tutorial| E1[Tutorial 템플릿]
    D -->|Deep Dive| E2[Deep Dive 템플릿]
    D -->|Comparison| E3[Comparison 템플릿]
    D -->|Best Practice| E4[Best Practice 템플릿]
    D -->|Case Study| E5[Case Study 템플릿]
    D -->|Troubleshooting| E6[Troubleshooting 템플릿]
    E1 & E2 & E3 & E4 & E5 & E6 --> F[정보 수집]
    F --> G[Context7 문서 조회]
    F --> H[WebSearch 검색]
    G & H --> I[아티클 작성]
    I --> J[품질 검토]
    J --> K[API로 게시]
    K --> L{게시 결과}
    L -->|성공| M[URL 출력]
    L -->|실패| N[에러 분석]
```

## Example

### 기본 사용
```
/blogs Laravel Queue 튜토리얼

EP : https://blogs.shaul.link/api/articles
TOKEN : eyJ0eXAiOiJKV1QiLCJhbGciOiJS...
```

### 비교 아티클
```
/blogs PostgreSQL vs MySQL 비교

EP : https://blogs.shaul.link/api/articles
TOKEN : eyJ0eXAiOiJKV1QiLCJhbGciOiJS...
```

### 프로젝트 사례
```
/blogs Claude Code Agents/Skills/MCP 활용 사례 정리

EP : https://blogs.shaul.link/api/articles
TOKEN : eyJ0eXAiOiJKV1QiLCJhbGciOiJS...
```

## Output

### 성공 시
```
## 블로그 게시 완료

- **ID**: 019bd1d8-xxxx-xxxx-xxxx-xxxxxxxxxxxx
- **제목**: Laravel Queue 완벽 가이드
- **URL**: https://blogs.shaul.link/articles/{slug}
- **카테고리**: tech
- **태그**: laravel, queue, redis
- **상태**: published
```

### 실패 시
```
## 블로그 게시 실패

- **에러**: 401 Unauthorized
- **원인**: 토큰이 만료되었거나 유효하지 않음
- **조치**: 새로운 토큰을 발급받아 다시 시도
```

## Notes
- 모든 코드 예시는 실행 가능해야 함
- 최신 버전 기준으로 작성 (Context7 활용)
- 저작권 준수: 출처 명시 필수
- 한국어로 작성 (기술 용어는 영문 병기 가능)
- JSON content 필드의 줄바꿈은 `\n`으로 이스케이프
- 토큰은 세션 기반이므로 만료 시 재발급 필요
