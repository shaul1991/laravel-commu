<x-layouts.app>
    <x-slot:title>내 글 관리</x-slot:title>

    <div class="py-8" x-data="{
        filter: 'all',
        selectedItems: [],
        articles: [
            { id: 1, title: 'Laravel 12에서 새롭게 바뀐 기능들', status: 'published', views: 1234, comments: 23, date: '2024-01-15', slug: 'laravel-12-new-features' },
            { id: 2, title: 'React 19 RC 살펴보기', status: 'published', views: 892, comments: 15, date: '2024-01-12', slug: 'react-19-rc-overview' },
            { id: 3, title: 'TypeScript 5.3 타입 시스템 개선사항', status: 'draft', views: 0, comments: 0, date: '2024-01-10', slug: 'typescript-5-3-improvements' },
            { id: 4, title: 'Docker 컨테이너 최적화 팁', status: 'published', views: 567, comments: 8, date: '2024-01-08', slug: 'docker-optimization-tips' },
            { id: 5, title: '개발자의 번아웃 극복기', status: 'draft', views: 0, comments: 0, date: '2024-01-05', slug: 'developer-burnout' },
        ],
        get filteredArticles() {
            if (this.filter === 'all') return this.articles;
            return this.articles.filter(a => a.status === this.filter);
        },
        toggleAll() {
            if (this.selectedItems.length === this.filteredArticles.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.filteredArticles.map(a => a.id);
            }
        },
        formatNumber(num) {
            return num.toLocaleString();
        }
    }">
        <div class="max-w-5xl mx-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">내 글 관리</h1>
                    <p class="mt-1 text-sm text-neutral-600">작성한 글을 관리하고 통계를 확인하세요</p>
                </div>
                <a href="/write" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    새 글 작성
                </a>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-4 gap-4 mb-8">
                <div class="card p-4">
                    <div class="text-sm text-neutral-500 mb-1">전체 글</div>
                    <div class="text-2xl font-bold text-neutral-900">5</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-neutral-500 mb-1">발행됨</div>
                    <div class="text-2xl font-bold text-green-600">3</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-neutral-500 mb-1">임시저장</div>
                    <div class="text-2xl font-bold text-yellow-600">2</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-neutral-500 mb-1">총 조회수</div>
                    <div class="text-2xl font-bold text-primary-600">2,693</div>
                </div>
            </div>

            {{-- Filters & Actions --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <button
                        @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        전체 <span x-text="'(' + articles.length + ')'"></span>
                    </button>
                    <button
                        @click="filter = 'published'"
                        :class="filter === 'published' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        발행됨 <span x-text="'(' + articles.filter(a => a.status === 'published').length + ')'"></span>
                    </button>
                    <button
                        @click="filter = 'draft'"
                        :class="filter === 'draft' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        임시저장 <span x-text="'(' + articles.filter(a => a.status === 'draft').length + ')'"></span>
                    </button>
                </div>
                <div class="flex items-center gap-2" x-show="selectedItems.length > 0">
                    <span class="text-sm text-neutral-600" x-text="selectedItems.length + '개 선택됨'"></span>
                    <button class="btn-outline text-sm py-1.5">삭제</button>
                </div>
            </div>

            {{-- Articles Table --}}
            <div class="card overflow-hidden">
                <table class="w-full">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="w-12 px-4 py-3">
                                <input
                                    type="checkbox"
                                    @change="toggleAll()"
                                    :checked="selectedItems.length === filteredArticles.length && filteredArticles.length > 0"
                                    class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                                >
                            </th>
                            <th class="text-left px-4 py-3 text-sm font-medium text-neutral-700">제목</th>
                            <th class="text-center px-4 py-3 text-sm font-medium text-neutral-700 w-24">상태</th>
                            <th class="text-right px-4 py-3 text-sm font-medium text-neutral-700 w-24">조회수</th>
                            <th class="text-right px-4 py-3 text-sm font-medium text-neutral-700 w-24">댓글</th>
                            <th class="text-right px-4 py-3 text-sm font-medium text-neutral-700 w-32">작성일</th>
                            <th class="w-20 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        <template x-for="article in filteredArticles" :key="article.id">
                            <tr class="hover:bg-neutral-50">
                                <td class="px-4 py-4">
                                    <input
                                        type="checkbox"
                                        :value="article.id"
                                        x-model="selectedItems"
                                        class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                                    >
                                </td>
                                <td class="px-4 py-4">
                                    <a :href="'/articles/' + article.slug" class="font-medium text-neutral-900 hover:text-primary-600" x-text="article.title"></a>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        x-text="article.status === 'published' ? '발행됨' : '임시저장'"
                                        :class="article.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                        class="inline-flex px-2 py-1 rounded-full text-xs font-medium"
                                    ></span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm text-neutral-600" x-text="formatNumber(article.views)"></td>
                                <td class="px-4 py-4 text-right text-sm text-neutral-600" x-text="article.comments"></td>
                                <td class="px-4 py-4 text-right text-sm text-neutral-600" x-text="article.date"></td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'/articles/' + article.slug + '/edit'" class="p-1.5 text-neutral-400 hover:text-neutral-600 rounded hover:bg-neutral-100" title="수정">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button class="p-1.5 text-neutral-400 hover:text-red-600 rounded hover:bg-neutral-100" title="삭제">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                {{-- Empty State --}}
                <div x-show="filteredArticles.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-neutral-900">글이 없습니다</h3>
                    <p class="mt-2 text-sm text-neutral-600">새로운 글을 작성해보세요.</p>
                    <a href="/write" class="btn-primary mt-4">새 글 작성</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
