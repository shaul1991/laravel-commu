{{--
    Article Detail Page - 게시글 상세 페이지 프로토타입

    프로토타입 목적으로 모든 데이터는 하드코딩되어 있습니다.
--}}

@php
    // 프로토타입 데이터
    $article = [
        'title' => 'PostgreSQL 18 성능 튜닝 완벽 가이드',
        'category' => 'Backend',
        'thumbnail' => null, // 실제 이미지가 없으므로 그라데이션 사용
        'author' => [
            'name' => '김개발',
            'avatar' => null,
            'bio' => '10년차 백엔드 개발자. PostgreSQL과 Laravel을 사랑합니다. 현재 스타트업에서 CTO로 일하고 있습니다.',
            'profileUrl' => '#',
            'socialLinks' => [
                ['platform' => 'github', 'url' => 'https://github.com'],
                ['platform' => 'twitter', 'url' => 'https://twitter.com'],
            ],
        ],
        'createdAt' => '2026.01.15',
        'updatedAt' => '2026.01.16',
        'readTime' => 15,
        'views' => 1234,
        'tags' => ['postgresql', 'database', 'performance', 'backend', 'optimization'],
        'series' => [
            'title' => 'PostgreSQL 마스터하기',
            'url' => '#',
            'current' => 3,
            'total' => 5,
            'prevUrl' => '#',
            'prevTitle' => 'Part 2: 인덱스 설계',
            'nextUrl' => '#',
            'nextTitle' => 'Part 4: 복제와 고가용성',
        ],
    ];

    // TOC 데이터
    $tocItems = [
        [
            'id' => 'introduction',
            'title' => '들어가며',
            'children' => [],
        ],
        [
            'id' => 'query-optimization',
            'title' => '쿼리 최적화',
            'children' => [
                ['id' => 'explain-analyze', 'title' => 'EXPLAIN ANALYZE 활용'],
                ['id' => 'index-usage', 'title' => '인덱스 활용'],
            ],
        ],
        [
            'id' => 'configuration',
            'title' => '설정 튜닝',
            'children' => [
                ['id' => 'memory-settings', 'title' => '메모리 설정'],
                ['id' => 'connection-pooling', 'title' => '커넥션 풀링'],
            ],
        ],
        [
            'id' => 'monitoring',
            'title' => '모니터링',
            'children' => [],
        ],
        [
            'id' => 'conclusion',
            'title' => '마치며',
            'children' => [],
        ],
    ];

    // 카테고리 색상
    $categoryColors = [
        'backend' => 'bg-green-100 text-green-800',
        'frontend' => 'bg-blue-100 text-blue-800',
        'devops' => 'bg-orange-100 text-orange-800',
        'ai' => 'bg-purple-100 text-purple-800',
        'database' => 'bg-red-100 text-red-800',
    ];
    $categoryClass = $categoryColors[strtolower($article['category'])] ?? 'bg-primary-100 text-primary-800';
@endphp

