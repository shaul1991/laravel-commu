<x-layouts.app title="Tech Blog - 개발자를 위한 기술 블로그">
    {{-- Hero Section - Featured Article --}}
    <section class="mb-8 lg:mb-12">
        <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
            {{-- Main Featured Post --}}
            <div class="lg:row-span-2">
                <article class="card group relative h-full overflow-hidden">
                    <a href="#" class="block h-full">
                        <div class="relative h-full min-h-[300px] lg:min-h-[400px]">
                            {{-- Background Image --}}
                            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-secondary-600">
                                <div class="absolute inset-0 bg-black/30"></div>
                            </div>

                            {{-- Content --}}
                            <div class="relative flex h-full flex-col justify-end p-6 lg:p-8">
                                <div class="mb-3">
                                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        Featured
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-white lg:text-3xl">
                                    Laravel 12와 PHP 8.4로 만드는 현대적인 웹 애플리케이션
                                </h2>
                                <p class="mt-3 text-sm text-white/80 line-clamp-2 lg:text-base">
                                    최신 Laravel과 PHP의 새로운 기능들을 활용하여 확장 가능하고 유지보수가 쉬운 웹 애플리케이션을 구축하는 방법을 알아봅니다.
                                </p>
                                <div class="mt-4 flex items-center gap-4 text-sm text-white/70">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-medium">JK</div>
                                        <span>김지훈</span>
                                    </div>
                                    <span>2026.01.15</span>
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        12분
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            </div>

            {{-- Secondary Featured Posts --}}
            <div class="space-y-4 lg:space-y-6">
                <article class="card group overflow-hidden transition-shadow hover:shadow-lg">
                    <a href="#" class="flex gap-4 p-4">
                        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 lg:h-28 lg:w-28">
                        </div>
                        <div class="flex flex-1 flex-col justify-center">
                            <span class="text-xs font-semibold text-green-600">Backend</span>
                            <h3 class="mt-1 font-bold text-neutral-900 line-clamp-2 group-hover:text-primary-600">
                                Redis를 활용한 효율적인 캐싱 전략
                            </h3>
                            <div class="mt-2 flex items-center gap-3 text-xs text-neutral-500">
                                <span>박개발</span>
                                <span>8분</span>
                            </div>
                        </div>
                    </a>
                </article>

                <article class="card group overflow-hidden transition-shadow hover:shadow-lg">
                    <a href="#" class="flex gap-4 p-4">
                        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 lg:h-28 lg:w-28">
                        </div>
                        <div class="flex flex-1 flex-col justify-center">
                            <span class="text-xs font-semibold text-blue-600">Frontend</span>
                            <h3 class="mt-1 font-bold text-neutral-900 line-clamp-2 group-hover:text-primary-600">
                                React Server Components 실전 가이드
                            </h3>
                            <div class="mt-2 flex items-center gap-3 text-xs text-neutral-500">
                                <span>이프론트</span>
                                <span>15분</span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </div>
    </section>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        {{-- Main Content - Articles --}}
        <main class="lg:col-span-8">
            {{-- Category Tabs --}}
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-neutral-900">최신 아티클</h2>
                <div class="hidden gap-1 rounded-lg border border-neutral-200 bg-neutral-50 p-1 sm:flex">
                    <button class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-neutral-900 shadow-sm">
                        전체
                    </button>
                    <button class="rounded-md px-3 py-1.5 text-sm font-medium text-neutral-500 hover:text-neutral-900">
                        Backend
                    </button>
                    <button class="rounded-md px-3 py-1.5 text-sm font-medium text-neutral-500 hover:text-neutral-900">
                        Frontend
                    </button>
                    <button class="rounded-md px-3 py-1.5 text-sm font-medium text-neutral-500 hover:text-neutral-900">
                        DevOps
                    </button>
                </div>
            </div>

            {{-- Mobile Category Pills --}}
            <div class="mb-6 flex gap-2 overflow-x-auto pb-2 sm:hidden">
                <span class="flex-shrink-0 rounded-full bg-primary-600 px-4 py-1.5 text-sm font-medium text-white">전체</span>
                <span class="flex-shrink-0 rounded-full bg-neutral-100 px-4 py-1.5 text-sm font-medium text-neutral-600">Backend</span>
                <span class="flex-shrink-0 rounded-full bg-neutral-100 px-4 py-1.5 text-sm font-medium text-neutral-600">Frontend</span>
                <span class="flex-shrink-0 rounded-full bg-neutral-100 px-4 py-1.5 text-sm font-medium text-neutral-600">DevOps</span>
                <span class="flex-shrink-0 rounded-full bg-neutral-100 px-4 py-1.5 text-sm font-medium text-neutral-600">AI/ML</span>
            </div>

            {{-- Article Grid --}}
            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.article-card
                    href="#"
                    category="Backend"
                    title="PostgreSQL 18 성능 튜닝 완벽 가이드"
                    excerpt="PostgreSQL 18의 새로운 기능과 함께 대용량 데이터베이스 성능을 최적화하는 방법을 단계별로 알아봅니다."
                    author="김디비"
                    date="2026.01.14"
                    readTime="10"
                    :tags="['postgresql', 'database', 'performance']"
                    :featured="true"
                />

                <x-ui.article-card
                    href="#"
                    category="Frontend"
                    title="Tailwind CSS 4 마이그레이션 가이드"
                    excerpt="Tailwind CSS 3에서 4로 마이그레이션할 때 알아야 할 변경사항과 새로운 기능들을 정리했습니다."
                    author="박스타일"
                    date="2026.01.13"
                    readTime="7"
                    :tags="['tailwindcss', 'css', 'frontend']"
                    :featured="true"
                />

                <x-ui.article-card
                    href="#"
                    category="DevOps"
                    title="Docker Compose로 개발 환경 구축하기"
                    excerpt="Docker Compose를 활용하여 PostgreSQL, Redis, MongoDB를 포함한 완벽한 개발 환경을 구축하는 방법."
                    author="이데브옵스"
                    date="2026.01.12"
                    readTime="8"
                    :tags="['docker', 'devops', 'infrastructure']"
                    :featured="true"
                />

                <x-ui.article-card
                    href="#"
                    category="AI"
                    title="Claude API를 활용한 코드 리뷰 자동화"
                    excerpt="AI를 활용하여 코드 리뷰 프로세스를 자동화하고 개발 생산성을 높이는 방법을 소개합니다."
                    author="최에이아이"
                    date="2026.01.11"
                    readTime="12"
                    :tags="['ai', 'claude', 'automation']"
                    :featured="true"
                />

                <x-ui.article-card
                    href="#"
                    category="Backend"
                    title="Laravel Queue 심층 분석"
                    excerpt="Laravel의 Queue 시스템 내부 동작 원리와 대규모 시스템에서의 활용 패턴을 분석합니다."
                    author="정큐"
                    date="2026.01.10"
                    readTime="15"
                    :tags="['laravel', 'queue', 'redis']"
                    :featured="true"
                />

                <x-ui.article-card
                    href="#"
                    category="Frontend"
                    title="Vue 3 Composition API 패턴 모음"
                    excerpt="실무에서 바로 사용할 수 있는 Vue 3 Composition API 패턴과 커스텀 훅 모음입니다."
                    author="한뷰"
                    date="2026.01.09"
                    readTime="9"
                    :tags="['vue', 'javascript', 'patterns']"
                    :featured="true"
                />
            </div>

            {{-- Load More --}}
            <div class="mt-10 text-center">
                <x-ui.button variant="outline" size="lg">
                    더 많은 아티클 보기
                </x-ui.button>
            </div>
        </main>

        {{-- Sidebar --}}
        <aside class="lg:col-span-4">
            <div class="space-y-6">
                {{-- Series Section --}}
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-900">인기 시리즈</h3>
                            <a href="#" class="text-xs text-primary-600 hover:underline">전체보기</a>
                        </div>
                    </x-slot:header>
                    <div class="space-y-4">
                        <a href="#" class="group block">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-primary-100 text-sm font-bold text-primary-600">
                                    1
                                </div>
                                <div>
                                    <h4 class="font-medium text-neutral-900 group-hover:text-primary-600">
                                        Laravel 마스터 클래스
                                    </h4>
                                    <p class="mt-0.5 text-xs text-neutral-500">12개 아티클 · 진행중</p>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="group block">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-green-100 text-sm font-bold text-green-600">
                                    2
                                </div>
                                <div>
                                    <h4 class="font-medium text-neutral-900 group-hover:text-primary-600">
                                        실전 Docker & Kubernetes
                                    </h4>
                                    <p class="mt-0.5 text-xs text-neutral-500">8개 아티클 · 완료</p>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="group block">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-purple-100 text-sm font-bold text-purple-600">
                                    3
                                </div>
                                <div>
                                    <h4 class="font-medium text-neutral-900 group-hover:text-primary-600">
                                        AI 개발자 되기
                                    </h4>
                                    <p class="mt-0.5 text-xs text-neutral-500">6개 아티클 · 진행중</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </x-ui.card>

                {{-- Categories --}}
                <x-ui.card>
                    <x-slot:header>
                        <h3 class="text-sm font-semibold text-neutral-900">카테고리</h3>
                    </x-slot:header>
                    <div class="space-y-2">
                        <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Backend
                            </span>
                            <span class="text-xs text-neutral-400">42</span>
                        </a>
                        <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                Frontend
                            </span>
                            <span class="text-xs text-neutral-400">38</span>
                        </a>
                        <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                DevOps
                            </span>
                            <span class="text-xs text-neutral-400">24</span>
                        </a>
                        <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                                AI/ML
                            </span>
                            <span class="text-xs text-neutral-400">18</span>
                        </a>
                        <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Database
                            </span>
                            <span class="text-xs text-neutral-400">15</span>
                        </a>
                    </div>
                </x-ui.card>

                {{-- Popular Tags --}}
                <x-ui.card>
                    <x-slot:header>
                        <h3 class="text-sm font-semibold text-neutral-900">인기 태그</h3>
                    </x-slot:header>
                    <div class="flex flex-wrap gap-2">
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">laravel</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">php</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">javascript</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">typescript</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">vue</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">react</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">docker</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">postgresql</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">redis</a>
                        <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600">api</a>
                    </div>
                </x-ui.card>

                {{-- Newsletter --}}
                <x-ui.card class="bg-gradient-to-br from-primary-50 to-secondary-50 border-0">
                    <div class="text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900">뉴스레터 구독</h3>
                        <p class="mt-1 text-sm text-neutral-600">매주 엄선된 기술 아티클을 받아보세요</p>
                        <div class="mt-4">
                            <x-ui.input type="email" placeholder="이메일 주소" class="text-center" />
                            <x-ui.button class="mt-2 w-full">구독하기</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </aside>
    </div>
</x-layouts.app>
