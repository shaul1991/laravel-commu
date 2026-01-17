<x-layouts.app title="Tech Blog - 개발자를 위한 기술 블로그">
    <div x-data="homePage()" x-init="init()">
        {{-- Hero Section - Featured Article --}}
        <section class="mb-8 lg:mb-12">
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                {{-- Main Featured Post --}}
                <div class="lg:row-span-2">
                    <template x-if="featuredArticle">
                        <article class="card group relative h-full overflow-hidden">
                            <a :href="'/articles/' + featuredArticle.slug" class="block h-full">
                                <div class="relative h-full min-h-[300px] lg:min-h-[400px]">
                                    {{-- Background Image --}}
                                    <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-secondary-600">
                                        <template x-if="featuredArticle.thumbnail_url">
                                            <img :src="featuredArticle.thumbnail_url" :alt="featuredArticle.title" class="absolute inset-0 h-full w-full object-cover">
                                        </template>
                                        <div class="absolute inset-0 bg-black/30"></div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="relative flex h-full flex-col justify-end p-6 lg:p-8">
                                        <div class="mb-3">
                                            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                                Featured
                                            </span>
                                        </div>
                                        <h2 class="text-2xl font-bold text-white lg:text-3xl" x-text="featuredArticle.title"></h2>
                                        <p class="mt-3 text-sm text-white/80 line-clamp-2 lg:text-base" x-text="featuredArticle.excerpt"></p>
                                        <div class="mt-4 flex items-center gap-4 text-sm text-white/70">
                                            <div class="flex items-center gap-2">
                                                <template x-if="featuredArticle.author?.avatar_url">
                                                    <img :src="featuredArticle.author.avatar_url" :alt="featuredArticle.author.name" class="h-8 w-8 rounded-full object-cover">
                                                </template>
                                                <template x-if="!featuredArticle.author?.avatar_url">
                                                    <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-medium" x-text="featuredArticle.author?.name?.charAt(0).toUpperCase() || '?'"></div>
                                                </template>
                                                <span x-text="featuredArticle.author?.name"></span>
                                            </div>
                                            <span x-text="formatDate(featuredArticle.published_at)"></span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="featuredArticle.reading_time + '분'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </template>

                    {{-- Loading State --}}
                    <template x-if="!featuredArticle && loading">
                        <div class="card h-full min-h-[300px] lg:min-h-[400px] animate-pulse bg-neutral-200"></div>
                    </template>
                </div>

                {{-- Secondary Featured Posts --}}
                <div class="space-y-4 lg:space-y-6">
                    <template x-for="article in secondaryFeatured" :key="article.id">
                        <article class="card group overflow-hidden transition-shadow hover:shadow-lg">
                            <a :href="'/articles/' + article.slug" class="flex gap-4 p-4">
                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg lg:h-28 lg:w-28"
                                     :class="getCategoryGradient(article.category)">
                                    <template x-if="article.thumbnail_url">
                                        <img :src="article.thumbnail_url" :alt="article.title" class="h-full w-full object-cover">
                                    </template>
                                </div>
                                <div class="flex flex-1 flex-col justify-center">
                                    <span class="text-xs font-semibold" :class="getCategoryTextColor(article.category)" x-text="article.category || 'General'"></span>
                                    <h3 class="mt-1 font-bold text-neutral-900 line-clamp-2 group-hover:text-primary-600" x-text="article.title"></h3>
                                    <div class="mt-2 flex items-center gap-3 text-xs text-neutral-500">
                                        <span x-text="article.author?.name"></span>
                                        <span x-text="article.reading_time + '분'"></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </template>

                    {{-- Loading State --}}
                    <template x-if="secondaryFeatured.length === 0 && loading">
                        <template x-for="i in 2" :key="i">
                            <div class="card h-32 animate-pulse bg-neutral-200"></div>
                        </template>
                    </template>
                </div>
            </div>
        </section>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            {{-- Main Content - Articles --}}
            <main class="lg:col-span-8">
                {{-- Section Header --}}
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-neutral-900">최신 아티클</h2>
                </div>

                {{-- Article Grid --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    <template x-for="article in articles" :key="article.id">
                        <article class="card group overflow-hidden transition-all duration-300 hover:shadow-lg">
                            <a :href="'/articles/' + article.slug" class="block">
                                {{-- Thumbnail --}}
                                <div class="relative aspect-video overflow-hidden bg-neutral-100">
                                    <template x-if="article.thumbnail_url">
                                        <img :src="article.thumbnail_url" :alt="article.title" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    </template>
                                    <template x-if="!article.thumbnail_url">
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-500 to-secondary-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                    </template>

                                    {{-- Category Badge --}}
                                    <template x-if="article.category">
                                        <div class="absolute left-4 top-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="getCategoryBadgeClass(article.category)" x-text="article.category"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-bold text-neutral-900 line-clamp-2 group-hover:text-primary-600 transition-colors" x-text="article.title"></h3>
                                    <p class="mt-2 text-sm text-neutral-600 line-clamp-2" x-text="article.excerpt"></p>

                                    {{-- Tags --}}
                                    <template x-if="article.tags && article.tags.length > 0">
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            <template x-for="tag in article.tags.slice(0, 3)" :key="tag.id || tag">
                                                <span class="text-xs text-neutral-500" x-text="'#' + (tag.name || tag)"></span>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Meta Info --}}
                                    <div class="mt-4 flex items-center justify-between border-t border-neutral-100 pt-4">
                                        <div class="flex items-center gap-2">
                                            <template x-if="article.author?.avatar_url">
                                                <img :src="article.author.avatar_url" :alt="article.author.name" class="h-6 w-6 rounded-full object-cover">
                                            </template>
                                            <template x-if="!article.author?.avatar_url && article.author?.name">
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-neutral-200 text-xs font-medium text-neutral-600" x-text="article.author.name.charAt(0).toUpperCase()"></div>
                                            </template>
                                            <span class="text-sm font-medium text-neutral-700" x-text="article.author?.name"></span>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-neutral-500">
                                            <span x-text="formatDate(article.published_at)"></span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="article.reading_time + '분'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </template>
                </div>

                {{-- Loading State --}}
                <template x-if="loading && articles.length === 0">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <template x-for="i in 6" :key="i">
                            <div class="card animate-pulse">
                                <div class="aspect-video bg-neutral-200"></div>
                                <div class="p-5 space-y-3">
                                    <div class="h-4 bg-neutral-200 rounded w-3/4"></div>
                                    <div class="h-3 bg-neutral-200 rounded w-full"></div>
                                    <div class="h-3 bg-neutral-200 rounded w-2/3"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Empty State --}}
                <template x-if="!loading && articles.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-900">게시글이 없습니다</h3>
                        <p class="mt-1 text-sm text-neutral-500">아직 작성된 게시글이 없습니다.</p>
                    </div>
                </template>

                {{-- Load More --}}
                <template x-if="hasMore && articles.length > 0">
                    <div class="mt-10 text-center">
                        <button
                            @click="loadMore()"
                            :disabled="loadingMore"
                            class="btn-outline text-base px-8 py-3">
                            <svg x-show="loadingMore" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loadingMore ? '로딩 중...' : '더 많은 아티클 보기'"></span>
                        </button>
                    </div>
                </template>
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
                            <template x-for="cat in categoryStats" :key="cat.name">
                                <a href="#" @click.prevent="filterByCategory(cat.name)" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-neutral-50">
                                    <span class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full" :class="getCategoryDotColor(cat.name)"></span>
                                        <span x-text="cat.name"></span>
                                    </span>
                                    <span class="text-xs text-neutral-400" x-text="cat.count"></span>
                                </a>
                            </template>
                        </div>
                    </x-ui.card>

                    {{-- Popular Tags --}}
                    <x-ui.card>
                        <x-slot:header>
                            <h3 class="text-sm font-semibold text-neutral-900">인기 태그</h3>
                        </x-slot:header>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in popularTags" :key="tag">
                                <a href="#" class="rounded-full border border-neutral-200 px-3 py-1 text-xs font-medium text-neutral-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600" x-text="tag"></a>
                            </template>
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
    </div>

    @push('scripts')
    <script>
        function homePage() {
            return {
                articles: [],
                featuredArticle: null,
                secondaryFeatured: [],
                loading: true,
                loadingMore: false,
                currentPage: 1,
                hasMore: false,
                currentCategory: null,
                categoryStats: [
                    { name: 'Backend', count: 0 },
                    { name: 'Frontend', count: 0 },
                    { name: 'DevOps', count: 0 },
                    { name: 'AI/ML', count: 0 },
                    { name: 'Database', count: 0 }
                ],
                popularTags: ['laravel', 'php', 'javascript', 'typescript', 'vue', 'react', 'docker', 'postgresql', 'redis', 'api'],

                async init() {
                    await this.fetchArticles();
                },

                async fetchArticles(append = false) {
                    if (!append) {
                        this.loading = true;
                    }

                    try {
                        let url = `/api/articles?page=${this.currentPage}&per_page=6`;
                        if (this.currentCategory) {
                            url += `&category=${encodeURIComponent(this.currentCategory)}`;
                        }

                        const response = await fetch(url);
                        const data = await response.json();

                        if (data.data) {
                            const allArticles = append ? [...this.articles, ...data.data] : data.data;

                            // Set featured articles
                            if (!append && this.currentCategory === null) {
                                this.featuredArticle = allArticles[0] || null;
                                this.secondaryFeatured = allArticles.slice(1, 3);
                                this.articles = allArticles.slice(3);
                            } else if (!append) {
                                this.featuredArticle = null;
                                this.secondaryFeatured = [];
                                this.articles = allArticles;
                            } else {
                                this.articles = allArticles.slice(this.currentCategory === null ? 3 : 0);
                            }

                            // Check if there are more pages
                            this.hasMore = data.meta?.current_page < data.meta?.last_page;
                        }
                    } catch (error) {
                        console.error('Failed to fetch articles:', error);
                    } finally {
                        this.loading = false;
                        this.loadingMore = false;
                    }
                },

                async filterByCategory(category) {
                    this.currentCategory = category;
                    this.currentPage = 1;
                    this.articles = [];
                    await this.fetchArticles();
                },

                async loadMore() {
                    this.loadingMore = true;
                    this.currentPage++;
                    await this.fetchArticles(true);
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('ko-KR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    }).replace(/\./g, '.').replace(/\s/g, '');
                },

                getCategoryGradient(category) {
                    const gradients = {
                        'Backend': 'bg-gradient-to-br from-green-500 to-emerald-600',
                        'Frontend': 'bg-gradient-to-br from-blue-500 to-indigo-600',
                        'DevOps': 'bg-gradient-to-br from-orange-500 to-amber-600',
                        'AI/ML': 'bg-gradient-to-br from-purple-500 to-violet-600',
                        'AI': 'bg-gradient-to-br from-purple-500 to-violet-600',
                        'Database': 'bg-gradient-to-br from-red-500 to-rose-600'
                    };
                    return gradients[category] || 'bg-gradient-to-br from-primary-500 to-secondary-500';
                },

                getCategoryTextColor(category) {
                    const colors = {
                        'Backend': 'text-green-600',
                        'Frontend': 'text-blue-600',
                        'DevOps': 'text-orange-600',
                        'AI/ML': 'text-purple-600',
                        'AI': 'text-purple-600',
                        'Database': 'text-red-600'
                    };
                    return colors[category] || 'text-primary-600';
                },

                getCategoryBadgeClass(category) {
                    const classes = {
                        'Backend': 'bg-green-100 text-green-800',
                        'Frontend': 'bg-blue-100 text-blue-800',
                        'DevOps': 'bg-orange-100 text-orange-800',
                        'AI/ML': 'bg-purple-100 text-purple-800',
                        'AI': 'bg-purple-100 text-purple-800',
                        'Database': 'bg-red-100 text-red-800'
                    };
                    return classes[category] || 'bg-primary-100 text-primary-800';
                },

                getCategoryDotColor(category) {
                    const colors = {
                        'Backend': 'bg-green-500',
                        'Frontend': 'bg-blue-500',
                        'DevOps': 'bg-orange-500',
                        'AI/ML': 'bg-purple-500',
                        'Database': 'bg-red-500'
                    };
                    return colors[category] || 'bg-neutral-500';
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
