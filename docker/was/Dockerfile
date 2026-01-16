FROM php:8.4-fpm-alpine

# ==========================================
# 시스템 패키지 설치
# - git, unzip: Composer 의존성 설치
# - libpq-dev: PostgreSQL PHP 확장 빌드
# - supervisor: PHP-FPM, Queue Worker, Scheduler 관리
# ==========================================
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    supervisor

# ==========================================
# PHP 확장 설치
# - pdo, pdo_pgsql: PostgreSQL 연결
# - pcntl: Queue Worker 프로세스 제어
# - opcache: PHP 성능 최적화
# ==========================================
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pcntl \
    opcache

# ==========================================
# PECL 확장 설치
# - redis: phpredis 확장 (선택적, predis 사용 시 불필요)
#          기본값: predis (pure PHP, composer.json에 포함)
#          성능 최적화 시: REDIS_CLIENT=phpredis 설정
# - mongodb: MongoDB 연결
# - xdebug: 디버깅 (IDE 연동)
# - excimer: Sentry 프로파일링
# ==========================================
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers openssl-dev \
    && pecl install redis mongodb xdebug excimer \
    && docker-php-ext-enable redis mongodb xdebug excimer \
    && apk del .build-deps

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# ==========================================
# Composer 설치
# ==========================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ==========================================
# 설정 파일 복사
# ==========================================
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY supervisord.conf /etc/supervisord.conf

# ==========================================
# 작업 디렉토리 및 권한
# ==========================================
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
