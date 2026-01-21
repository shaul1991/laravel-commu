pipeline {
    agent any

    parameters {
        choice(
            name: 'ENVIRONMENT',
            choices: ['prod', 'dev'],
            description: '배포 환경 선택'
        )
        choice(
            name: 'ACTION',
            choices: ['deploy', 'rollback', 'build-base'],
            description: '실행할 작업 선택 (build-base: Base 이미지 빌드, rollback: prod 환경만 지원)'
        )
        string(
            name: 'BRANCH',
            defaultValue: '',
            description: '배포할 브랜치 또는 태그 (비워두면 환경에 따라 자동 선택: prod=master, dev=develop)'
        )
        string(
            name: 'ROLLBACK_VERSION',
            defaultValue: '',
            description: '롤백할 버전 (rollback 시 사용, 비워두면 이전 버전으로 롤백)'
        )
        password(
            name: 'KEYCLOAK_CLIENT_SECRET',
            defaultValue: '',
            description: 'Keycloak Client Secret (dev 환경 배포 시 필요, 비워두면 기존 설정 유지)'
        )
    }

    environment {
        // 공통 설정
        DOCKER_IMAGE_BASE = 'laravel-commu-base'
        GIT_REPO = 'git@github.com:shaul1991/laravel-commu.git'
        GIT_CREDENTIALS_ID = 'home-server-deploy'
        SLACK_CHANNEL = '#deployments'
    }

    stages {
        // ==========================================
        // 환경 설정 초기화
        // ==========================================
        stage('Initialize Environment') {
            steps {
                script {
                    // 환경별 변수 설정
                    if (params.ENVIRONMENT == 'prod') {
                        env.PROJECT_NAME = 'laravel-commu'
                        env.DEPLOY_PATH = '/var/www/laravel-commu'
                        env.STORAGE_PATH = '/var/www/laravel-commu-storage'
                        env.ENV_CONFIG_FILE_ID = 'commu-env-prod'
                        env.TARGET_BRANCH = params.BRANCH ?: 'master'
                        env.BLUE_PORT = '10000'
                        env.GREEN_PORT = '10001'
                        env.COMPOSE_FILE = 'docker/docker-compose.deploy.yml'
                    } else {
                        env.PROJECT_NAME = 'laravel-commu-dev'
                        env.DEPLOY_PATH = '/var/www/laravel-commu-dev'
                        env.STORAGE_PATH = '/var/www/laravel-commu-dev-storage'
                        env.ENV_CONFIG_FILE_ID = 'commu-env-dev'
                        env.TARGET_BRANCH = params.BRANCH ?: 'develop'
                        env.DEV_PORT = '10100'
                        env.COMPOSE_FILE = 'docker/docker-compose.deploy.yml'
                    }

                    // rollback은 prod 환경에서만 지원
                    if (params.ACTION == 'rollback' && params.ENVIRONMENT != 'prod') {
                        error("Rollback is only supported in prod environment")
                    }

                    echo "Environment: ${params.ENVIRONMENT}"
                    echo "Action: ${params.ACTION}"
                    echo "Branch: ${env.TARGET_BRANCH}"
                }
            }
        }

        // ==========================================
        // Base 이미지 빌드 (수동 실행)
        // ==========================================
        stage('Build Base Image') {
            when {
                expression { params.ACTION == 'build-base' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - build-base (${params.ENVIRONMENT})"
                }
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: env.TARGET_BRANCH]],
                    userRemoteConfigs: [[
                        url: env.GIT_REPO,
                        credentialsId: env.GIT_CREDENTIALS_ID
                    ]]
                ])
                sh """
                    docker build \
                        -f docker/Dockerfile.base \
                        -t ${DOCKER_IMAGE_BASE}:latest \
                        .
                """
                echo "Base image built: ${DOCKER_IMAGE_BASE}:latest"
            }
        }

        // ==========================================
        // 배포: 환경 준비
        // ==========================================
        stage('Prepare Environment') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - deploy-${params.ENVIRONMENT} (${env.TARGET_BRANCH})"

                    // Base 이미지 존재 확인
                    def baseImageExists = sh(
                        script: "docker images -q ${DOCKER_IMAGE_BASE}:latest",
                        returnStdout: true
                    ).trim()

                    if (!baseImageExists) {
                        error("Base image not found. Run 'build-base' action first.")
                    }

                    // Storage 디렉토리 초기화
                    sh """
                        mkdir -p ${env.STORAGE_PATH}/app/public
                        mkdir -p ${env.STORAGE_PATH}/framework/cache/data
                        mkdir -p ${env.STORAGE_PATH}/framework/sessions
                        mkdir -p ${env.STORAGE_PATH}/framework/views
                        mkdir -p ${env.STORAGE_PATH}/logs
                        chown -R www-data:www-data ${env.STORAGE_PATH} || true
                        chmod -R 775 ${env.STORAGE_PATH}
                    """

                    // Docker 네트워크 생성
                    sh """
                        docker network create laravel_network 2>/dev/null || true
                    """

                    // Blue-Green 배포 대상 결정 (prod 환경만)
                    if (params.ENVIRONMENT == 'prod') {
                        def blueRunning = sh(
                            script: "docker ps -q -f name=${env.PROJECT_NAME}-blue",
                            returnStdout: true
                        ).trim()

                        if (blueRunning) {
                            env.CURRENT_ENV = 'blue'
                            env.TARGET_ENV = 'green'
                            env.TARGET_PORT = env.GREEN_PORT
                            env.CURRENT_PORT = env.BLUE_PORT
                        } else {
                            env.CURRENT_ENV = 'green'
                            env.TARGET_ENV = 'blue'
                            env.TARGET_PORT = env.BLUE_PORT
                            env.CURRENT_PORT = env.GREEN_PORT
                        }
                        echo "Current: ${env.CURRENT_ENV} -> Target: ${env.TARGET_ENV}"
                    } else {
                        env.TARGET_ENV = 'dev'
                        env.TARGET_PORT = env.DEV_PORT
                    }
                }
            }
        }

        // ==========================================
        // 배포: 코드 업데이트
        // ==========================================
        stage('Update Code') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 배포 디렉토리 생성
                    sh "mkdir -p ${env.DEPLOY_PATH}"

                    // Git 저장소 존재 여부 확인
                    def gitExists = sh(
                        script: "docker run --rm -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} -w ${env.DEPLOY_PATH} alpine:latest test -d .git && echo 'yes' || echo 'no'",
                        returnStdout: true
                    ).trim()

                    if (gitExists == 'no') {
                        // 최초 배포: 저장소 클론
                        echo "Initial deployment: Cloning repository..."
                        sh """
                            docker run --rm \
                                -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} \
                                -v /root/.ssh:/root/.ssh:ro \
                                alpine/git:latest \
                                clone ${GIT_REPO} ${env.DEPLOY_PATH}
                        """
                    }

                    // Git fetch & reset
                    if (params.ENVIRONMENT == 'prod') {
                        // prod: 로컬 변경사항 무시하고 강제 리셋
                        sh """
                            docker run --rm \
                                --entrypoint sh \
                                -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} \
                                -v /root/.ssh:/root/.ssh:ro \
                                -w ${env.DEPLOY_PATH} \
                                alpine/git:latest \
                                -c "git config --global --add safe.directory ${env.DEPLOY_PATH} && git fetch origin && git checkout -f ${env.TARGET_BRANCH} && git reset --hard origin/${env.TARGET_BRANCH}"
                        """
                    } else {
                        // dev: 일반 pull
                        sh """
                            docker run --rm \
                                --entrypoint sh \
                                -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} \
                                -v /root/.ssh:/root/.ssh:ro \
                                -w ${env.DEPLOY_PATH} \
                                alpine/git:latest \
                                -c "git config --global --add safe.directory ${env.DEPLOY_PATH} && git fetch origin && git checkout ${env.TARGET_BRANCH} && git pull origin ${env.TARGET_BRANCH}"
                        """
                    }

                    // 배포 버전 기록
                    env.DEPLOY_VERSION = sh(
                        script: "docker run --rm -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} -w ${env.DEPLOY_PATH} alpine/git:latest rev-parse --short HEAD",
                        returnStdout: true
                    ).trim()

                    env.DEPLOY_TIMESTAMP = sh(
                        script: "date +%Y%m%d-%H%M%S",
                        returnStdout: true
                    ).trim()

                    env.VERSION_TAG = "${env.DEPLOY_TIMESTAMP}-${env.DEPLOY_VERSION}"
                    echo "Deploying version: ${env.VERSION_TAG}"
                }
            }
        }

        // ==========================================
        // 배포: 의존성 설치 및 빌드
        // ==========================================
        stage('Install Dependencies') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    sh """
                        docker run --rm \
                            -v ${env.DEPLOY_PATH}:/var/www/html \
                            -w /var/www/html \
                            ${DOCKER_IMAGE_BASE}:latest \
                            sh -c "composer install --no-dev --optimize-autoloader --no-interaction && npm ci --production=false && npm run build"
                    """
                }
            }
        }

        // ==========================================
        // 배포: .env 파일 주입
        // ==========================================
        stage('Inject Environment') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    configFileProvider([
                        configFile(fileId: env.ENV_CONFIG_FILE_ID, variable: 'ENV_FILE_PATH')
                    ]) {
                        sh """
                            cat \${ENV_FILE_PATH} | docker run --rm -i \
                                -v ${env.DEPLOY_PATH}:/var/www/html \
                                alpine:latest \
                                sh -c 'cat > /var/www/html/.env'
                        """
                        echo ".env file injected from Jenkins Config File Provider"
                    }

                    // Keycloak 환경변수 주입 (dev 환경)
                    if (params.ENVIRONMENT == 'dev' && params.KEYCLOAK_CLIENT_SECRET?.toString()?.trim()) {
                        sh """
                            docker run --rm \
                                -v ${env.DEPLOY_PATH}:/var/www/html \
                                -e KEYCLOAK_SECRET="${params.KEYCLOAK_CLIENT_SECRET}" \
                                alpine:latest \
                                sh -c 'echo "" >> /var/www/html/.env && echo "# Keycloak OAuth" >> /var/www/html/.env && echo "KEYCLOAK_CLIENT_ID=blogs-dev" >> /var/www/html/.env && echo "KEYCLOAK_CLIENT_SECRET=\${KEYCLOAK_SECRET}" >> /var/www/html/.env && echo "KEYCLOAK_BASE_URL=https://dev-keycloak.shaul.kr" >> /var/www/html/.env && echo "KEYCLOAK_REALM=dev" >> /var/www/html/.env'
                        """
                        echo "Keycloak environment variables injected"
                    }
                }
            }
        }

        // ==========================================
        // 배포: 새 컨테이너 시작
        // ==========================================
        stage('Deploy Container') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    def containerName = params.ENVIRONMENT == 'prod' ?
                        "${env.PROJECT_NAME}-${env.TARGET_ENV}" : env.PROJECT_NAME

                    // 기존 컨테이너 중지
                    sh """
                        docker stop ${containerName} 2>/dev/null || true
                        docker rm ${containerName} 2>/dev/null || true
                    """

                    // 새 컨테이너 시작
                    sh """
                        docker run --rm \
                            -v /var/run/docker.sock:/var/run/docker.sock \
                            -v ${env.DEPLOY_PATH}:${env.DEPLOY_PATH} \
                            -w ${env.DEPLOY_PATH} \
                            -e APP_PATH=${env.DEPLOY_PATH} \
                            -e STORAGE_PATH=${env.STORAGE_PATH} \
                            -e ENV_FILE=${env.DEPLOY_PATH}/.env \
                            -e DEPLOY_ENV=${params.ENVIRONMENT} \
                            docker:cli \
                            docker compose -f ${env.COMPOSE_FILE} up -d ${env.TARGET_ENV}
                    """

                    // OAuth 키 권한 수정
                    sh """
                        sleep 5
                        docker exec ${containerName} sh -c 'if [ -f /var/www/html/storage/oauth-private.key ]; then chown www-data:www-data /var/www/html/storage/oauth-private.key /var/www/html/storage/oauth-public.key && chmod 600 /var/www/html/storage/oauth-private.key && chmod 660 /var/www/html/storage/oauth-public.key; fi' || true
                    """

                    // Laravel 캐시 생성
                    sh """
                        docker exec ${containerName} php artisan config:cache || true
                        docker exec ${containerName} php artisan route:cache || true
                        docker exec ${containerName} php artisan view:cache || true
                        docker exec ${containerName} php artisan migrate --force || true
                    """

                    // dev 환경: storage 권한 설정
                    if (params.ENVIRONMENT == 'dev') {
                        sh """
                            docker exec ${containerName} chown -R www-data:www-data /var/www/html/storage
                            docker exec ${containerName} chmod -R 775 /var/www/html/storage
                            docker exec ${containerName} php artisan storage:link || true
                            docker exec ${containerName} sh -c 'if [ -f /var/www/html/storage/oauth-private.key ]; then chmod 600 /var/www/html/storage/oauth-private.key && chmod 660 /var/www/html/storage/oauth-public.key; fi' || true
                        """
                    }
                }
            }
        }

        // ==========================================
        // 배포: 헬스체크
        // ==========================================
        stage('Health Check') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    def containerName = params.ENVIRONMENT == 'prod' ?
                        "${env.PROJECT_NAME}-${env.TARGET_ENV}" : env.PROJECT_NAME
                    def maxRetries = 30
                    def retryCount = 0
                    def healthy = false

                    while (retryCount < maxRetries && !healthy) {
                        sleep(2)
                        def status = sh(
                            script: "docker exec ${containerName} curl -sf http://localhost/up || echo 'unhealthy'",
                            returnStdout: true
                        ).trim()

                        if (status != 'unhealthy') {
                            healthy = true
                            echo "Health check passed!"
                        } else {
                            retryCount++
                            echo "Health check attempt ${retryCount}/${maxRetries}..."
                        }
                    }

                    if (!healthy) {
                        sh "docker logs --tail 50 ${containerName} || true"
                        error("Health check failed after ${maxRetries} attempts")
                    }
                }
            }
        }

        // ==========================================
        // 배포: 트래픽 전환 (prod 환경만)
        // ==========================================
        stage('Switch Traffic') {
            when {
                expression { params.ACTION == 'deploy' && params.ENVIRONMENT == 'prod' }
            }
            steps {
                script {
                    // Caddy 설정 업데이트
                    sh """
                        docker run --rm \
                            -v ${env.DEPLOY_PATH}:/var/www/html \
                            -v /etc/caddy:/etc/caddy \
                            alpine:latest \
                            sh /var/www/html/deploy/switch-traffic.sh ${env.TARGET_PORT}
                    """

                    // Caddy reload
                    sh """
                        docker run --rm --privileged --pid=host alpine:latest \
                            nsenter -t 1 -m -u -n -i systemctl reload caddy
                    """

                    // 배포 버전 기록
                    sh """
                        docker run --rm \
                            -v ${env.DEPLOY_PATH}:/var/www/html \
                            alpine:latest \
                            sh -c "echo '${env.VERSION_TAG}' >> /var/www/html/deploy/versions.log && tail -10 /var/www/html/deploy/versions.log > /var/www/html/deploy/versions.tmp && mv /var/www/html/deploy/versions.tmp /var/www/html/deploy/versions.log"
                    """

                    echo "Traffic switched to ${env.TARGET_ENV} (port ${env.TARGET_PORT})"
                }
            }
        }

        // ==========================================
        // 배포: 정리
        // ==========================================
        stage('Cleanup') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    if (params.ENVIRONMENT == 'prod') {
                        echo "Previous container (${env.CURRENT_ENV}) kept for rollback"
                    }

                    // npm 캐시 정리
                    sh """
                        docker run --rm \
                            -v ${env.DEPLOY_PATH}:/var/www/html \
                            alpine:latest \
                            rm -rf /var/www/html/node_modules/.cache 2>/dev/null || true
                    """
                }
            }
        }

        // ==========================================
        // 롤백 (prod 환경만)
        // ==========================================
        stage('Rollback') {
            when {
                expression { params.ACTION == 'rollback' && params.ENVIRONMENT == 'prod' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - rollback"

                    // 현재 활성 포트 확인
                    def currentPort = sh(
                        script: "docker run --rm -v ${env.DEPLOY_PATH}:/var/www/html alpine:latest cat /var/www/html/deploy/current-port.txt 2>/dev/null || echo '${env.BLUE_PORT}'",
                        returnStdout: true
                    ).trim()

                    // 롤백 대상 포트 결정
                    def rollbackPort = (currentPort == env.BLUE_PORT) ? env.GREEN_PORT : env.BLUE_PORT
                    def rollbackEnv = (currentPort == env.BLUE_PORT) ? 'green' : 'blue'

                    // 롤백 컨테이너 상태 확인
                    def rollbackRunning = sh(
                        script: "docker ps -q -f name=${env.PROJECT_NAME}-${rollbackEnv}",
                        returnStdout: true
                    ).trim()

                    if (!rollbackRunning) {
                        error("No previous version available for rollback")
                    }

                    // 트래픽 전환
                    sh """
                        docker run --rm \
                            -v ${env.DEPLOY_PATH}:/var/www/html \
                            -v /etc/caddy:/etc/caddy \
                            alpine:latest \
                            sh /var/www/html/deploy/switch-traffic.sh ${rollbackPort}
                    """

                    // Caddy reload
                    sh """
                        docker run --rm --privileged --pid=host alpine:latest \
                            nsenter -t 1 -m -u -n -i systemctl reload caddy
                    """

                    echo "Rolled back to ${rollbackEnv} (port ${rollbackPort})"
                }
            }
        }
    }

    post {
        success {
            script {
                def envLabel = params.ENVIRONMENT == 'prod' ? '' : '[DEV] '
                def projectName = env.PROJECT_NAME ?: (params.ENVIRONMENT == 'prod' ? 'laravel-commu' : 'laravel-commu-dev')
                def message = ""
                def url = params.ENVIRONMENT == 'prod' ?
                    'https://blogs.shaul.kr' : 'https://dev-blogs.shaul.kr'

                switch(params.ACTION) {
                    case 'deploy':
                        message = "${envLabel}배포 성공: ${projectName} v${env.VERSION_TAG ?: 'N/A'}"
                        break
                    case 'rollback':
                        message = "${envLabel}롤백 성공: ${projectName}"
                        break
                    case 'build-base':
                        message = "${envLabel}Base 이미지 빌드 성공: ${DOCKER_IMAGE_BASE}"
                        break
                }

                try {
                    slackSend(
                        channel: env.SLACK_CHANNEL,
                        color: 'good',
                        message: """
                            *${message}*
                            - Environment: ${params.ENVIRONMENT}
                            - Branch: ${env.TARGET_BRANCH ?: 'N/A'}
                            - Build: #${BUILD_NUMBER}
                            - URL: ${url}
                        """.stripIndent()
                    )
                } catch (Exception e) {
                    echo "Slack notification skipped: ${e.message}"
                }
            }
        }
        failure {
            script {
                def envLabel = params.ENVIRONMENT == 'prod' ? '' : '[DEV] '
                def projectName = env.PROJECT_NAME ?: (params.ENVIRONMENT == 'prod' ? 'laravel-commu' : 'laravel-commu-dev')
                def message = ""

                switch(params.ACTION) {
                    case 'deploy':
                        message = "${envLabel}배포 실패: ${projectName}"
                        break
                    case 'rollback':
                        message = "${envLabel}롤백 실패: ${projectName}"
                        break
                    case 'build-base':
                        message = "${envLabel}Base 이미지 빌드 실패: ${DOCKER_IMAGE_BASE}"
                        break
                }

                try {
                    slackSend(
                        channel: env.SLACK_CHANNEL,
                        color: 'danger',
                        message: """
                            *${message}*
                            - Environment: ${params.ENVIRONMENT}
                            - Branch: ${env.TARGET_BRANCH ?: 'N/A'}
                            - Build: #${BUILD_NUMBER}
                            - Console: ${BUILD_URL}console
                        """.stripIndent()
                    )
                } catch (Exception e) {
                    echo "Slack notification skipped: ${e.message}"
                }

                // 배포 실패 시 자동 롤백 시도 (prod 환경만)
                if (params.ACTION == 'deploy' && params.ENVIRONMENT == 'prod') {
                    echo "Attempting automatic rollback..."
                    build job: env.JOB_NAME, parameters: [
                        string(name: 'ENVIRONMENT', value: 'prod'),
                        string(name: 'ACTION', value: 'rollback'),
                        string(name: 'BRANCH', value: env.TARGET_BRANCH ?: 'master')
                    ], wait: false
                }
            }
        }
    }
}
