<x-layouts.app>
    <x-slot:title>아티클</x-slot:title>

    <div class="py-8" x-data="articlesPage()" x-init="init()">
        <div class="max-w-5xl mx-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">아티클</h1>
                    <p class="mt-1 text-sm text-neutral-600">개발자들의 인사이트와 경험을 공유합니다</p>
                </div>
                <a href="{{ route('articles.create') }}" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    글 작성
                </a>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                {{-- Categories --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    <button
                        @click="filterByCategory(null)"
                        :class="currentCategory === null ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0"
                    >
                        전체
                    </button>
                    <template x-for="cat in categories" :key="cat">
                        <button
                            @click="filterByCategory(cat)"
                            :class="currentCategory === cat ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0"
                            x-text="cat"
                        >
                        </button>
                    </template>
                </div>

                {{-- Sort & View --}}
                <div class="flex items-center gap-3">
                    <select x-model="sortBy" @change="fetchArticles()" class="input py-2 text-sm">
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

            {{-- Loading State --}}
            <template x-if="loading">
                <div class="space-y-4">
                    <template x-for="i in 5" :key="i">
                        <div class="card p-6 animate-pulse">
                            <div class="flex gap-6">
                                <div class="flex-1 space-y-3">
                                    <div class="h-4 bg-neutral-200 rounded w-1/4"></div>
                                    <div class="h-6 bg-neutral-200 rounded w-3/4"></div>
                                    <div class="h-4 bg-neutral-200 rounded w-full"></div>
                                    <div class="h-4 bg-neutral-200 rounded w-1/2"></div>
                                </div>
                                <div class="w-40 h-28 bg-neutral-200 rounded-lg flex-shrink-0"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- List View --}}
            <div x-show="!loading && view === 'list'" class="space-y-4">
                <template x-for="article in articles" :key="article.id">
                    <a :href="'/articles/' + article.slug" class="card block p-6 hover:shadow-md transition-shadow">
                        <div class="flex gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <template x-for="tag in (article.tags || []).slice(0, 3)" :key="tag.id || tag">
                                        <span class="text-xs text-primary-600 font-medium" x-text="'#' + (tag.name || tag)"></span>
                                    </template>
                                </div>
                                <h2 class="text-lg font-bold text-neutral-900 mb-2 group-hover:text-primary-600" x-text="article.title"></h2>
                                <p class="text-neutral-600 text-sm mb-4 line-clamp-2" x-text="article.excerpt"></p>
                                <div class="flex items-center gap-4 text-sm text-neutral-500">
                                    <span class="font-medium text-neutral-700" x-text="article.author?.name"></span>
                                    <span x-text="formatRelativeTime(article.published_at)"></span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span x-text="formatNumber(article.view_count || 0)"></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <span x-text="article.comments_count || 0"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="w-40 h-28 bg-neutral-100 rounded-lg flex-shrink-0 overflow-hidden">
                                <template x-if="article.thumbnail_url">
                                    <img :src="article.thumbnail_url" :alt="article.title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!article.thumbnail_url">
                                    <div class="w-full h-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center">
                                        <svg class="h-8 w-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Grid View --}}
            <div x-show="!loading && view === 'grid'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <template x-for="article in articles" :key="article.id">
                    <a :href="'/articles/' + article.slug" class="card overflow-hidden hover:shadow-md transition-shadow">
                        <div class="h-40 bg-neutral-100 overflow-hidden">
                            <template x-if="article.thumbnail_url">
                                <img :src="article.thumbnail_url" :alt="article.title" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!article.thumbnail_url">
                                <div class="w-full h-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <template x-for="tag in (article.tags || []).slice(0, 2)" :key="tag.id || tag">
                                    <span class="text-xs text-primary-600 font-medium" x-text="'#' + (tag.name || tag)"></span>
                                </template>
                            </div>
                            <h2 class="font-bold text-neutral-900 mb-2 line-clamp-2" x-text="article.title"></h2>
                            <p class="text-neutral-600 text-sm mb-3 line-clamp-2" x-text="article.excerpt"></p>
                            <div class="flex items-center justify-between text-sm text-neutral-500">
                                <span class="font-medium text-neutral-700" x-text="article.author?.name"></span>
                                <span x-text="formatRelativeTime(article.published_at)"></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Empty State --}}
            <template x-if="!loading && articles.length === 0">
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-neutral-900">게시글이 없습니다</h3>
                    <p class="mt-2 text-neutral-500">아직 작성된 게시글이 없습니다. 첫 번째 글을 작성해보세요!</p>
                    <a href="{{ route('articles.create') }}" class="btn-primary mt-6 inline-flex">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        글 작성하기
                    </a>
                </div>
            </template>

            {{-- Pagination --}}
            <template x-if="!loading && meta.last_page > 1">
                <div class="flex items-center justify-center gap-2 mt-8">
                    <button
                        @click="goToPage(meta.current_page - 1)"
                        :disabled="meta.current_page === 1"
                        :class="meta.current_page === 1 ? 'text-neutral-400 cursor-not-allowed' : 'text-neutral-700 hover:bg-neutral-50'"
                        class="p-2 rounded-lg border border-neutral-200"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <template x-for="page in visiblePages" :key="page">
                        <template x-if="page === '...'">
                            <span class="px-2 text-neutral-400">...</span>
                        </template>
                        <template x-if="page !== '...'">
                            <button
                                @click="goToPage(page)"
                                :class="page === meta.current_page ? 'bg-primary-600 text-white' : 'border border-neutral-200 text-neutral-700 hover:bg-neutral-50'"
                                class="w-10 h-10 rounded-lg font-medium"
                                x-text="page"
                            >
                            </button>
                        </template>
                    </template>

                    <button
                        @click="goToPage(meta.current_page + 1)"
                        :disabled="meta.current_page === meta.last_page"
                        :class="meta.current_page === meta.last_page ? 'text-neutral-400 cursor-not-allowed' : 'text-neutral-700 hover:bg-neutral-50'"
                        class="p-2 rounded-lg border border-neutral-200"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
    <script>
        function articlesPage() {
            return {
                articles: [],
                loading: true,
                view: 'list',
                sortBy: 'latest',
                currentCategory: null,
                categories: ['기술', '커리어', '일상'],
                meta: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0
                },

                async init() {
                    await this.fetchArticles();
                },

                async fetchArticles() {
                    this.loading = true;

                    try {
                        let url = `/api/articles?page=${this.meta.current_page}&per_page=${this.meta.per_page}`;

                        if (this.currentCategory) {
                            url += `&category=${encodeURIComponent(this.currentCategory)}`;
                        }

                        if (this.sortBy === 'popular') {
                            url += '&sort=popular';
                        } else if (this.sortBy === 'comments') {
                            url += '&sort=comments';
                        }

                        const response = await fetch(url);
                        const data = await response.json();

                        if (data.data) {
                            this.articles = data.data;
                            this.meta = {
                                current_page: data.meta?.current_page || 1,
                                last_page: data.meta?.last_page || 1,
                                per_page: data.meta?.per_page || 10,
                                total: data.meta?.total || 0
                            };
                        }
                    } catch (error) {
                        console.error('Failed to fetch articles:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async filterByCategory(category) {
                    this.currentCategory = category;
                    this.meta.current_page = 1;
                    await this.fetchArticles();
                },

                async goToPage(page) {
                    if (page < 1 || page > this.meta.last_page) return;
                    this.meta.current_page = page;
                    await this.fetchArticles();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                get visiblePages() {
                    const pages = [];
                    const current = this.meta.current_page;
                    const last = this.meta.last_page;

                    if (last <= 7) {
                        for (let i = 1; i <= last; i++) {
                            pages.push(i);
                        }
                    } else {
                        pages.push(1);

                        if (current > 3) {
                            pages.push('...');
                        }

                        const start = Math.max(2, current - 1);
                        const end = Math.min(last - 1, current + 1);

                        for (let i = start; i <= end; i++) {
                            pages.push(i);
                        }

                        if (current < last - 2) {
                            pages.push('...');
                        }

                        pages.push(last);
                    }

                    return pages;
                },

                formatRelativeTime(dateString) {
                    if (!dateString) return '';

                    const date = new Date(dateString);
                    const now = new Date();
                    const diff = now - date;
                    const seconds = Math.floor(diff / 1000);
                    const minutes = Math.floor(seconds / 60);
                    const hours = Math.floor(minutes / 60);
                    const days = Math.floor(hours / 24);

                    if (days > 7) {
                        return date.toLocaleDateString('ko-KR', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    } else if (days > 0) {
                        return `${days}일 전`;
                    } else if (hours > 0) {
                        return `${hours}시간 전`;
                    } else if (minutes > 0) {
                        return `${minutes}분 전`;
                    } else {
                        return '방금 전';
                    }
                },

                formatNumber(num) {
                    if (num >= 1000) {
                        return (num / 1000).toFixed(1) + 'K';
                    }
                    return num.toString();
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
