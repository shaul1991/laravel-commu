# Xdebug 설정 가이드

## 문제점

### 증상
`php artisan` 명령 실행 시 IDE의 breakpoint에서 계속 멈추는 현상 발생.

### 원인
`xdebug.ini` 설정에서 `start_with_request=yes`로 되어 있어 모든 PHP 요청(웹, CLI 포함)에서 자동으로 디버거에 연결을 시도.

```ini
# 문제가 된 설정
xdebug.start_with_request=yes
```

### 추가 문제
`xdebug.ini` 파일이 Docker 이미지 빌드 시에만 복사되고 런타임에 마운트되지 않아, 설정 변경 시 이미지 재빌드가 필요했음.

## 해결 방법

### 1. start_with_request를 trigger로 변경

```ini
# 수정된 설정
xdebug.start_with_request=trigger
```

**trigger 모드 동작:**
- 웹 요청: `XDEBUG_SESSION` 쿠키가 있을 때만 디버깅 활성화 (브라우저 확장/IDE가 설정)
- CLI 요청: 환경변수 `XDEBUG_TRIGGER=1`이 있을 때만 디버깅 활성화

### 2. xdebug.ini 런타임 마운트 추가

`docker-compose.yml`에 볼륨 마운트 추가:

```yaml
volumes:
  - ./was/xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini:ro
```

이를 통해 설정 변경 시 컨테이너 재시작만으로 적용 가능.

## 사용법

### 웹 디버깅
1. 브라우저에 Xdebug 확장 설치 (예: Xdebug Helper)
2. 확장에서 "Debug" 모드 활성화
3. IDE에서 디버그 리스닝 시작
4. 웹페이지 접속 시 breakpoint에서 멈춤

### CLI/Artisan 디버깅
디버깅이 필요한 경우에만 환경변수 추가:

```bash
XDEBUG_TRIGGER=1 php artisan migrate
XDEBUG_TRIGGER=1 php artisan tinker
```

### Xdebug 모드 변경
Makefile 명령어 사용:

```bash
make xdebug-off      # 비활성화
make xdebug-debug    # IDE 스텝 디버깅
make xdebug-develop  # 향상된 에러 출력
make xdebug-coverage # 코드 커버리지
```

## 참고

### Xdebug 모드 종류

| 모드 | 설명 |
|------|------|
| `off` | 비활성화 |
| `debug` | IDE 연결, breakpoint 지원 |
| `develop` | 향상된 에러 메시지, var_dump 개선 |
| `trace` | 함수 호출 로그 파일 생성 |
| `coverage` | 코드 커버리지 수집 |
| `profile` | 성능 프로파일링 |

### start_with_request 옵션

| 값 | 설명 |
|----|------|
| `yes` | 모든 요청에서 자동 시작 |
| `no` | 자동 시작 안 함 |
| `trigger` | 트리거(쿠키/환경변수)가 있을 때만 시작 |
