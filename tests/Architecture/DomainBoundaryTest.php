<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 도메인 아키텍처 경계 검증 테스트
 *
 * Core Domain은 외부 의존성 없이 순수한 비즈니스 로직만 포함해야 합니다.
 * Aggregator는 Core Domain을 조합하고 Application 계층과 통신합니다.
 */
final class DomainBoundaryTest extends TestCase
{
    private const CORE_DOMAIN_PATH = 'app/Domain/Core';

    private const AGGREGATOR_PATH = 'app/Domain/Aggregator';

    /**
     * 허용된 Core Domain 의존성 패턴
     */
    private const ALLOWED_CORE_DEPENDENCIES = [
        'App\\Domain\\Core\\',           // 같은 Core Domain 참조 허용
        'DateTimeImmutable',              // PHP 내장 타입
        'DateTime',
        'DateTimeInterface',
        'Throwable',
        'Exception',
        'InvalidArgumentException',
        'RuntimeException',
        'DomainException',
        'LogicException',
    ];

    /**
     * Core Domain이 Infrastructure, Application 계층에 의존하지 않는지 검증
     */
    #[Test]
    public function core_domain_does_not_depend_on_infrastructure(): void
    {
        $violations = [];
        $files = $this->getPhpFilesInDirectory(base_path(self::CORE_DOMAIN_PATH));

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // Infrastructure 의존성 검사
            if (preg_match('/use\s+App\\\\Infrastructure\\\\/', $content)) {
                $violations[] = "{$relativePath}: Infrastructure 계층 의존성 발견";
            }

            // Eloquent 모델 직접 사용 검사
            if (preg_match('/use\s+Illuminate\\\\Database\\\\Eloquent\\\\/', $content)) {
                $violations[] = "{$relativePath}: Eloquent 모델 직접 의존성 발견";
            }

            // Laravel Facade 사용 검사
            if (preg_match('/use\s+Illuminate\\\\Support\\\\Facades\\\\/', $content)) {
                $violations[] = "{$relativePath}: Laravel Facade 의존성 발견";
            }

            // HTTP 관련 의존성 검사
            if (preg_match('/use\s+Illuminate\\\\Http\\\\/', $content)) {
                $violations[] = "{$relativePath}: HTTP 계층 의존성 발견";
            }
        }

        $this->assertEmpty(
            $violations,
            "Core Domain 경계 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * Core Domain 엔티티가 AggregateRoot 또는 순수 엔티티인지 검증
     */
    #[Test]
    public function core_domain_entities_follow_ddd_patterns(): void
    {
        $entityFiles = $this->getPhpFilesInDirectory(base_path(self::CORE_DOMAIN_PATH), 'Entities');
        $violations = [];

        foreach ($entityFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // private constructor 패턴 확인 (팩토리 메서드 강제)
            if (! preg_match('/private\s+function\s+__construct/', $content)) {
                $violations[] = "{$relativePath}: private constructor 패턴 미적용";
            }

            // 팩토리 메서드 존재 확인
            if (! preg_match('/public\s+static\s+function\s+(create|register|reconstitute)/', $content)) {
                $violations[] = "{$relativePath}: 팩토리 메서드 미정의";
            }
        }

        $this->assertEmpty(
            $violations,
            "DDD 패턴 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * Value Objects가 불변성을 유지하는지 검증
     */
    #[Test]
    public function value_objects_are_immutable(): void
    {
        $valueObjectFiles = $this->getPhpFilesInDirectory(base_path(self::CORE_DOMAIN_PATH), 'ValueObjects');
        $violations = [];

        foreach ($valueObjectFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // final class 확인 (상속 금지)
            if (preg_match('/^class\s+\w+/m', $content) && ! preg_match('/final\s+class/', $content)) {
                // Enum은 제외
                if (! preg_match('/^enum\s+\w+/m', $content)) {
                    $violations[] = "{$relativePath}: Value Object는 final class여야 함";
                }
            }

            // readonly 속성 또는 private 속성 확인
            if (preg_match('/public\s+(?!readonly)\w+\s+\$/', $content)) {
                $violations[] = "{$relativePath}: Value Object 속성은 readonly 또는 private이어야 함";
            }
        }

        $this->assertEmpty(
            $violations,
            "Value Object 불변성 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * Repository Interface가 Domain 계층에만 정의되어 있는지 검증
     */
    #[Test]
    public function repository_interfaces_are_in_domain_layer(): void
    {
        $repoFiles = $this->getPhpFilesInDirectory(base_path(self::CORE_DOMAIN_PATH), 'Repositories');
        $violations = [];

        foreach ($repoFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // Interface인지 확인
            if (! preg_match('/interface\s+\w+RepositoryInterface/', $content)) {
                $violations[] = "{$relativePath}: Repository는 Interface여야 함";
            }

            // 구현체가 아닌지 확인
            if (preg_match('/class\s+\w+Repository\s/', $content)) {
                $violations[] = "{$relativePath}: Repository 구현체가 Domain 계층에 있음";
            }
        }

        $this->assertEmpty(
            $violations,
            "Repository 패턴 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * Aggregator가 Application Contracts만 의존하는지 검증
     */
    #[Test]
    public function aggregator_depends_only_on_contracts(): void
    {
        $aggregatorFiles = $this->getPhpFilesInDirectory(base_path(self::AGGREGATOR_PATH));
        $violations = [];

        foreach ($aggregatorFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // Infrastructure 직접 의존성 검사
            if (preg_match('/use\s+App\\\\Infrastructure\\\\(?!.*Interface)/', $content)) {
                $violations[] = "{$relativePath}: Aggregator가 Infrastructure 구현체에 의존";
            }

            // Eloquent 직접 사용 검사
            if (preg_match('/use\s+Illuminate\\\\Database\\\\Eloquent\\\\Model/', $content)) {
                $violations[] = "{$relativePath}: Aggregator가 Eloquent Model에 직접 의존";
            }
        }

        $this->assertEmpty(
            $violations,
            "Aggregator 의존성 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * Domain Events가 올바르게 정의되어 있는지 검증
     */
    #[Test]
    public function domain_events_implement_interface(): void
    {
        $eventFiles = $this->getPhpFilesInDirectory(base_path(self::CORE_DOMAIN_PATH), 'Events');
        $violations = [];

        foreach ($eventFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path().'/', '', $file);

            // DomainEvent interface 구현 확인
            if (! preg_match('/implements\s+.*DomainEvent/', $content)) {
                $violations[] = "{$relativePath}: DomainEvent interface 미구현";
            }

            // final class 확인
            if (! preg_match('/final\s+(readonly\s+)?class/', $content)) {
                $violations[] = "{$relativePath}: Domain Event는 final class여야 함";
            }
        }

        $this->assertEmpty(
            $violations,
            "Domain Event 패턴 위반:\n".implode("\n", $violations)
        );
    }

    /**
     * 디렉토리 내 PHP 파일 목록 조회
     */
    private function getPhpFilesInDirectory(string $directory, ?string $subDirectory = null): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $pattern = $subDirectory
            ? "{$directory}/**/{$subDirectory}/*.php"
            : "{$directory}/**/*.php";

        $files = glob($pattern, GLOB_BRACE) ?: [];

        // 재귀적으로 하위 디렉토리 검색
        if (! $subDirectory) {
            $additionalFiles = glob("{$directory}/*/*.php") ?: [];
            $files = array_merge($files, $additionalFiles);
        }

        return array_unique($files);
    }
}
