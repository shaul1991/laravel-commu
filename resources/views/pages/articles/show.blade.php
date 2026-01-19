{{--
    Article Detail Page - 게시글 상세 페이지
    API 연동 버전
--}}

<x-layouts.app>
    <div x-data="articleDetail()" x-init="init()">
        {{-- Loading State --}}
        <template x-if="loading">
            <div class="py-12">
                <div class="container-main">
                    <div class="mx-auto max-w-4xl">
                        <div class="animate-pulse space-y-4">
                            <div class="h-8 bg-neutral-200 rounded w-1/4"></div>
                            <div class="h-12 bg-neutral-200 rounded w-3/4"></div>
                            <div class="h-6 bg-neutral-200 rounded w-1/2"></div>
                            <div class="aspect-video bg-neutral-200 rounded-2xl mt-8"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Error State --}}
        <template x-if="!loading && error">
            <div class="py-16 text-center">
                <div class="container-main">
                    <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h2 class="mt-4 text-xl font-bold text-neutral-900">게시글을 찾을 수 없습니다</h2>
                    <p class="mt-2 text-neutral-500" x-text="error"></p>
                    <a href="{{ route('articles.index') }}" class="btn-primary mt-6 inline-flex">게시글 목록으로</a>
                </div>
            </div>
        </template>

        {{-- Article Content --}}
        <template x-if="!loading && !error && article">
            <div>
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
                                <a href="{{ route('articles.index') }}" class="hover:text-primary-600">아티클</a>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="text-neutral-700">현재 글</span>
                            </nav>

                            {{-- Category Badge --}}
                            <template x-if="article.category">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="getCategoryClass(article.category)" x-text="article.category"></span>
                            </template>

                            {{-- Title --}}
                            <h1 class="mt-4 text-3xl font-bold tracking-tight text-neutral-900 lg:text-4xl" x-text="article.title"></h1>

                            {{-- Meta --}}
                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-500">
                                <span x-text="formatDate(article.published_at)"></span>
                                <span class="hidden lg:inline text-neutral-300">|</span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="article.reading_time + '분'"></span>
                                </span>
                                <span class="hidden lg:inline text-neutral-300">|</span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span x-text="formatNumber(article.view_count || 0)"></span>
                                </span>
                            </div>

                            {{-- Author --}}
                            <div class="mt-6 flex items-center gap-4">
                                <template x-if="article.author?.avatar_url">
                                    <img :src="article.author.avatar_url" :alt="article.author.name" class="h-12 w-12 rounded-full object-cover">
                                </template>
                                <template x-if="!article.author?.avatar_url">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-lg font-bold text-white" x-text="article.author?.name?.charAt(0).toUpperCase() || '?'"></div>
                                </template>
                                <div>
                                    <a :href="'/@' + article.author?.username" class="font-semibold text-neutral-900 hover:text-primary-600" x-text="article.author?.name"></a>
                                    <p class="text-sm text-neutral-500" x-text="'@' + article.author?.username"></p>
                                </div>
                            </div>

                            {{-- Thumbnail --}}
                            <div class="mt-8 aspect-video overflow-hidden rounded-2xl bg-neutral-100">
                                <template x-if="article.thumbnail_url">
                                    <img :src="article.thumbnail_url" :alt="article.title" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!article.thumbnail_url">
                                    <div class="flex h-full w-full items-center justify-center" :class="getCategoryGradient(article.category)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Main Content --}}
                <div class="container-main py-8 lg:py-12">
                    {{-- Content --}}
                    <div class="max-w-4xl mx-auto">
                        {{-- Article Body --}}
                        <article>
                            <div class="prose-article" x-html="article.content_html"></div>

                            {{-- Tags --}}
                            <template x-if="article.tags && article.tags.length > 0">
                                <div class="mt-12 flex flex-wrap gap-2 border-t border-neutral-200 pt-8">
                                    <template x-for="tag in article.tags" :key="tag.id || tag">
                                        <a href="#" class="rounded-full bg-neutral-100 px-3 py-1.5 text-sm text-neutral-600 transition-colors hover:bg-primary-100 hover:text-primary-700" x-text="'#' + (tag.name || tag)"></a>
                                    </template>
                                </div>
                            </template>

                            {{-- Article Actions --}}
                            <div class="mt-8 flex items-center justify-between border-t border-neutral-200 pt-8">
                                <div class="flex items-center gap-4">
                                    {{-- Like Button --}}
                                    <button @click="toggleLike()" class="flex items-center gap-2 rounded-lg px-4 py-2 transition-colors" :class="article.is_liked ? 'bg-red-50 text-red-600' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200'">
                                        <svg class="h-5 w-5" :fill="article.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span x-text="article.like_count || 0"></span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- Author Actions (Edit/Delete) --}}
                                    <template x-if="article.is_author">
                                        <div class="flex items-center gap-2 mr-4">
                                            <a :href="'/articles/' + article.slug + '/edit'" class="flex items-center gap-1.5 rounded-lg bg-primary-50 px-3 py-2 text-sm font-medium text-primary-600 transition-colors hover:bg-primary-100" title="수정">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>수정</span>
                                            </a>
                                            <button @click="showDeleteModal = true" class="flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-100" title="삭제">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>삭제</span>
                                            </button>
                                        </div>
                                    </template>

                                    {{-- Share Buttons --}}
                                    <button @click="copyLink()" class="rounded-lg bg-neutral-100 p-2 text-neutral-600 transition-colors hover:bg-neutral-200" title="링크 복사">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </button>
                                    <a :href="'https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(article.title)" target="_blank" class="rounded-lg bg-neutral-100 p-2 text-neutral-600 transition-colors hover:bg-neutral-200" title="트위터 공유">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Delete Confirmation Modal --}}
                            <template x-if="article.is_author">
                                <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteModal = false">
                                    <div class="mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.stop>
                                        <h3 class="text-lg font-bold text-neutral-900">아티클 삭제</h3>
                                        <p class="mt-2 text-neutral-600">정말로 이 아티클을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.</p>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <button @click="showDeleteModal = false" class="rounded-lg bg-neutral-100 px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:bg-neutral-200">
                                                취소
                                            </button>
                                            <button @click="deleteArticle()" :disabled="deleting" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50">
                                                <span x-show="!deleting">삭제</span>
                                                <span x-show="deleting">삭제 중...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Author Box --}}
                            <div class="mt-8 rounded-2xl border border-neutral-200 bg-white p-6">
                                <div class="flex items-start gap-4">
                                    <template x-if="article.author?.avatar_url">
                                        <img :src="article.author.avatar_url" :alt="article.author.name" class="h-16 w-16 rounded-full object-cover">
                                    </template>
                                    <template x-if="!article.author?.avatar_url">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-2xl font-bold text-white" x-text="article.author?.name?.charAt(0).toUpperCase() || '?'"></div>
                                    </template>
                                    <div class="flex-1">
                                        <a :href="'/@' + article.author?.username" class="text-lg font-bold text-neutral-900 hover:text-primary-600" x-text="article.author?.name"></a>
                                        <p class="text-sm text-neutral-500" x-text="'@' + article.author?.username"></p>
                                        <p class="mt-2 text-sm text-neutral-600" x-text="article.author?.bio || '아직 자기소개가 없습니다.'"></p>
                                        <a :href="'/@' + article.author?.username" class="mt-3 inline-block text-sm font-medium text-primary-600 hover:text-primary-700">프로필 보기 →</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Comment Section --}}
                            <x-ui.comment-section :articleSlug="$slug" />
                        </article>

                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function articleDetail() {
            return {
                article: null,
                loading: true,
                error: null,
                slug: '{{ $slug }}',
                showDeleteModal: false,
                deleting: false,

                async init() {
                    await this.fetchArticle();
                },

                async fetchArticle() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const headers = {
                            'Accept': 'application/json',
                            ...window.auth?.getAuthHeaders()
                        };
                        const response = await fetch(`/api/articles/${this.slug}`, { headers });

                        if (!response.ok) {
                            if (response.status === 404) {
                                this.error = '게시글이 존재하지 않거나 삭제되었습니다.';
                            } else {
                                this.error = '게시글을 불러오는데 실패했습니다.';
                            }
                            return;
                        }

                        const data = await response.json();
                        this.article = data.data;

                        // Update page title
                        document.title = this.article.title + ' - Community';
                    } catch (error) {
                        console.error('Failed to fetch article:', error);
                        this.error = '네트워크 오류가 발생했습니다.';
                    } finally {
                        this.loading = false;
                    }
                },

                async toggleLike() {
                    if (!window.auth?.isAuthenticated()) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }

                    try {
                        const response = await window.auth.fetch(`/api/articles/${this.slug}/like`, {
                            method: 'POST'
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.article.is_liked = data.data.is_liked;
                            this.article.like_count = data.data.like_count;
                        }
                    } catch (error) {
                        console.error('Failed to toggle like:', error);
                    }
                },

                copyLink() {
                    navigator.clipboard.writeText(window.location.href).then(() => {
                        alert('링크가 클립보드에 복사되었습니다.');
                    });
                },

                async deleteArticle() {
                    if (!window.auth?.isAuthenticated()) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }

                    this.deleting = true;

                    try {
                        const response = await window.auth.fetch(`/api/articles/${this.slug}`, {
                            method: 'DELETE'
                        });

                        if (response.ok) {
                            window.location.href = '/articles';
                        } else {
                            const data = await response.json();
                            alert(data.message || '아티클 삭제에 실패했습니다.');
                        }
                    } catch (error) {
                        console.error('Failed to delete article:', error);
                        alert('네트워크 오류가 발생했습니다.');
                    } finally {
                        this.deleting = false;
                        this.showDeleteModal = false;
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('ko-KR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                },

                formatNumber(num) {
                    if (num >= 1000) {
                        return (num / 1000).toFixed(1) + 'K';
                    }
                    return num.toString();
                },

                getCategoryClass(category) {
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
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
