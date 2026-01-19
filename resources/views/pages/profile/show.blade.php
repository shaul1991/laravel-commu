<x-layouts.app>
    <x-slot:title>{{ '@' . $username }} 프로필</x-slot:title>

    <div class="py-8" x-data="profilePage('{{ $username }}')" x-init="init()">
        <div class="max-w-4xl mx-auto">
            {{-- Loading State --}}
            <div x-show="loading" class="card p-8">
                <div class="flex items-start gap-6 animate-pulse">
                    <div class="w-24 h-24 rounded-full bg-neutral-200"></div>
                    <div class="flex-1 space-y-3">
                        <div class="h-6 w-1/3 bg-neutral-200 rounded"></div>
                        <div class="h-4 w-1/4 bg-neutral-200 rounded"></div>
                        <div class="h-16 w-full bg-neutral-200 rounded"></div>
                    </div>
                </div>
            </div>

            {{-- Profile Header --}}
            <div x-show="!loading && user" x-cloak class="card p-8 mb-6">
                <div class="flex items-start gap-6">
                    {{-- Avatar --}}
                    <template x-if="user && user.avatar">
                        <img :src="user.avatar" :alt="user.name" class="w-24 h-24 rounded-full object-cover flex-shrink-0">
                    </template>
                    <template x-if="user && !user.avatar">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
                            <span x-text="user.name ? user.name.charAt(0) : ''"></span>
                        </div>
                    </template>

                    {{-- Info --}}
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-neutral-900" x-text="user?.name"></h1>
                                <p class="text-neutral-500" x-text="'@' + user?.username"></p>
                            </div>
                            <div class="flex items-center gap-2" x-show="isAuthenticated && !isOwnProfile">
                                <button
                                    @click="toggleFollow()"
                                    :class="isFollowing ? 'btn-outline' : 'btn-primary'"
                                    :disabled="followLoading"
                                >
                                    <span x-show="!followLoading" x-text="isFollowing ? '팔로잉' : '팔로우'"></span>
                                    <span x-show="followLoading">...</span>
                                </button>
                            </div>
                            <div x-show="isOwnProfile">
                                <a href="/settings" class="btn-outline">프로필 편집</a>
                            </div>
                        </div>

                        <p class="mt-3 text-neutral-700" x-text="user?.bio || '소개가 없습니다.'"></p>

                        {{-- Meta --}}
                        <div class="flex items-center gap-4 mt-4 text-sm text-neutral-500">
                            <span x-show="user?.location" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="user?.location"></span>
                            </span>
                            <span x-show="user?.website" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <a :href="user?.website" target="_blank" class="hover:text-primary-600" x-text="user?.website?.replace(/^https?:\/\//, '')"></a>
                            </span>
                            <span x-show="user?.created_at" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-text="formatJoinDate(user?.created_at)"></span>
                            </span>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-6 mt-4">
                            <button class="hover:text-primary-600">
                                <span class="font-bold text-neutral-900" x-text="user?.follower_count || 0"></span>
                                <span class="text-neutral-500">팔로워</span>
                            </button>
                            <button class="hover:text-primary-600">
                                <span class="font-bold text-neutral-900" x-text="user?.following_count || 0"></span>
                                <span class="text-neutral-500">팔로잉</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Error State --}}
            <div x-show="!loading && error" x-cloak class="card p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="mt-4 text-neutral-500" x-text="error"></p>
                <a href="/" class="btn-primary mt-6 inline-block">홈으로 돌아가기</a>
            </div>

            {{-- Tabs --}}
            <div x-show="!loading && user" x-cloak class="border-b border-neutral-200 mb-6">
                <nav class="flex gap-8">
                    <button
                        @click="tab = 'articles'; if (articles.length === 0) fetchArticles()"
                        :class="tab === 'articles' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        아티클 <span class="text-sm">(<span x-text="user?.article_count || 0"></span>)</span>
                    </button>
                    <button
                        @click="tab = 'comments'"
                        :class="tab === 'comments' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        댓글
                    </button>
                    <button
                        @click="tab = 'likes'"
                        :class="tab === 'likes' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                        class="pb-4 border-b-2 font-medium transition-colors"
                    >
                        좋아요
                    </button>
                </nav>
            </div>

            {{-- Articles Tab --}}
            <div x-show="!loading && user && tab === 'articles'" x-cloak>
                {{-- Articles Loading --}}
                <div x-show="articlesLoading" class="space-y-4">
                    <template x-for="i in 3">
                        <div class="card p-5 animate-pulse">
                            <div class="h-5 w-2/3 bg-neutral-200 rounded mb-3"></div>
                            <div class="h-4 w-full bg-neutral-200 rounded mb-2"></div>
                            <div class="h-4 w-1/2 bg-neutral-200 rounded"></div>
                        </div>
                    </template>
                </div>

                {{-- Articles List --}}
                <div x-show="!articlesLoading" class="space-y-4">
                    <template x-if="articles.length === 0">
                        <div class="card p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-4 text-neutral-500">작성한 아티클이 없습니다.</p>
                        </div>
                    </template>

                    <template x-for="article in articles" :key="article.id">
                        <a :href="'/articles/' + article.slug" class="card block p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700" x-text="article.category"></span>
                            </div>
                            <h3 class="font-bold text-neutral-900 mb-2" x-text="article.title"></h3>
                            <p class="text-sm text-neutral-600 mb-3 line-clamp-2" x-text="article.excerpt"></p>
                            <div class="flex items-center gap-4 text-sm text-neutral-500">
                                <span x-text="formatDate(article.published_at || article.created_at)"></span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span x-text="article.view_count || 0"></span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span x-text="article.comment_count || 0"></span>
                                </span>
                            </div>
                        </a>
                    </template>

                    {{-- Load More Articles --}}
                    <button
                        x-show="articlesMeta?.current_page < articlesMeta?.last_page"
                        @click="loadMoreArticles()"
                        class="w-full py-3 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        :disabled="loadingMoreArticles"
                    >
                        <span x-show="!loadingMoreArticles">더 보기</span>
                        <span x-show="loadingMoreArticles">로딩 중...</span>
                    </button>
                </div>
            </div>

            {{-- Comments Tab (Placeholder) --}}
            <div x-show="!loading && user && tab === 'comments'" x-cloak class="card p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="mt-4 text-neutral-500">댓글 목록은 곧 추가될 예정입니다.</p>
            </div>

            {{-- Likes Tab (Placeholder) --}}
            <div x-show="!loading && user && tab === 'likes'" x-cloak class="card p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <p class="mt-4 text-neutral-500">좋아요한 글 목록은 곧 추가될 예정입니다.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function profilePage(username) {
        return {
            username: username,
            user: null,
            loading: true,
            error: null,
            tab: 'articles',
            isFollowing: false,
            followLoading: false,
            isOwnProfile: false,
            isAuthenticated: false,
            articles: [],
            articlesMeta: { current_page: 1, last_page: 1 },
            articlesLoading: false,
            loadingMoreArticles: false,

            async init() {
                this.isAuthenticated = window.auth?.isAuthenticated() ?? false;

                // Check if viewing own profile
                if (this.isAuthenticated) {
                    const currentUser = window.auth.getUser();
                    if (currentUser && currentUser.username === this.username) {
                        this.isOwnProfile = true;
                    }
                }

                await this.fetchProfile();
                if (this.user) {
                    await this.fetchArticles();
                }
            },

            async fetchProfile() {
                this.loading = true;
                this.error = null;

                try {
                    const headers = {
                        'Accept': 'application/json'
                    };

                    // Add auth header if authenticated
                    if (this.isAuthenticated && window.auth?.getAuthHeaders) {
                        Object.assign(headers, window.auth.getAuthHeaders());
                    }

                    const response = await fetch(`/api/users/${this.username}`, {
                        headers: headers
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.user = data.data;
                        this.isFollowing = data.data.is_following || false;

                        // Double-check own profile
                        if (data.data.is_own_profile) {
                            this.isOwnProfile = true;
                        }
                    } else if (response.status === 404) {
                        this.error = '사용자를 찾을 수 없습니다.';
                    } else {
                        this.error = '프로필을 불러오는데 실패했습니다.';
                    }
                } catch (error) {
                    console.error('Failed to fetch profile:', error);
                    this.error = '프로필을 불러오는데 실패했습니다.';
                } finally {
                    this.loading = false;
                }
            },

            async fetchArticles(page = 1) {
                if (page === 1) this.articlesLoading = true;
                else this.loadingMoreArticles = true;

                try {
                    const response = await fetch(`/api/users/${this.username}/articles?page=${page}&per_page=10`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (page === 1) {
                            this.articles = data.data || [];
                        } else {
                            this.articles = [...this.articles, ...(data.data || [])];
                        }
                        this.articlesMeta = data.meta || { current_page: 1, last_page: 1 };
                    }
                } catch (error) {
                    console.error('Failed to fetch articles:', error);
                } finally {
                    this.articlesLoading = false;
                    this.loadingMoreArticles = false;
                }
            },

            loadMoreArticles() {
                if (this.articlesMeta?.current_page) {
                    this.fetchArticles(this.articlesMeta.current_page + 1);
                }
            },

            async toggleFollow() {
                if (this.followLoading) return;

                if (!this.isAuthenticated) {
                    window.location.href = `/login?redirect=/@${this.username}`;
                    return;
                }

                this.followLoading = true;
                try {
                    const response = await window.auth.fetch(`/api/users/${this.username}/follow`, {
                        method: 'POST'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.isFollowing = data.data?.is_following ?? !this.isFollowing;
                        // Update follower count
                        if (this.user && data.data?.follower_count !== undefined) {
                            this.user.follower_count = data.data.follower_count;
                        } else if (this.user) {
                            // Manually adjust count
                            this.user.follower_count = this.isFollowing
                                ? (this.user.follower_count || 0) + 1
                                : Math.max((this.user.follower_count || 0) - 1, 0);
                        }
                    } else if (response.status === 401) {
                        window.location.href = `/login?redirect=/@${this.username}`;
                    }
                } catch (error) {
                    console.error('Failed to toggle follow:', error);
                } finally {
                    this.followLoading = false;
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
            },

            formatJoinDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long' }) + ' 가입';
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
