<x-layouts.app>
    <x-slot:title>아티클</x-slot:title>

    <div class="py-8" x-data="{
        view: 'list',
        sort: 'latest',
        category: 'all'
    }">
        <div class="max-w-5xl mx-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">아티클</h1>
                    <p class="mt-1 text-sm text-neutral-600">개발자들의 인사이트와 경험을 공유합니다</p>
                </div>
                <a href="/write" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    글 작성
                </a>
            </div>

            {{-- Filters --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Categories --}}
                <div class="flex items-center gap-2">
                    <button
                        @click="category = 'all'"
                        :class="category === 'all' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        전체
                    </button>
                    <button
                        @click="category = 'tech'"
                        :class="category === 'tech' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        기술
                    </button>
                    <button
                        @click="category = 'career'"
                        :class="category === 'career' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        커리어
                    </button>
                    <button
                        @click="category = 'life'"
                        :class="category === 'life' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        일상
                    </button>
                </div>

                {{-- Sort & View --}}
                <div class="flex items-center gap-3">
                    <select x-model="sort" class="input py-2 text-sm">
                        <option value="latest">최신순</option>
                        <option value="popular">인기순</option>
                        <option value="comments">댓글순</option>
                    </select>
                    <div class="flex items-center border border-neutral-200 rounded-lg overflow-hidden">
                        <button
                            @click="view = 'list'"
                            :class="view === 'list' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-500 hover:bg-neutral-50'"
                            class="p-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <button
                            @click="view = 'grid'"
                            :class="view === 'grid' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-500 hover:bg-neutral-50'"
                            class="p-2"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- List View --}}
            <div x-show="view === 'list'" class="space-y-4">
                @php
                    $articles = [
                        ['title' => 'Laravel 12에서 새롭게 바뀐 기능들', 'excerpt' => 'Laravel 12가 출시되면서 많은 새로운 기능들이 추가되었습니다. 이번 글에서는 주요 변경사항들을 살펴보겠습니다.', 'author' => '김개발', 'date' => '3시간 전', 'views' => '1.2K', 'comments' => 23, 'slug' => 'laravel-12-features', 'tags' => ['Laravel', 'PHP']],
                        ['title' => 'React 19 RC 살펴보기', 'excerpt' => 'React 19 RC가 발표되었습니다. 새로운 훅과 서버 컴포넌트 개선사항을 알아봅시다.', 'author' => '박프론트', 'date' => '5시간 전', 'views' => '892', 'comments' => 15, 'slug' => 'react-19-rc', 'tags' => ['React', 'JavaScript']],
                        ['title' => 'TypeScript 5.3 타입 시스템 개선사항', 'excerpt' => 'TypeScript 5.3에서 개선된 타입 추론과 새로운 유틸리티 타입들을 소개합니다.', 'author' => '이타입', 'date' => '1일 전', 'views' => '756', 'comments' => 12, 'slug' => 'typescript-5-3', 'tags' => ['TypeScript']],
                        ['title' => '주니어 개발자의 성장 이야기', 'excerpt' => '입사 1년차 주니어 개발자가 겪은 성장통과 극복 과정을 공유합니다.', 'author' => '최주니어', 'date' => '2일 전', 'views' => '2.3K', 'comments' => 45, 'slug' => 'junior-growth', 'tags' => ['커리어', '성장']],
                        ['title' => 'Docker 컨테이너 최적화 팁', 'excerpt' => '프로덕션 환경에서 Docker 컨테이너를 최적화하는 실용적인 팁들을 정리했습니다.', 'author' => '정데브옵스', 'date' => '3일 전', 'views' => '567', 'comments' => 8, 'slug' => 'docker-tips', 'tags' => ['Docker', 'DevOps']],
                    ];
                @endphp

                @foreach($articles as $article)
                <a href="/articles/{{ $article['slug'] }}" class="card block p-6 hover:shadow-md transition-shadow">
                    <div class="flex gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                @foreach($article['tags'] as $tag)
                                <span class="text-xs text-primary-600 font-medium">#{{ $tag }}</span>
                                @endforeach
                            </div>
                            <h2 class="text-lg font-bold text-neutral-900 mb-2 group-hover:text-primary-600">{{ $article['title'] }}</h2>
                            <p class="text-neutral-600 text-sm mb-4 line-clamp-2">{{ $article['excerpt'] }}</p>
                            <div class="flex items-center gap-4 text-sm text-neutral-500">
                                <span class="font-medium text-neutral-700">{{ $article['author'] }}</span>
                                <span>{{ $article['date'] }}</span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $article['views'] }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    {{ $article['comments'] }}
                                </span>
                            </div>
                        </div>
                        <div class="w-40 h-28 bg-neutral-100 rounded-lg flex-shrink-0"></div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Grid View --}}
            <div x-show="view === 'grid'" x-cloak class="grid grid-cols-2 gap-6">
                @foreach($articles as $article)
                <a href="/articles/{{ $article['slug'] }}" class="card overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-40 bg-neutral-100"></div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            @foreach($article['tags'] as $tag)
                            <span class="text-xs text-primary-600 font-medium">#{{ $tag }}</span>
                            @endforeach
                        </div>
                        <h2 class="font-bold text-neutral-900 mb-2 line-clamp-2">{{ $article['title'] }}</h2>
                        <p class="text-neutral-600 text-sm mb-3 line-clamp-2">{{ $article['excerpt'] }}</p>
                        <div class="flex items-center justify-between text-sm text-neutral-500">
                            <span class="font-medium text-neutral-700">{{ $article['author'] }}</span>
                            <span>{{ $article['date'] }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-center gap-2 mt-8">
                <button class="p-2 rounded-lg border border-neutral-200 text-neutral-400 cursor-not-allowed">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button class="w-10 h-10 rounded-lg bg-primary-600 text-white font-medium">1</button>
                <button class="w-10 h-10 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium">2</button>
                <button class="w-10 h-10 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium">3</button>
                <span class="px-2 text-neutral-400">...</span>
                <button class="w-10 h-10 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium">10</button>
                <button class="p-2 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>
