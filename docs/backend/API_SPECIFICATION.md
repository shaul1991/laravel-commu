# API Specification

## Overview

- **Base URL**: `/api/v1`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json`
- **Response Format**: JSON API 형식

## Common Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "입력값이 올바르지 않습니다.",
    "details": {
      "email": ["이메일 형식이 올바르지 않습니다."]
    }
  }
}
```

---

## 1. Authentication API

### 1.1 회원가입
```
POST /api/v1/auth/register
```

**Request Body:**
```json
{
  "name": "홍길동",
  "email": "user@example.com",
  "username": "honggildong",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "terms_agreed": true
}
```

**Validation Rules:**
- `name`: required, string, max:50
- `email`: required, email, unique:users
- `username`: required, string, min:3, max:20, regex:/^[a-zA-Z0-9_]+$/, unique:users
- `password`: required, string, min:8, confirmed, 영문/숫자/특수문자 포함
- `terms_agreed`: required, accepted

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "홍길동",
      "email": "user@example.com",
      "username": "honggildong",
      "avatar_url": null,
      "created_at": "2024-01-15T10:00:00Z"
    },
    "token": "1|abcdefghijklmnop..."
  }
}
```

---

### 1.2 로그인
```
POST /api/v1/auth/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "Password123!",
  "remember": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "홍길동",
      "email": "user@example.com",
      "username": "honggildong",
      "avatar_url": "https://...",
      "bio": "개발자입니다.",
      "created_at": "2024-01-15T10:00:00Z"
    },
    "token": "2|abcdefghijklmnop..."
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "이메일 또는 비밀번호가 올바르지 않습니다."
  }
}
```

---

### 1.3 로그아웃
```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "로그아웃되었습니다."
  }
}
```

---

### 1.4 비밀번호 재설정 요청
```
POST /api/v1/auth/forgot-password
```

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "비밀번호 재설정 링크가 이메일로 발송되었습니다."
  }
}
```

---

### 1.5 비밀번호 재설정
```
POST /api/v1/auth/reset-password
```

**Request Body:**
```json
{
  "token": "reset-token-here",
  "email": "user@example.com",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "비밀번호가 성공적으로 변경되었습니다."
  }
}
```

---

### 1.6 OAuth 로그인 (Google)
```
GET /api/v1/auth/oauth/google
```
Redirects to Google OAuth consent screen.

```
GET /api/v1/auth/oauth/google/callback
```
Handles OAuth callback and returns token.

---

### 1.7 OAuth 로그인 (GitHub)
```
GET /api/v1/auth/oauth/github
```
```
GET /api/v1/auth/oauth/github/callback
```

---

### 1.8 현재 사용자 정보
```
GET /api/v1/auth/me
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "홍길동",
    "email": "user@example.com",
    "username": "honggildong",
    "avatar_url": "https://...",
    "bio": "개발자입니다.",
    "location": "서울, 대한민국",
    "website": "https://example.com",
    "github": "honggildong",
    "followers_count": 123,
    "following_count": 45,
    "articles_count": 10,
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

---

## 2. Articles API

### 2.1 아티클 목록 조회
```
GET /api/v1/articles
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 (max: 50) |
| category | string | null | 카테고리 필터 (tech, career, life, review) |
| sort | string | latest | 정렬 (latest, popular, comments) |
| tag | string | null | 태그 필터 |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Laravel 12에서 새롭게 바뀐 기능들",
      "slug": "laravel-12-new-features",
      "excerpt": "Laravel 12가 출시되면서...",
      "thumbnail_url": "https://...",
      "category": "tech",
      "tags": ["Laravel", "PHP"],
      "author": {
        "id": 1,
        "name": "김개발",
        "username": "devkim",
        "avatar_url": "https://..."
      },
      "views_count": 1234,
      "comments_count": 23,
      "likes_count": 56,
      "published_at": "2024-01-15T10:00:00Z",
      "created_at": "2024-01-15T09:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

---

### 2.2 아티클 상세 조회
```
GET /api/v1/articles/{slug}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Laravel 12에서 새롭게 바뀐 기능들",
    "slug": "laravel-12-new-features",
    "content": "## 소개\n\nLaravel 12가 출시되었습니다...",
    "content_html": "<h2>소개</h2><p>Laravel 12가 출시되었습니다...</p>",
    "excerpt": "Laravel 12가 출시되면서...",
    "thumbnail_url": "https://...",
    "category": "tech",
    "tags": ["Laravel", "PHP", "백엔드"],
    "author": {
      "id": 1,
      "name": "김개발",
      "username": "devkim",
      "avatar_url": "https://...",
      "bio": "5년차 백엔드 개발자"
    },
    "views_count": 1234,
    "comments_count": 23,
    "likes_count": 56,
    "is_liked": false,
    "is_bookmarked": false,
    "published_at": "2024-01-15T10:00:00Z",
    "created_at": "2024-01-15T09:00:00Z",
    "updated_at": "2024-01-15T14:30:00Z"
  }
}
```

