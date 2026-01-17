pipeline {
    agent any

    parameters {
        choice(
            name: 'ACTION',
            choices: ['deploy', 'rollback'],
            description: '실행할 작업 선택'
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
        DOCKER_IMAGE = 'laravel-commu'
        BLUE_PORT = '10000'
        GREEN_PORT = '10001'

        // GitHub 설정
        GIT_REPO = 'git@github.com:shaul1991/laravel-commu.git'
        GIT_CREDENTIALS_ID = 'home-server-deploy'

        // Slack 설정 (Jenkins Slack Plugin 미설정 시 알림 스킵)
        SLACK_CHANNEL = '#deployments'
    }

    stages {
        stage('Checkout') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - ${params.ACTION} (${params.BRANCH})"
                }
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: params.BRANCH]],
                    userRemoteConfigs: [[
                        url: env.GIT_REPO,
                        credentialsId: env.GIT_CREDENTIALS_ID
                    ]]
                ])
            }
        }

        stage('Prepare Environment') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 현재 활성 컨테이너 확인
                    def blueRunning = sh(
                        script: "docker ps -q -f name=${PROJECT_NAME}-blue",
                        returnStdout: true
                    ).trim()

                    def greenRunning = sh(
                        script: "docker ps -q -f name=${PROJECT_NAME}-green",
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

                    // 배포 버전 생성
                    env.DEPLOY_VERSION = sh(
                        script: "git rev-parse --short HEAD",
                        returnStdout: true
                    ).trim()

                    env.DEPLOY_TIMESTAMP = sh(
                        script: "date +%Y%m%d-%H%M%S",
                        returnStdout: true
                    ).trim()

                    env.IMAGE_TAG = "${env.DEPLOY_TIMESTAMP}-${env.DEPLOY_VERSION}"
                }
            }
        }

        stage('Build Docker Image') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                sh """
                    docker build \
                        -f docker/Dockerfile.prod \
                        -t ${DOCKER_IMAGE}:${IMAGE_TAG} \
                        -t ${DOCKER_IMAGE}:latest \
                        .
                """
            }
        }

        stage('Deploy New Container') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 기존 대상 컨테이너 중지 (있다면)
                    sh """
                        docker stop ${PROJECT_NAME}-${TARGET_ENV} 2>/dev/null || true
                        docker rm ${PROJECT_NAME}-${TARGET_ENV} 2>/dev/null || true
                    """

                    // 새 컨테이너 시작
                    sh """
                        docker compose -f docker/docker-compose.prod.yml \
                            --env-file ${DEPLOY_PATH}/.env.prod \
                            up -d ${TARGET_ENV}
                    """
                }
            }
        }

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

        stage('Switch Traffic') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // Caddy 설정 업데이트
                    sh """
                        ${DEPLOY_PATH}/deploy/switch-traffic.sh ${TARGET_PORT}
                    """

                    // 배포 버전 기록
                    sh """
                        echo "${IMAGE_TAG}" >> ${DEPLOY_PATH}/deploy/versions.log
                        # 최근 5개만 유지
                        tail -5 ${DEPLOY_PATH}/deploy/versions.log > ${DEPLOY_PATH}/deploy/versions.tmp
                        mv ${DEPLOY_PATH}/deploy/versions.tmp ${DEPLOY_PATH}/deploy/versions.log
                    """
                }
            }
        }

        stage('Cleanup Old Container') {
            when {
                expression { params.ACTION == 'deploy' }
            }
            steps {
                script {
                    // 이전 컨테이너는 롤백을 위해 유지 (다음 배포 시 삭제됨)
                    echo "Previous container (${CURRENT_ENV}) kept for rollback"

                    // 오래된 이미지 정리 (최근 5개 제외)
                    sh """
                        docker images ${DOCKER_IMAGE} --format '{{.Tag}}' | \
                            grep -v latest | \
                            sort -r | \
                            tail -n +6 | \
                            xargs -r -I {} docker rmi ${DOCKER_IMAGE}:{} 2>/dev/null || true
                    """
                }
            }
        }

        stage('Rollback') {
            when {
                expression { params.ACTION == 'rollback' }
            }
            steps {
                script {
                    currentBuild.displayName = "#${BUILD_NUMBER} - rollback"

                    // 현재 활성 포트 확인
                    def currentPort = sh(
                        script: "cat ${DEPLOY_PATH}/deploy/current-port.txt || echo '${BLUE_PORT}'",
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

                    // 트래픽 전환
                    sh """
                        ${DEPLOY_PATH}/deploy/switch-traffic.sh ${rollbackPort}
                    """

                    echo "Rolled back to ${rollbackEnv} (port ${rollbackPort})"
                }
            }
        }
    }

    post {
        success {
            script {
                def message = params.ACTION == 'deploy'
                    ? "배포 성공: ${PROJECT_NAME} v${env.IMAGE_TAG ?: 'N/A'}"
                    : "롤백 성공: ${PROJECT_NAME}"

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
                def message = params.ACTION == 'deploy'
                    ? "배포 실패: ${PROJECT_NAME}"
                    : "롤백 실패: ${PROJECT_NAME}"

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
