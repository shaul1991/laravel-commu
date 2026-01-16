# PHPStorm Xdebug 설정 가이드

## 사전 요구사항

- PHPStorm 설치
- Docker 환경 실행 (`make up`)
- Xdebug 모드 활성화 (`make xdebug-debug`)

## PHPStorm 설정

### 1. PHP CLI Interpreter 설정

1. `Settings/Preferences` > `PHP` 이동
2. `CLI Interpreter` 옆 `...` 클릭
3. `+` > `From Docker, Vagrant, VM, WSL, Remote...` 선택
4. `Docker Compose` 선택
   - Configuration file: `docker/docker-compose.yml`
   - Service: `was`
5. `OK` 클릭

### 2. Xdebug 포트 설정

1. `Settings/Preferences` > `PHP` > `Debug` 이동
2. **Xdebug** 섹션에서:
   - Debug port: `9003` (기본값 확인)
   - `Can accept external connections` 체크

### 3. 서버 매핑 설정

1. `Settings/Preferences` > `PHP` > `Servers` 이동
2. `+` 클릭하여 새 서버 추가
3. 설정:
   - Name: `laravel-docker` (임의)
   - Host: `localhost`
   - Port: `80`
   - Debugger: `Xdebug`
   - `Use path mappings` 체크
4. 경로 매핑:
   | 로컬 경로 | 서버 경로 |
   |-----------|-----------|
   | 프로젝트 루트 | `/var/www/html` |

### 4. DBGp Proxy 설정 (선택사항)

1. `Settings/Preferences` > `PHP` > `Debug` > `DBGp Proxy` 이동
2. IDE key: `PHPSTORM`

## 디버깅 시작

### 웹 요청 디버깅

1. **리스닝 시작**: 상단 툴바에서 `Start Listening for PHP Debug Connections` 클릭 (전화기 아이콘)
2. **브라우저 확장 설치**:
   - Chrome: [Xdebug Helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc)
   - Firefox: [Xdebug Helper](https://addons.mozilla.org/en-US/firefox/addon/xdebug-helper-for-firefox/)
3. **브라우저 확장 설정**:
   - 확장 아이콘 우클릭 > Options
   - IDE key: `PHPSTORM`
4. **디버깅**:
   - 브라우저 확장에서 `Debug` 모드 활성화 (벌레 아이콘이 초록색)
   - 코드에 breakpoint 설정
   - 웹페이지 접속

### CLI/Artisan 디버깅

1. PHPStorm에서 리스닝 시작
2. 터미널에서 환경변수와 함께 실행:
   ```bash
   make sh  # 컨테이너 접속
   XDEBUG_TRIGGER=1 php artisan migrate
   ```

   또는 호스트에서:
   ```bash
   docker compose -f docker/docker-compose.yml exec -e XDEBUG_TRIGGER=1 was php artisan migrate
   ```

### PHPUnit 테스트 디버깅

1. 테스트 파일 열기
2. 테스트 메서드 옆 초록색 화살표 클릭
3. `Debug 'testMethodName'` 선택

## 트러블슈팅

### Breakpoint에서 멈추지 않음

1. **리스닝 확인**: PHPStorm에서 리스닝이 활성화되어 있는지 확인
2. **Xdebug 모드 확인**:
   ```bash
   docker compose -f docker/docker-compose.yml exec was php -i | grep xdebug.mode
   # 출력: xdebug.mode => debug => debug
   ```
3. **경로 매핑 확인**: 서버 설정에서 경로 매핑이 올바른지 확인
4. **포트 확인**: 9003 포트가 다른 프로세스에 의해 사용 중인지 확인

### 연결이 거부됨

1. **방화벽 확인**: 9003 포트가 열려 있는지 확인
2. **Docker 네트워크 확인**: `host.docker.internal`이 호스트를 가리키는지 확인
   ```bash
   docker compose -f docker/docker-compose.yml exec was ping -c 1 host.docker.internal
   ```

### 디버깅이 너무 느림

`trace` 모드가 함께 활성화되어 있으면 성능이 저하될 수 있음:
```bash
make xdebug-debug  # debug 모드만 활성화
```

## 유용한 단축키

| 단축키 (macOS) | 단축키 (Windows/Linux) | 동작 |
|----------------|------------------------|------|
| `Cmd + F8` | `Ctrl + F8` | Breakpoint 토글 |
| `F8` | `F8` | Step Over (다음 줄) |
| `F7` | `F7` | Step Into (함수 내부로) |
| `Shift + F8` | `Shift + F8` | Step Out (함수 밖으로) |
| `F9` | `F9` | Resume (다음 breakpoint까지) |
| `Cmd + F2` | `Ctrl + F2` | 디버깅 중지 |

## 참고 링크

- [PHPStorm Xdebug 공식 문서](https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html)
- [Xdebug 공식 문서](https://xdebug.org/docs/)