---

### 2.3 아티클 작성
```
POST /api/v1/articles
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "새로운 아티클 제목",
  "content": "## 소개\n\n내용입니다...",
  "category": "tech",
  "tags": ["Laravel", "PHP"],
  "status": "published",
  "thumbnail": "(file upload)"
}
```

**Validation Rules:**
- `title`: required, string, min:5, max:200
- `content`: required, string, min:100
- `category`: required, in:tech,career,life,review
- `tags`: array, max:5
- `tags.*`: string, max:20
- `status`: required, in:draft,published
- `thumbnail`: nullable, image, max:2048

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "새로운 아티클 제목",
    "slug": "new-article-title",
    "status": "published",
    "published_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### 2.4 아티클 수정
```
PUT /api/v1/articles/{slug}
Authorization: Bearer {token}
```

**Request Body:** (same as create)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "수정된 아티클 제목",
    "slug": "updated-article-title",
    "updated_at": "2024-01-15T14:30:00Z"
  }
}
```

---

### 2.5 아티클 삭제
```
DELETE /api/v1/articles/{slug}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "아티클이 삭제되었습니다."
  }
}
```

---

### 2.6 아티클 임시저장
```
POST /api/v1/articles/draft
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "작성 중인 아티클",
  "content": "작성 중...",
  "category": "tech",
  "tags": ["Laravel"]
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 11,
    "title": "작성 중인 아티클",
    "slug": null,
    "status": "draft",
    "saved_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### 2.7 내 아티클 목록
```
GET /api/v1/me/articles
Authorization: Bearer {token}
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| status | string | all | 상태 필터 (all, published, draft) |
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 |

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "articles": [...],
    "stats": {
      "total": 5,
      "published": 3,
      "draft": 2,
      "total_views": 2693
    }
  },
  "meta": {...}
}
```

---

### 2.8 아티클 좋아요
```
POST /api/v1/articles/{slug}/like
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "is_liked": true,
    "likes_count": 57
  }
}
```

---

### 2.9 아티클 좋아요 취소
```
DELETE /api/v1/articles/{slug}/like
Authorization: Bearer {token}
```

---

### 2.10 이미지 업로드
```
POST /api/v1/upload/image
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request:**
- `image`: file (required, image, max:5120KB)

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "url": "https://storage.example.com/images/abc123.jpg",
    "thumbnail_url": "https://storage.example.com/images/abc123_thumb.jpg"
  }
}
```

---

## 3. Comments API

### 3.1 댓글 목록 조회
```
GET /api/v1/articles/{slug}/comments
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 |
| sort | string | latest | 정렬 (latest, oldest, popular) |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "정말 유익한 글이네요!",
      "author": {
        "id": 2,
        "name": "박댓글",
        "username": "parkcomment",
        "avatar_url": "https://..."
      },
      "likes_count": 5,
      "is_liked": false,
      "replies_count": 2,
      "replies": [
        {
          "id": 2,
          "content": "감사합니다!",
          "author": {...},
          "created_at": "2024-01-15T11:00:00Z"
        }
      ],
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {...}
}
```

---

### 3.2 댓글 작성
```
POST /api/v1/articles/{slug}/comments
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "content": "좋은 글 감사합니다!",
  "parent_id": null
}
```

**Validation Rules:**
- `content`: required, string, min:1, max:1000
- `parent_id`: nullable, exists:comments,id

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "content": "좋은 글 감사합니다!",
    "author": {...},
    "created_at": "2024-01-15T12:00:00Z"
  }
}
```

---

### 3.3 댓글 수정
```
PUT /api/v1/comments/{id}
Authorization: Bearer {token}
```

---

### 3.4 댓글 삭제
```
DELETE /api/v1/comments/{id}
Authorization: Bearer {token}
```

---

### 3.5 댓글 좋아요
```
POST /api/v1/comments/{id}/like
Authorization: Bearer {token}
```

---

## 4. Search API

### 4.1 통합 검색
```
GET /api/v1/search
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| q | string | required | 검색어 |
| type | string | all | 검색 타입 (all, articles, users, tags) |
| period | string | all | 기간 (all, day, week, month, year) |
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 |

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "articles": {
      "items": [...],
      "total": 18
    },
    "users": {
      "items": [...],
      "total": 3
    },
    "tags": {
      "items": [...],
      "total": 3
    }
  },
  "meta": {
    "query": "Laravel",
    "total_results": 24
  }
}
```

---

### 4.2 아티클 검색
```
GET /api/v1/search/articles
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| q | string | required | 검색어 |
| category | string | null | 카테고리 필터 |
| tag | string | null | 태그 필터 |
| author | string | null | 작성자 username |
| period | string | all | 기간 필터 |
| sort | string | relevance | 정렬 (relevance, latest, popular) |

