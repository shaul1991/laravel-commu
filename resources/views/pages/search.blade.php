<x-layouts.app>
    <x-slot:title>검색</x-slot:title>

    <div class="py-8" x-data="{
        query: new URLSearchParams(window.location.search).get('q') || '',
        type: 'all',
        period: 'all'
    }">
        <div class="max-w-4xl mx-auto">
            {{-- Search Box --}}
            <div class="mb-8">
                <form action="/search" method="GET" class="relative">
                    <input
                        type="text"
                        name="q"
                        x-model="query"
                        placeholder="검색어를 입력하세요..."
                        class="w-full pl-12 pr-4 py-4 text-lg border border-neutral-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                    >
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-6 w-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>
            </div>

            {{-- Results Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-neutral-900">
                        <span x-show="query">"<span x-text="query"></span>" 검색 결과</span>
                        <span x-show="!query">전체 검색</span>
                    </h1>
                    <p class="text-sm text-neutral-600 mt-1">총 <span class="font-medium">24</span>개의 결과</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-neutral-200">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-neutral-600">유형:</span>
                    <button
                        @click="type = 'all'"
                        :class="type === 'all' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        전체
                    </button>
                    <button
                        @click="type = 'articles'"
                        :class="type === 'articles' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        아티클
                    </button>
                    <button
                        @click="type = 'users'"
                        :class="type === 'users' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        사용자
                    </button>
                    <button
                        @click="type = 'tags'"
                        :class="type === 'tags' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        태그
                    </button>
                </div>
                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-sm text-neutral-600">기간:</span>
                    <select x-model="period" class="input py-1.5 text-sm">
                        <option value="all">전체 기간</option>
                        <option value="day">오늘</option>
                        <option value="week">이번 주</option>
                        <option value="month">이번 달</option>
                        <option value="year">올해</option>
                    </select>
                </div>
            </div>

            {{-- Search Results --}}
            <div class="space-y-6">
                {{-- Article Results --}}
                <div x-show="type === 'all' || type === 'articles'">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4" x-show="type === 'all'">아티클 (18)</h2>

                    <div class="space-y-4">
                        @php
                            $results = [
                                ['title' => 'Laravel 12에서 새롭게 바뀐 기능들', 'excerpt' => '...Laravel 12가 출시되면서 많은 새로운 기능들이 추가되었습니다. 이번 글에서는 <mark class="bg-yellow-200">Laravel</mark>의 주요 변경사항들을...', 'author' => '김개발', 'date' => '3시간 전', 'slug' => 'laravel-12-features'],
                                ['title' => 'Laravel Livewire로 실시간 기능 구현하기', 'excerpt' => '...<mark class="bg-yellow-200">Laravel</mark> Livewire를 사용하면 JavaScript 없이도 실시간 인터랙티브 UI를 만들 수 있습니다...', 'author' => '박개발', 'date' => '1일 전', 'slug' => 'laravel-livewire'],
                                ['title' => 'Laravel API 설계 베스트 프랙티스', 'excerpt' => '...RESTful API를 <mark class="bg-yellow-200">Laravel</mark>로 구축할 때 알아야 할 베스트 프랙티스를 정리했습니다...', 'author' => '이백엔드', 'date' => '3일 전', 'slug' => 'laravel-api-best-practices'],
                            ];
                        @endphp

                        @foreach($results as $result)
                        <a href="/articles/{{ $result['slug'] }}" class="card block p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-bold text-neutral-900 mb-2 hover:text-primary-600">{{ $result['title'] }}</h3>
                            <p class="text-sm text-neutral-600 mb-3">{!! $result['excerpt'] !!}</p>
                            <div class="flex items-center gap-3 text-sm text-neutral-500">
                                <span class="font-medium text-neutral-700">{{ $result['author'] }}</span>
                                <span>{{ $result['date'] }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    <button x-show="type === 'all'" class="w-full mt-4 py-3 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                        아티클 더보기 (15개)
                    </button>
                </div>

                {{-- User Results --}}
                <div x-show="type === 'all' || type === 'users'" class="mt-8" :class="{ 'mt-0': type === 'users' }">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4" x-show="type === 'all'">사용자 (3)</h2>

                    <div class="grid grid-cols-3 gap-4">
                        @php
                            $users = [
                                ['name' => '김라라벨', 'username' => 'laravel_kim', 'bio' => 'Laravel 전문 개발자', 'followers' => 234],
                                ['name' => 'Laravel Korea', 'username' => 'laravel_korea', 'bio' => 'Laravel 한국 커뮤니티', 'followers' => 1523],
                                ['name' => '이라벨러', 'username' => 'laraveler', 'bio' => '웹 개발 5년차', 'followers' => 89],
                            ];
                        @endphp

                        @foreach($users as $user)
                        <a href="/@{{ $user['username'] }}" class="card p-4 hover:shadow-md transition-shadow text-center">
                            <div class="w-16 h-16 mx-auto rounded-full bg-neutral-200 mb-3"></div>
                            <h3 class="font-bold text-neutral-900">{{ $user['name'] }}</h3>
                            <p class="text-sm text-neutral-500 mb-2">{{ '@' . $user['username'] }}</p>
                            <p class="text-xs text-neutral-600 mb-3">{{ $user['bio'] }}</p>
                            <p class="text-xs text-neutral-500">팔로워 {{ $user['followers'] }}명</p>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Tag Results --}}
                <div x-show="type === 'all' || type === 'tags'" class="mt-8" :class="{ 'mt-0': type === 'tags' }">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4" x-show="type === 'all'">태그 (3)</h2>

                    <div class="flex flex-wrap gap-3">
                        @php
                            $tags = [
                                ['name' => 'Laravel', 'count' => 156],
                                ['name' => 'Laravel-12', 'count' => 23],
                                ['name' => 'Laravel-Livewire', 'count' => 45],
                            ];
                        @endphp

                        @foreach($tags as $tag)
                        <a href="/search?q=%23{{ $tag['name'] }}" class="card px-4 py-3 hover:shadow-md transition-shadow flex items-center gap-3">
                            <span class="text-2xl text-primary-600">#</span>
                            <div>
                                <h3 class="font-bold text-neutral-900">{{ $tag['name'] }}</h3>
                                <p class="text-sm text-neutral-500">{{ $tag['count'] }}개의 글</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Empty State --}}
            <div x-show="false" class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900">검색 결과가 없습니다</h3>
                <p class="mt-2 text-sm text-neutral-600">다른 검색어로 다시 시도해보세요.</p>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-center gap-2 mt-12">
                <button class="p-2 rounded-lg border border-neutral-200 text-neutral-400 cursor-not-allowed">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button class="w-10 h-10 rounded-lg bg-primary-600 text-white font-medium">1</button>
                <button class="w-10 h-10 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium">2</button>
                <button class="w-10 h-10 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium">3</button>
                <button class="p-2 rounded-lg border border-neutral-200 text-neutral-700 hover:bg-neutral-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>
