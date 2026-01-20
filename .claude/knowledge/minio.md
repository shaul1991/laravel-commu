# MinIO 운영 가이드

## 개요
- **용도**: S3 호환 Object Storage
- **컨테이너**: `minio/minio`

## Docker 설정

### docker-compose.yml
```yaml
minio:
  image: minio/minio
  command: server /data --console-address ":9001"
  environment:
    MINIO_ROOT_USER: ${MINIO_ROOT_USER}
    MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD}
  volumes:
    - minio-data:/data
  ports:
    - "9000:9000"   # API
    - "9001:9001"   # Console
  healthcheck:
    test: ["CMD", "mc", "ready", "local"]
    interval: 30s
    timeout: 20s
    retries: 3
  restart: unless-stopped
```

## Laravel 설정

### .env
```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=${MINIO_ROOT_USER}
AWS_SECRET_ACCESS_KEY=${MINIO_ROOT_PASSWORD}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=blogs
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### config/filesystems.php
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
],
```

## 운영 명령어

### MinIO Client (mc)
```bash
# 별칭 설정
mc alias set local http://localhost:9000 ${MINIO_ROOT_USER} ${MINIO_ROOT_PASSWORD}

# 버킷 목록
mc ls local

# 버킷 생성
mc mb local/blogs

# 파일 업로드
mc cp file.txt local/blogs/

# 버킷 용량
mc du local/blogs
```

### Laravel
```php
// 파일 저장
Storage::disk('s3')->put('path/file.txt', $content);

// 파일 URL
Storage::disk('s3')->url('path/file.txt');

// 파일 삭제
Storage::disk('s3')->delete('path/file.txt');
```

## 콘솔 접속
- URL: `http://localhost:9001`
- 계정: MINIO_ROOT_USER / MINIO_ROOT_PASSWORD

## 트러블슈팅

| 증상 | 확인 | 조치 |
|------|------|------|
| 연결 실패 | `mc admin info local` | 컨테이너 재시작 |
| 권한 오류 | 버킷 정책 확인 | 정책 수정 |
| 디스크 부족 | `mc du local` | 오래된 파일 정리 |
