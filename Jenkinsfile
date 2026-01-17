pipeline {
    agent any

    parameters {
        choice(
            name: 'ACTION',
            choices: ['deploy', 'rollback', 'build-base'],
            description: '실행할 작업 선택 (build-base: Base 이미지 빌드)'
        )
        string(
            name: 'BRANCH',
            defaultValue: 'master',
            description: '배포할 브랜치 또는 태그 (deploy 시 사용)'
        )
        string(
            name: 'ROLLBACK_VERSION',
            defaultValue: '',
            description: '롤백할 버전 (rollback 시 사용, 비워두면 이전 버전으로 롤백)'
        )
    }

    environment {
        // 프로젝트 설정
        PROJECT_NAME = 'laravel-commu'
        DEPLOY_PATH = '/var/www/laravel-commu'

        // Docker 설정
        DOCKER_IMAGE_BASE = 'laravel-commu-base'
        BLUE_PORT = '10000'
        GREEN_PORT = '10001'

        // GitHub 설정
        GIT_REPO = 'git@github.com:shaul1991/laravel-commu.git'
        GIT_CREDENTIALS_ID = 'home-server-deploy'

        // Jenkins Config File ID (.env 파일)
        ENV_CONFIG_FILE_ID = 'commu-env-prod'

        // Storage 경로 (blue/green 공유)
        STORAGE_PATH = '/var/www/laravel-commu-storage'

        // Slack 설정
        SLACK_CHANNEL = '#deployments'
    }

    stages {
        // ==========================================
        // Base 이미지 빌드 (수동 실행)
        // ==========================================
        stage('Build Base Image') {
            when {
                expression { params.ACTION == 'build-base' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - build-base"
                }
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: params.BRANCH]],
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
                    currentBuild.displayName = "#${BUILD_NUMBER} - deploy (${params.BRANCH})"

                    // Base 이미지 존재 확인
                    def baseImageExists = sh(
                        script: "docker images -q ${DOCKER_IMAGE_BASE}:latest",
                        returnStdout: true
                    ).trim()

                    if (!baseImageExists) {
                        error("Base image not found. Run 'build-base' action first.")
                    }

                    // Storage 디렉토리 초기화 (최초 배포 시)
                    sh """
                        mkdir -p ${STORAGE_PATH}/app/public
                        mkdir -p ${STORAGE_PATH}/framework/cache/data
                        mkdir -p ${STORAGE_PATH}/framework/sessions
                        mkdir -p ${STORAGE_PATH}/framework/views
                        mkdir -p ${STORAGE_PATH}/logs
                        chown -R www-data:www-data ${STORAGE_PATH} || true
                        chmod -R 775 ${STORAGE_PATH}
                    """

                    // 현재 활성 컨테이너 확인
                    def blueRunning = sh(
                        script: "docker ps -q -f name=${PROJECT_NAME}-blue",
                        returnStdout: true
                    ).trim()

                    // 배포 대상 결정 (Blue-Green 전환)
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
                }
            }
        }

        // ==========================================
        // 배포: 코드 업데이트 (호스트 경로 마운트하여 실행)
        // ==========================================
        stage('Update Code') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 배포 디렉토리 생성
                    sh "mkdir -p ${DEPLOY_PATH}"

                    // Git 저장소 존재 여부 확인
                    def gitExists = sh(
                        script: "docker run --rm -v ${DEPLOY_PATH}:${DEPLOY_PATH} -w ${DEPLOY_PATH} alpine:latest test -d .git && echo 'yes' || echo 'no'",
                        returnStdout: true
                    ).trim()

                    if (gitExists == 'no') {
                        // 최초 배포: 저장소 클론
                        echo "Initial deployment: Cloning repository..."
                        sh """
                            docker run --rm \
                                -v ${DEPLOY_PATH}:${DEPLOY_PATH} \
                                -v /root/.ssh:/root/.ssh:ro \
                                alpine/git:latest \
                                clone ${GIT_REPO} ${DEPLOY_PATH}
                        """
                    }

                    // Git pull (--entrypoint로 쉘 실행)
                    sh """
                        docker run --rm \
                            --entrypoint sh \
                            -v ${DEPLOY_PATH}:${DEPLOY_PATH} \
                            -v /root/.ssh:/root/.ssh:ro \
                            -w ${DEPLOY_PATH} \
                            alpine/git:latest \
                            -c "git config --global --add safe.directory ${DEPLOY_PATH} && git fetch origin && git checkout ${params.BRANCH} && git pull origin ${params.BRANCH}"
                    """

                    // 배포 버전 기록
                    env.DEPLOY_VERSION = sh(
                        script: "docker run --rm -v ${DEPLOY_PATH}:${DEPLOY_PATH} -w ${DEPLOY_PATH} alpine/git:latest rev-parse --short HEAD",
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
        // 배포: 의존성 설치 및 빌드 (Base 이미지 사용)
        // ==========================================
        stage('Install Dependencies') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                sh """
                    docker run --rm \
                        -v ${DEPLOY_PATH}:/var/www/html \
                        -w /var/www/html \
                        ${DOCKER_IMAGE_BASE}:latest \
                        sh -c "composer install --no-dev --optimize-autoloader --no-interaction && npm ci --production=false && npm run build"
                """
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
                configFileProvider([
                    configFile(fileId: env.ENV_CONFIG_FILE_ID, variable: 'ENV_FILE_PATH')
                ]) {
                    // Jenkins 컨테이너 내에서 cat으로 읽고, docker run으로 전달
                    sh """
                        cat \${ENV_FILE_PATH} | docker run --rm -i \
                            -v ${DEPLOY_PATH}:/var/www/html \
                            alpine:latest \
                            sh -c 'cat > /var/www/html/.env'
                    """
                    echo ".env file injected from Jenkins Config File Provider"
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
                    // 기존 대상 컨테이너 중지
                    sh """
                        docker stop ${PROJECT_NAME}-${TARGET_ENV} 2>/dev/null || true
                        docker rm ${PROJECT_NAME}-${TARGET_ENV} 2>/dev/null || true
                    """

                    // 새 컨테이너 시작 (docker compose를 docker-cli 컨테이너에서 실행)
                    sh """
                        docker run --rm \
                            -v /var/run/docker.sock:/var/run/docker.sock \
                            -v ${DEPLOY_PATH}:${DEPLOY_PATH} \
                            -w ${DEPLOY_PATH} \
                            -e APP_PATH=${DEPLOY_PATH} \
                            -e STORAGE_PATH=${STORAGE_PATH} \
                            -e ENV_FILE=${DEPLOY_PATH}/.env \
                            docker/compose:latest \
                            -f docker/docker-compose.prod.yml up -d ${TARGET_ENV}
                    """

                    // Laravel 캐시 생성 (컨테이너 내에서 실행)
                    sh """
                        sleep 5
                        docker exec ${PROJECT_NAME}-${TARGET_ENV} php artisan config:cache || true
                        docker exec ${PROJECT_NAME}-${TARGET_ENV} php artisan route:cache || true
                        docker exec ${PROJECT_NAME}-${TARGET_ENV} php artisan view:cache || true
                        docker exec ${PROJECT_NAME}-${TARGET_ENV} php artisan migrate --force || true
                    """
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
                    def maxRetries = 30
                    def retryCount = 0
                    def healthy = false

                    while (retryCount < maxRetries && !healthy) {
                        sleep(2)
                        def status = sh(
                            script: "curl -sf http://localhost:${TARGET_PORT}/up || echo 'unhealthy'",
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
                        error("Health check failed after ${maxRetries} attempts")
                    }
                }
            }
        }

        // ==========================================
        // 배포: 트래픽 전환
        // ==========================================
        stage('Switch Traffic') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // Caddy 설정 업데이트 (호스트 경로 마운트)
                    sh """
                        docker run --rm \
                            -v ${DEPLOY_PATH}:/var/www/html \
                            -v /etc/caddy:/etc/caddy \
                            alpine:latest \
                            sh -c "/var/www/html/deploy/switch-traffic.sh ${TARGET_PORT}"
                    """

                    // 배포 버전 기록
                    sh """
                        docker run --rm \
                            -v ${DEPLOY_PATH}:/var/www/html \
                            alpine:latest \
                            sh -c "echo '${VERSION_TAG}' >> /var/www/html/deploy/versions.log && tail -10 /var/www/html/deploy/versions.log > /var/www/html/deploy/versions.tmp && mv /var/www/html/deploy/versions.tmp /var/www/html/deploy/versions.log"
                    """

                    echo "Traffic switched to ${TARGET_ENV} (port ${TARGET_PORT})"
                }
            }
        }

        // ==========================================
        // 배포: 이전 컨테이너 정리
        // ==========================================
        stage('Cleanup') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 이전 컨테이너는 롤백을 위해 유지
                    echo "Previous container (${CURRENT_ENV}) kept for rollback"

                    // npm 캐시 정리 (호스트 경로 마운트)
                    sh """
                        docker run --rm \
                            -v ${DEPLOY_PATH}:/var/www/html \
                            alpine:latest \
                            rm -rf /var/www/html/node_modules/.cache 2>/dev/null || true
                    """
                }
            }
        }

        // ==========================================
        // 롤백
        // ==========================================
        stage('Rollback') {
            when {
                expression { params.ACTION == 'rollback' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - rollback"

                    // 현재 활성 포트 확인 (호스트 경로 마운트)
                    def currentPort = sh(
                        script: "docker run --rm -v ${DEPLOY_PATH}:/var/www/html alpine:latest cat /var/www/html/deploy/current-port.txt 2>/dev/null || echo '${BLUE_PORT}'",
                        returnStdout: true
                    ).trim()

                    // 롤백 대상 포트 결정
                    def rollbackPort = (currentPort == env.BLUE_PORT) ? env.GREEN_PORT : env.BLUE_PORT
                    def rollbackEnv = (currentPort == env.BLUE_PORT) ? 'green' : 'blue'

                    // 롤백 컨테이너 상태 확인
                    def rollbackRunning = sh(
                        script: "docker ps -q -f name=${PROJECT_NAME}-${rollbackEnv}",
                        returnStdout: true
                    ).trim()

                    if (!rollbackRunning) {
                        error("No previous version available for rollback")
                    }

                    // 트래픽 전환 (호스트 경로 마운트)
                    sh """
                        docker run --rm \
                            -v ${DEPLOY_PATH}:/var/www/html \
                            -v /etc/caddy:/etc/caddy \
                            alpine:latest \
                            sh -c "/var/www/html/deploy/switch-traffic.sh ${rollbackPort}"
                    """

                    echo "Rolled back to ${rollbackEnv} (port ${rollbackPort})"
                }
            }
        }
    }

    post {
        success {
            script {
                def message = ""
                switch(params.ACTION) {
                    case 'deploy':
                        message = "배포 성공: ${PROJECT_NAME} v${env.VERSION_TAG ?: 'N/A'}"
                        break
                    case 'rollback':
                        message = "롤백 성공: ${PROJECT_NAME}"
                        break
                    case 'build-base':
                        message = "Base 이미지 빌드 성공: ${DOCKER_IMAGE_BASE}"
                        break
                }

                try {
                    slackSend(
                        channel: env.SLACK_CHANNEL,
                        color: 'good',
                        message: """
                            *${message}*
                            - Branch: ${params.BRANCH}
                            - Build: #${BUILD_NUMBER}
                            - URL: https://blogs.shaul.link
                        """.stripIndent()
                    )
                } catch (Exception e) {
                    echo "Slack notification skipped: ${e.message}"
                }
            }
        }
        failure {
            script {
                def message = ""
                switch(params.ACTION) {
                    case 'deploy':
                        message = "배포 실패: ${PROJECT_NAME}"
                        break
                    case 'rollback':
                        message = "롤백 실패: ${PROJECT_NAME}"
                        break
                    case 'build-base':
                        message = "Base 이미지 빌드 실패: ${DOCKER_IMAGE_BASE}"
                        break
                }

                try {
                    slackSend(
                        channel: env.SLACK_CHANNEL,
                        color: 'danger',
                        message: """
                            *${message}*
                            - Branch: ${params.BRANCH}
                            - Build: #${BUILD_NUMBER}
                            - Console: ${BUILD_URL}console
                        """.stripIndent()
                    )
                } catch (Exception e) {
                    echo "Slack notification skipped: ${e.message}"
                }

                // 배포 실패 시 자동 롤백 시도
                if (params.ACTION == 'deploy') {
                    echo "Attempting automatic rollback..."
                    build job: env.JOB_NAME, parameters: [
                        string(name: 'ACTION', value: 'rollback'),
                        string(name: 'BRANCH', value: params.BRANCH)
                    ], wait: false
                }
            }
        }
    }
}
