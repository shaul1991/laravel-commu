<x-layouts.app>
    <x-slot:title>검색</x-slot:title>

    <div class="py-8" x-data="searchPage()" x-init="init()">
        <div class="max-w-4xl mx-auto">
            {{-- Search Box --}}
            <div class="mb-8">
                <form @submit.prevent="search()" class="relative">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.500ms="search()"
                        placeholder="검색어를 입력하세요... (2자 이상)"
                        class="w-full pl-12 pr-4 py-4 text-lg border border-neutral-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        autofocus
                    >
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-6 w-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="h-5 w-5 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </form>
            </div>

            {{-- Results Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-neutral-900">
                        <span x-show="query && query.length >= 2">"<span x-text="query"></span>" 검색 결과</span>
                        <span x-show="!query || query.length < 2">검색</span>
                    </h1>
                    <p class="text-sm text-neutral-600 mt-1" x-show="searched">
                        총 <span class="font-medium" x-text="totalResults"></span>개의 결과
                    </p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-neutral-200">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-neutral-600">유형:</span>
                    <button
                        @click="type = 'all'; search()"
                        :class="type === 'all' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        전체
                    </button>
                    <button
                        @click="type = 'articles'; search()"
                        :class="type === 'articles' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        아티클
                    </button>
                    <button
                        @click="type = 'users'; search()"
                        :class="type === 'users' ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        사용자
                    </button>
                </div>
                <div class="flex items-center gap-2 ml-auto" x-show="type === 'all' || type === 'articles'">
                    <span class="text-sm text-neutral-600">카테고리:</span>
                    <select x-model="category" @change="search()" class="input py-1.5 text-sm">
                        <option value="">전체</option>
                        <option value="기술">기술</option>
                        <option value="커리어">커리어</option>
                        <option value="일상">일상</option>
                        <option value="리뷰">리뷰</option>
                    </select>
                </div>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="space-y-4">
                <template x-for="i in 3">
                    <div class="animate-pulse card p-5">
                        <div class="h-5 w-2/3 rounded bg-neutral-200 mb-3"></div>
                        <div class="h-4 w-full rounded bg-neutral-200 mb-2"></div>
                        <div class="h-4 w-1/2 rounded bg-neutral-200"></div>
                    </div>
                </template>
            </div>

            {{-- Search Results --}}
            <div x-show="!loading && searched" class="space-y-6">
                {{-- Article Results --}}
                <div x-show="(type === 'all' || type === 'articles') && articles.length > 0">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4" x-show="type === 'all'">
                        아티클 (<span x-text="articlesMeta?.total || 0"></span>)
                    </h2>

                    <div class="space-y-4">
                        <template x-for="article in articles" :key="article.id">
                            <a :href="'/articles/' + article.slug" class="card block p-5 hover:shadow-md transition-shadow">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700" x-text="article.category"></span>
                                </div>
                                <h3 class="font-bold text-neutral-900 mb-2 hover:text-primary-600" x-text="article.title"></h3>
                                <p class="text-sm text-neutral-600 mb-3 line-clamp-2" x-text="article.excerpt"></p>
                                <div class="flex items-center gap-4 text-sm text-neutral-500">
                                    <span class="font-medium text-neutral-700" x-text="article.author?.name || '익명'"></span>
                                    <span x-text="formatDate(article.published_at || article.created_at)"></span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span x-text="article.view_count || 0"></span>
                                    </span>
                                </div>
                            </a>
                        </template>
                    </div>

                    {{-- Load More Articles --}}
                    <button
                        x-show="articlesMeta?.current_page < articlesMeta?.last_page"
                        @click="loadMoreArticles()"
                        class="w-full mt-4 py-3 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        :disabled="loadingMore"
                    >
                        <span x-show="!loadingMore">더 보기 (<span x-text="(articlesMeta?.total || 0) - articles.length"></span>개)</span>
                        <span x-show="loadingMore">로딩 중...</span>
                    </button>
                </div>

                {{-- User Results --}}
                <div x-show="(type === 'all' || type === 'users') && users.length > 0" class="mt-8" :class="{ 'mt-0': type === 'users' }">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4" x-show="type === 'all'">
                        사용자 (<span x-text="users.length"></span>)
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="user in users" :key="user.id">
                            <a :href="'/@' + user.username" class="card p-4 hover:shadow-md transition-shadow text-center">
                                <template x-if="user.avatar">
                                    <img :src="user.avatar" :alt="user.name" class="w-16 h-16 mx-auto rounded-full object-cover mb-3">
                                </template>
                                <template x-if="!user.avatar">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-white text-xl font-bold mb-3">
                                        <span x-text="user.name?.charAt(0) || '?'"></span>
                                    </div>
                                </template>
                                <h3 class="font-bold text-neutral-900" x-text="user.name"></h3>
                                <p class="text-sm text-neutral-500 mb-2" x-text="'@' + user.username"></p>
                                <p class="text-xs text-neutral-600 mb-3 line-clamp-2" x-text="user.bio || '소개가 없습니다.'"></p>
                                <div class="flex justify-center gap-4 text-xs text-neutral-500">
                                    <span>팔로워 <span class="font-medium" x-text="user.follower_count || 0"></span></span>
                                    <span>글 <span class="font-medium" x-text="user.article_count || 0"></span></span>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Empty State --}}
                <div x-show="articles.length === 0 && users.length === 0" class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-neutral-900">검색 결과가 없습니다</h3>
                    <p class="mt-2 text-sm text-neutral-600">다른 검색어로 다시 시도해보세요.</p>
                </div>
            </div>

            {{-- Initial State --}}
            <div x-show="!loading && !searched" class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900">검색어를 입력하세요</h3>
                <p class="mt-2 text-sm text-neutral-600">2자 이상 입력하시면 검색이 시작됩니다.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function searchPage() {
        return {
            query: '',
            type: 'all',
            category: '',
            articles: [],
            users: [],
            articlesMeta: { current_page: 1, last_page: 1, total: 0 },
            loading: false,
            loadingMore: false,
            searched: false,

            get totalResults() {
                if (this.type === 'articles') return this.articlesMeta?.total || 0;
                if (this.type === 'users') return this.users.length;
                return (this.articlesMeta?.total || 0) + this.users.length;
            },

            init() {
                // Get query from URL
                const params = new URLSearchParams(window.location.search);
                this.query = params.get('q') || '';
                this.type = params.get('type') || 'all';
                this.category = params.get('category') || '';

                if (this.query && this.query.length >= 2) {
                    this.search();
                }
            },

            async search() {
                if (!this.query || this.query.length < 2) {
                    this.searched = false;
                    this.articles = [];
                    this.users = [];
                    return;
                }

                this.loading = true;
                this.searched = true;

                // Update URL
                const params = new URLSearchParams();
                params.set('q', this.query);
                if (this.type !== 'all') params.set('type', this.type);
                if (this.category) params.set('category', this.category);
                window.history.replaceState({}, '', `${window.location.pathname}?${params}`);

                try {
                    const promises = [];

                    if (this.type === 'all' || this.type === 'articles') {
                        const articleParams = new URLSearchParams({
                            q: this.query,
                            per_page: '10'
                        });
                        if (this.category) articleParams.set('category', this.category);

                        promises.push(
                            fetch(`/api/search/articles?${articleParams}`)
                                .then(r => r.json())
                                .then(data => {
                                    this.articles = data.data || [];
                                    this.articlesMeta = data.meta || { current_page: 1, last_page: 1, total: 0 };
                                })
                                .catch(() => {
                                    this.articles = [];
                                    this.articlesMeta = { current_page: 1, last_page: 1, total: 0 };
                                })
                        );
                    } else {
                        this.articles = [];
                    }

                    if (this.type === 'all' || this.type === 'users') {
                        promises.push(
                            fetch(`/api/search/users?q=${encodeURIComponent(this.query)}&limit=12`)
                                .then(r => r.json())
                                .then(data => {
                                    this.users = data.data || [];
                                })
                                .catch(() => {
                                    this.users = [];
                                })
                        );
                    } else {
                        this.users = [];
                    }

                    await Promise.all(promises);
                } catch (error) {
                    console.error('Search failed:', error);
                } finally {
                    this.loading = false;
                }
            },

            async loadMoreArticles() {
                if (this.loadingMore || !this.articlesMeta || this.articlesMeta.current_page >= this.articlesMeta.last_page) return;

                this.loadingMore = true;
                try {
                    const params = new URLSearchParams({
                        q: this.query,
                        per_page: '10',
                        page: String(this.articlesMeta.current_page + 1)
                    });
                    if (this.category) params.set('category', this.category);

                    const response = await fetch(`/api/search/articles?${params}`);
                    const data = await response.json();

                    this.articles = [...this.articles, ...(data.data || [])];
                    this.articlesMeta = data.meta || this.articlesMeta;
                } catch (error) {
                    console.error('Load more failed:', error);
                } finally {
                    this.loadingMore = false;
                }
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;
                const days = Math.floor(diff / 86400000);

                if (days < 1) return '오늘';
                if (days < 7) return `${days}일 전`;
                if (days < 30) return `${Math.floor(days / 7)}주 전`;

                return date.toLocaleDateString('ko-KR', { year: 'numeric', month: 'short', day: 'numeric' });
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