<x-layouts.app>
    <x-slot name="title">{{ $article['title'] }} - Community</x-slot>

    {{-- Hero Section --}}
    <header class="border-b border-neutral-200 bg-white py-8 lg:py-12">
        <div class="container-main">
            <div class="mx-auto max-w-4xl">
                {{-- Breadcrumb --}}
                <nav class="mb-4 flex items-center gap-2 text-sm text-neutral-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-primary-600">홈</a>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="#" class="hover:text-primary-600">{{ $article['category'] }}</a>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-neutral-700">현재 글</span>
                </nav>

                {{-- Category Badge --}}
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $categoryClass }}">
                    {{ $article['category'] }}
                </span>

                {{-- Title --}}
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-neutral-900 lg:text-4xl">
                    {{ $article['title'] }}
                </h1>

                {{-- Meta --}}
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-500">
                    <span>{{ $article['createdAt'] }}</span>
                    <span class="hidden lg:inline text-neutral-300">|</span>
                    <span class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $article['readTime'] }}분
                    </span>
                    <span class="hidden lg:inline text-neutral-300">|</span>
                    <span class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ number_format($article['views']) }}
                    </span>
                </div>

                {{-- Author --}}
                <div class="mt-6 flex items-center gap-4">
                    @if($article['author']['avatar'])
                        <img src="{{ $article['author']['avatar'] }}" alt="{{ $article['author']['name'] }}" class="h-12 w-12 rounded-full object-cover">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-lg font-bold text-white">
                            {{ mb_strtoupper(mb_substr($article['author']['name'], 0, 1), 'UTF-8') }}
                        </div>
                    @endif
                    <div>
                        <a href="{{ $article['author']['profileUrl'] }}" class="font-semibold text-neutral-900 hover:text-primary-600">
                            {{ $article['author']['name'] }}
                        </a>
                        <p class="text-sm text-neutral-500">Backend Developer</p>
                    </div>
                </div>

                {{-- Thumbnail --}}
                <div class="mt-8 aspect-video overflow-hidden rounded-2xl bg-neutral-100">
                    @if($article['thumbnail'])
                        <img src="{{ $article['thumbnail'] }}" alt="{{ $article['title'] }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="container-main py-8 lg:py-12">
        {{-- Series Navigation --}}
        @if($article['series'])
            <div class="mx-auto max-w-4xl lg:max-w-none">
                <x-ui.series-nav
                    :seriesTitle="$article['series']['title']"
                    :seriesUrl="$article['series']['url']"
                    :current="$article['series']['current']"
                    :total="$article['series']['total']"
                    :prevUrl="$article['series']['prevUrl']"
                    :prevTitle="$article['series']['prevTitle']"
                    :nextUrl="$article['series']['nextUrl']"
                    :nextTitle="$article['series']['nextTitle']"
                />
            </div>
        @endif

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            {{-- Article Body --}}
            <article class="lg:col-span-9">
                <div class="prose-article mx-auto max-w-4xl lg:mx-0 lg:max-w-none">
                    {{-- Introduction --}}
                    <h2 id="introduction">들어가며</h2>
                    <p>
                        PostgreSQL은 강력한 오픈소스 관계형 데이터베이스입니다. 이 글에서는 PostgreSQL 18의 새로운 기능과 함께
                        실무에서 바로 적용할 수 있는 성능 튜닝 기법들을 살펴보겠습니다.
                    </p>
                    <p>
                        성능 튜닝은 단순히 설정 값을 변경하는 것이 아니라, 시스템의 특성을 이해하고 워크로드에 맞는 최적의 설정을 찾아가는 과정입니다.
                    </p>

                    {{-- Query Optimization --}}
                    <h2 id="query-optimization">쿼리 최적화</h2>
                    <p>
                        쿼리 최적화는 데이터베이스 성능 튜닝의 핵심입니다. PostgreSQL은 강력한 쿼리 분석 도구를 제공합니다.
                    </p>

                    <h3 id="explain-analyze">EXPLAIN ANALYZE 활용</h3>
                    <p>
                        <code>EXPLAIN ANALYZE</code> 명령어를 사용하면 쿼리의 실행 계획과 실제 실행 시간을 확인할 수 있습니다.
                    </p>

                    <x-ui.code-block language="sql" filename="query_analysis.sql">
EXPLAIN ANALYZE
SELECT u.name, COUNT(o.id) as order_count
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE u.created_at > '2025-01-01'
GROUP BY u.id, u.name
ORDER BY order_count DESC
LIMIT 10;</x-ui.code-block>

                    <p>
                        위 쿼리의 실행 계획을 분석하면 어떤 부분에서 병목이 발생하는지 파악할 수 있습니다.
                    </p>

                    <h3 id="index-usage">인덱스 활용</h3>
                    <p>
                        적절한 인덱스 설계는 쿼리 성능에 큰 영향을 미칩니다. PostgreSQL 18에서는 더욱 향상된 인덱스 기능을 제공합니다.
                    </p>

                    <blockquote>
                        <p>
                            "인덱스는 양날의 검입니다. 읽기 성능은 향상시키지만, 쓰기 성능은 저하시킬 수 있습니다.
                            워크로드를 분석하여 적절한 인덱스를 선택하세요."
                        </p>
                    </blockquote>

                    <x-ui.code-block language="sql">
-- 복합 인덱스 생성
CREATE INDEX CONCURRENTLY idx_users_created_status
ON users (created_at, status)
WHERE status = 'active';

-- 인덱스 사용 확인
SELECT indexrelname, idx_scan, idx_tup_read
FROM pg_stat_user_indexes
WHERE schemaname = 'public';</x-ui.code-block>

                    {{-- Configuration --}}
                    <h2 id="configuration">설정 튜닝</h2>
                    <p>
                        PostgreSQL의 설정 파일(<code>postgresql.conf</code>)에는 수백 개의 설정 항목이 있습니다.
                        이 중 성능에 가장 큰 영향을 미치는 설정들을 살펴보겠습니다.
                    </p>

                    <h3 id="memory-settings">메모리 설정</h3>
                    <p>
                        메모리 설정은 PostgreSQL 성능의 핵심입니다. 다음은 권장 설정값입니다:
                    </p>

                    <ul>
                        <li><code>shared_buffers</code>: 총 메모리의 25% (최대 8GB)</li>
                        <li><code>effective_cache_size</code>: 총 메모리의 50-75%</li>
                        <li><code>work_mem</code>: 워크로드에 따라 64MB-256MB</li>
                        <li><code>maintenance_work_mem</code>: 512MB-1GB</li>
                    </ul>

                    <x-ui.code-block language="ini" filename="postgresql.conf">
# Memory Settings
shared_buffers = 4GB
effective_cache_size = 12GB
work_mem = 128MB
maintenance_work_mem = 1GB