---

### 4.3 사용자 검색
```
GET /api/v1/search/users
```

---

### 4.4 태그 검색
```
GET /api/v1/search/tags
```

---

## 5. Users API

### 5.1 사용자 프로필 조회
```
GET /api/v1/users/{username}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "김개발",
    "username": "devkim",
    "avatar_url": "https://...",
    "bio": "5년차 백엔드 개발자입니다.",
    "location": "서울, 대한민국",
    "website": "https://devkim.dev",
    "github": "devkim",
    "followers_count": 1234,
    "following_count": 567,
    "articles_count": 15,
    "is_following": false,
    "created_at": "2023-03-15T00:00:00Z"
  }
}
```

---

### 5.2 사용자 아티클 목록
```
GET /api/v1/users/{username}/articles
```

---

### 5.3 사용자 댓글 목록
```
GET /api/v1/users/{username}/comments
```

---

### 5.4 사용자 좋아요 목록
```
GET /api/v1/users/{username}/likes
```

---

### 5.5 팔로우
```
POST /api/v1/users/{username}/follow
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "is_following": true,
    "followers_count": 1235
  }
}
```

---

### 5.6 언팔로우
```
DELETE /api/v1/users/{username}/follow
Authorization: Bearer {token}
```

---

### 5.7 팔로워 목록
```
GET /api/v1/users/{username}/followers
```

---

### 5.8 팔로잉 목록
```
GET /api/v1/users/{username}/following
```

---

## 6. Settings API

### 6.1 프로필 수정
```
PUT /api/v1/settings/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "김개발",
  "username": "devkim",
  "bio": "5년차 백엔드 개발자입니다.",
  "location": "서울, 대한민국",
  "website": "https://devkim.dev",
  "github": "devkim"
}
```

**Validation Rules:**
- `name`: required, string, max:50
- `username`: required, string, min:3, max:20, unique:users (except self)
- `bio`: nullable, string, max:200
- `location`: nullable, string, max:100
- `website`: nullable, url, max:200
- `github`: nullable, string, max:50

---

### 6.2 프로필 사진 변경
```
POST /api/v1/settings/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request:**
- `avatar`: file (required, image, max:2048KB, dimensions:min_width=100,min_height=100)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "avatar_url": "https://storage.example.com/avatars/user1.jpg"
  }
}
```

---

### 6.3 이메일 변경
```
PUT /api/v1/settings/email
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "email": "newemail@example.com",
  "password": "CurrentPassword123!"
}
```

---

### 6.4 비밀번호 변경
```
PUT /api/v1/settings/password
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "CurrentPassword123!",
  "password": "NewPassword456!",
  "password_confirmation": "NewPassword456!"
}
```

---

### 6.5 알림 설정 조회
```
GET /api/v1/settings/notifications
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "email_comments": true,
    "email_follows": true,
    "email_likes": false,
    "email_newsletter": true
  }
}
```

---

### 6.6 알림 설정 변경
```
PUT /api/v1/settings/notifications
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "email_comments": true,
  "email_follows": true,
  "email_likes": false,
  "email_newsletter": true
}
```

---

### 6.7 연결된 소셜 계정 목록
```
GET /api/auth/social-accounts
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "social_accounts": {
      "github": {
        "connected": true,
        "provider_id": "12345678",
        "nickname": "devkim",
        "email": "devkim@github.com",
        "avatar": "https://avatars.githubusercontent.com/u/12345678",
        "connected_at": "2024-01-05T00:00:00Z"
      },
      "google": {
        "connected": false
      }
    },
    "can_unlink": {
      "github": true,
      "google": false
    }
  }
}
```

**Notes:**
- `can_unlink`: 해당 소셜 계정을 연동 해제할 수 있는지 여부
- 비밀번호가 없고 소셜 계정이 1개뿐이면 마지막 인증 수단이므로 연동 해제 불가

---

### 6.8 소셜 계정 연결 해제
```
DELETE /api/auth/social-accounts/{provider}
Authorization: Bearer {token}
```

