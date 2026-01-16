<x-layouts.app>
    <x-slot:title>{{ '@' . $username }} 프로필</x-slot:title>

    <div class="py-8" x-data="{ tab: 'articles' }">
        <div class="max-w-4xl mx-auto">
            {{-- Profile Header --}}
            <div class="card p-8 mb-6">
                <div class="flex items-start gap-6">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
                        김
                    </div>

                    {{-- Info --}}
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-neutral-900">김개발</h1>
                                <p class="text-neutral-500">{{ '@' . $username }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="btn-primary">팔로우</button>
                                <button class="btn-outline p-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p class="mt-3 text-neutral-700">
                            5년차 백엔드 개발자입니다. Laravel과 PHP를 주로 다루며, 깔끔한 코드와 좋은 설계에 관심이 많습니다.
                            개발 관련 글을 꾸준히 작성하고 있어요.
                        </p>

                        {{-- Meta --}}
                        <div class="flex items-center gap-4 mt-4 text-sm text-neutral-500">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                서울, 대한민국
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                github.com/devkim
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                2023년 3월 가입
                            </span>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-6 mt-4">
                            <button class="hover:text-primary-600">
                                <span class="font-bold text-neutral-900">1,234</span>
                                <span class="text-neutral-500">팔로워</span>
                            </button>
                            <button class="hover:text-primary-600">
                                <span class="font-bold text-neutral-900">567</span>
                                <span class="text-neutral-500">팔로잉</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-neutral-200 mb-6">
                <nav class="flex gap-8">
                    <button
                        @click="tab = 'articles'"
                        :class="tab === 'articles' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        아티클 <span class="text-sm">(15)</span>
                    </button>
                    <button
                        @click="tab = 'comments'"
                        :class="tab === 'comments' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        댓글 <span class="text-sm">(48)</span>
                    </button>
                    <button
                        @click="tab = 'likes'"
                        :class="tab === 'likes' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        좋아요 <span class="text-sm">(123)</span>
                    </button>
                </nav>
            </div>

            {{-- Articles Tab --}}
            <div x-show="tab === 'articles'" class="space-y-4">
                @php
                    $articles = [
                        ['title' => 'Laravel 12에서 새롭게 바뀐 기능들', 'excerpt' => 'Laravel 12가 출시되면서 많은 새로운 기능들이 추가되었습니다...', 'date' => '3시간 전', 'views' => '1.2K', 'comments' => 23, 'slug' => 'laravel-12-features'],
                        ['title' => 'PHP 8.4 새로운 기능 살펴보기', 'excerpt' => 'PHP 8.4에서 추가된 새로운 기능들을 하나씩 알아봅시다...', 'date' => '1일 전', 'views' => '892', 'comments' => 15, 'slug' => 'php-8-4-features'],
                        ['title' => 'Docker 컨테이너 최적화 팁', 'excerpt' => '프로덕션 환경에서 Docker를 최적화하는 방법을 정리했습니다...', 'date' => '3일 전', 'views' => '567', 'comments' => 8, 'slug' => 'docker-optimization'],
                    ];
                @endphp

                @foreach($articles as $article)
                <a href="/articles/{{ $article['slug'] }}" class="card block p-5 hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-neutral-900 mb-2">{{ $article['title'] }}</h3>
                    <p class="text-sm text-neutral-600 mb-3">{{ $article['excerpt'] }}</p>
                    <div class="flex items-center gap-4 text-sm text-neutral-500">
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
                </a>
                @endforeach
            </div>

            {{-- Comments Tab --}}
            <div x-show="tab === 'comments'" x-cloak class="space-y-4">
                @php
                    $comments = [
                        ['content' => '정말 유익한 글이네요! Laravel 12 업그레이드 할 때 많은 도움이 될 것 같습니다.', 'article' => 'Next.js 14 새로운 기능', 'date' => '2시간 전'],
                        ['content' => '저도 비슷한 경험이 있어서 공감이 많이 됩니다. 특히 테스트 코드 부분이요.', 'article' => 'TDD로 개발하기', 'date' => '5시간 전'],
                        ['content' => 'Docker multi-stage 빌드 팁 감사합니다!', 'article' => 'Docker 이미지 최적화', 'date' => '1일 전'],
                    ];
                @endphp

                @foreach($comments as $comment)
                <div class="card p-5">
                    <p class="text-neutral-700 mb-3">{{ $comment['content'] }}</p>
                    <div class="flex items-center justify-between text-sm">
                        <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">{{ $comment['article'] }}</a>
                        <span class="text-neutral-500">{{ $comment['date'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Likes Tab --}}
            <div x-show="tab === 'likes'" x-cloak class="space-y-4">
                @php
                    $likes = [
                        ['title' => 'React 19 RC 살펴보기', 'author' => '박프론트', 'date' => '1시간 전', 'slug' => 'react-19-rc'],
                        ['title' => 'Rust로 CLI 도구 만들기', 'author' => '최러스트', 'date' => '3시간 전', 'slug' => 'rust-cli'],
                        ['title' => 'GitHub Actions 활용법', 'author' => '정데브옵스', 'date' => '1일 전', 'slug' => 'github-actions'],
                    ];
                @endphp

                @foreach($likes as $like)
                <a href="/articles/{{ $like['slug'] }}" class="card block p-5 hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-neutral-900 mb-2">{{ $like['title'] }}</h3>
                    <div class="flex items-center gap-3 text-sm text-neutral-500">
                        <span class="font-medium text-neutral-700">{{ $like['author'] }}</span>
                        <span>좋아요 {{ $like['date'] }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