# WAL Settings
wal_buffers = 64MB
checkpoint_completion_target = 0.9</x-ui.code-block>

                    <h3 id="connection-pooling">커넥션 풀링</h3>
                    <p>
                        PostgreSQL은 프로세스 기반 아키텍처를 사용하므로, 커넥션 풀링은 필수입니다.
                        PgBouncer나 Pgpool-II를 사용하는 것을 권장합니다.
                    </p>

                    {{-- Monitoring --}}
                    <h2 id="monitoring">모니터링</h2>
                    <p>
                        성능 튜닝 후에는 지속적인 모니터링이 필요합니다. PostgreSQL은 다양한 통계 뷰를 제공합니다.
                    </p>

                    <x-ui.code-block language="sql">
-- 슬로우 쿼리 확인
SELECT query, calls, total_time, mean_time
FROM pg_stat_statements
ORDER BY total_time DESC
LIMIT 10;

-- 테이블 통계
SELECT relname, seq_scan, idx_scan,
       n_tup_ins, n_tup_upd, n_tup_del
FROM pg_stat_user_tables
ORDER BY seq_scan DESC;</x-ui.code-block>

                    {{-- Conclusion --}}
                    <h2 id="conclusion">마치며</h2>
                    <p>
                        PostgreSQL 성능 튜닝은 지속적인 과정입니다. 이 글에서 다룬 내용을 바탕으로
                        여러분의 시스템에 맞는 최적의 설정을 찾아가시기 바랍니다.
                    </p>
                    <p>
                        다음 글에서는 PostgreSQL의 복제와 고가용성에 대해 알아보겠습니다.
                    </p>

                    {{-- Tags --}}
                    <div class="mt-12 flex flex-wrap gap-2 border-t border-neutral-200 pt-8">
                        @foreach($article['tags'] as $tag)
                            <a
                                href="#"
                                class="rounded-full bg-neutral-100 px-3 py-1.5 text-sm text-neutral-600 transition-colors hover:bg-primary-100 hover:text-primary-700"
                            >
                                #{{ $tag }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Author Box --}}
                <div class="mx-auto max-w-4xl lg:mx-0 lg:max-w-none">
                    <x-ui.author-box
                        :name="$article['author']['name']"
                        :avatar="$article['author']['avatar']"
                        :bio="$article['author']['bio']"
                        :profileUrl="$article['author']['profileUrl']"
                        :socialLinks="$article['author']['socialLinks']"
                    />
                </div>

                {{-- Related Articles --}}
                <section class="mx-auto mt-12 max-w-4xl lg:mx-0 lg:max-w-none">
                    <h2 class="mb-6 text-xl font-bold text-neutral-900">관련 아티클</h2>
                    <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
                        <x-ui.article-card
                            href="#"
                            category="Backend"
                            title="PostgreSQL 인덱스 설계 전략"
                            excerpt="효율적인 인덱스 설계를 위한 실전 가이드"
                            author="김개발"
                            date="2026.01.10"
                            readTime="12"
                            :tags="['postgresql', 'index']"
                        />
                        <x-ui.article-card
                            href="#"
                            category="Backend"
                            title="Redis를 활용한 캐싱 전략"
                            excerpt="데이터베이스 부하를 줄이는 효과적인 캐싱 방법"
                            author="이캐시"
                            date="2026.01.08"
                            readTime="10"
                            :tags="['redis', 'caching']"
                        />
                        <x-ui.article-card
                            href="#"
                            category="DevOps"
                            title="PostgreSQL 모니터링 with Prometheus"
                            excerpt="Prometheus와 Grafana로 PostgreSQL 모니터링 구축하기"
                            author="박데옵"
                            date="2026.01.05"
                            readTime="15"
                            :tags="['postgresql', 'monitoring']"
                        />
                    </div>
                </section>

                {{-- Comment Section --}}
                <section class="mx-auto mt-12 max-w-4xl lg:mx-0 lg:max-w-none">
                    <x-ui.comment-section :articleSlug="$slug ?? 'postgresql-18-performance-tuning'" />
                </section>
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-3">
                <div class="sticky top-24 space-y-6">
                    {{-- TOC --}}
                    <x-ui.toc :items="$tocItems" />

                    {{-- Share Buttons (Desktop) --}}
                    <div class="hidden rounded-xl border border-neutral-200 bg-white p-4 lg:block">
                        <h3 class="mb-4 text-sm font-semibold text-neutral-900">공유하기</h3>
                        <x-ui.share-buttons
                            url="{{ url()->current() }}"
                            title="{{ $article['title'] }}"
                            layout="horizontal"
                        />
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Mobile Share Buttons --}}
    <div class="fixed bottom-20 right-6 z-30 lg:hidden">
        <x-ui.share-buttons
            url="{{ url()->current() }}"
            title="{{ $article['title'] }}"
            layout="vertical"
        />
    </div>
</x-layouts.app>