**Path Parameters:**
- `provider`: 연동 해제할 소셜 계정 제공자 (github, google)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "소셜 계정 연동이 해제되었습니다."
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "error": {
    "code": "CANNOT_UNLINK_LAST_AUTH",
    "message": "마지막 인증 수단은 연동 해제할 수 없습니다. 먼저 비밀번호를 설정하거나 다른 소셜 계정을 연동하세요."
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "연동된 소셜 계정을 찾을 수 없습니다."
  }
}
```

---

### 6.9 활성 세션 목록
```
GET /api/v1/settings/sessions
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": "session_abc123",
      "device": "Chrome on macOS",
      "ip_address": "123.456.789.0",
      "location": "서울, 대한민국",
      "is_current": true,
      "last_active_at": "2024-01-15T10:00:00Z"
    },
    {
      "id": "session_def456",
      "device": "Safari on iPhone",
      "ip_address": "123.456.789.1",
      "location": "서울, 대한민국",
      "is_current": false,
      "last_active_at": "2024-01-13T15:00:00Z"
    }
  ]
}
```

---

### 6.10 세션 종료
```
DELETE /api/v1/settings/sessions/{id}
Authorization: Bearer {token}
```

---

### 6.11 다른 모든 세션 종료
```
DELETE /api/v1/settings/sessions
Authorization: Bearer {token}
```

---

### 6.12 계정 비활성화
```
POST /api/v1/settings/deactivate
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "password": "CurrentPassword123!"
}
```

---

### 6.13 계정 삭제
```
DELETE /api/v1/settings/account
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "password": "CurrentPassword123!",
  "confirmation": "DELETE"
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 422 | 입력값 유효성 검증 실패 |
| INVALID_CREDENTIALS | 401 | 잘못된 인증 정보 |
| UNAUTHORIZED | 401 | 인증 필요 |
| FORBIDDEN | 403 | 권한 없음 |
| NOT_FOUND | 404 | 리소스를 찾을 수 없음 |
| CONFLICT | 409 | 리소스 충돌 (중복 등) |
| TOO_MANY_REQUESTS | 429 | 요청 횟수 초과 |
| INTERNAL_ERROR | 500 | 서버 내부 오류 |

---

## 7. Tags API

### 7.1 태그 목록 조회
```
GET /api/tags
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 (max: 50) |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Laravel",
      "slug": "laravel",
      "article_count": 45
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "name": "PHP",
      "slug": "php",
      "article_count": 38
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

---

### 7.2 인기 태그 조회
```
GET /api/tags/popular
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| limit | int | 10 | 반환할 태그 수 (max: 50) |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Laravel",
      "slug": "laravel",
      "article_count": 45
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "name": "PHP",
      "slug": "php",
      "article_count": 38
    }
  ]
}
```

---

### 7.3 태그 검색 (자동완성)
```
GET /api/tags/search
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| q | string | required | 검색어 |
| limit | int | 10 | 반환할 태그 수 (max: 50) |

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Laravel",
      "slug": "laravel",
      "article_count": 45
    }
  ]
}
```

---

### 7.4 태그 상세 조회
```
GET /api/tags/{slug}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Laravel",
    "slug": "laravel",
    "article_count": 45,
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "태그를 찾을 수 없습니다."
  }
}
```

---

### 7.5 태그별 아티클 목록
```
GET /api/tags/{slug}/articles
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | 페이지 번호 |
| per_page | int | 20 | 페이지당 항목 수 (max: 50) |
| sort | string | latest | 정렬 (latest, popular) |

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Laravel",
      "slug": "laravel",
      "article_count": 45
    },
    "articles": [
      {
        "id": 1,
        "title": "Laravel 12에서 새롭게 바뀐 기능들",
        "slug": "laravel-12-new-features",
        "excerpt": "Laravel 12가 출시되면서...",
        "thumbnail_url": "https://...",
        "category": "tech",
        "tags": ["Laravel", "PHP"],
        "author": {
          "id": 1,
          "name": "김개발",
          "username": "devkim",
          "avatar_url": "https://..."
        },
        "views_count": 1234,
        "comments_count": 23,
        "likes_count": 56,
        "published_at": "2024-01-15T10:00:00Z"
      }
    ]
  },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3
  }
}
```

---

### 7.6 아티클 태그 연동

아티클 생성/수정 시 태그를 함께 지정할 수 있습니다.

**아티클 생성 시 태그 추가:**
```
POST /api/articles
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "새로운 아티클",
  "content": "내용...",
  "category": "tech",
  "tags": ["Laravel", "PHP", "Backend"]
}
```

**Validation Rules:**
- `tags`: array (선택)
- `tags.*`: string, max:50

**동작:**
- 존재하지 않는 태그는 자동으로 생성됨
- 기존 태그는 `article_count`가 증가함
- 태그 이름은 대소문자 구분 없이 중복 체크됨

---

## Rate Limiting

| Endpoint | Limit |
|----------|-------|
| 일반 API | 60 requests/minute |
| 인증 API | 10 requests/minute |
| 검색 API | 30 requests/minute |
| 업로드 API | 10 requests/minute |
