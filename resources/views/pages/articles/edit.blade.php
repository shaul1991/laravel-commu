<x-layouts.app>
    <x-slot:title>글 수정</x-slot:title>

    <div class="py-8" x-data="articleEdit()" x-init="init()">
        {{-- Loading State --}}
        <template x-if="loading">
            <div class="max-w-4xl mx-auto">
                <div class="card p-8">
                    <div class="animate-pulse space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="h-8 bg-neutral-200 rounded w-32"></div>
                            <div class="flex gap-3">
                                <div class="h-10 bg-neutral-200 rounded w-24"></div>
                                <div class="h-10 bg-neutral-200 rounded w-24"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-neutral-200 rounded w-48"></div>
                        <div class="h-12 bg-neutral-200 rounded w-full"></div>
                        <div class="h-64 bg-neutral-200 rounded w-full"></div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Error State --}}
        <template x-if="!loading && loadError">
            <div class="max-w-4xl mx-auto">
                <div class="card p-8 text-center">
                    <svg class="h-16 w-16 mx-auto text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h2 class="text-xl font-bold text-neutral-900 mb-2">오류 발생</h2>
                    <p class="text-neutral-600 mb-6" x-text="loadError"></p>
                    <a href="/" class="btn-primary">홈으로 돌아가기</a>
                </div>
            </div>
        </template>

        {{-- Authentication Required --}}
        <template x-if="!loading && !loadError && !isAuthenticated">
            <div class="max-w-4xl mx-auto">
                <div class="card p-8 text-center">
                    <svg class="h-16 w-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="text-xl font-bold text-neutral-900 mb-2">로그인이 필요합니다</h2>
                    <p class="text-neutral-600 mb-6">글을 수정하려면 로그인이 필요합니다.</p>
                    <a :href="'/login?redirect=/articles/' + slug + '/edit'" class="btn-primary">로그인하기</a>
                </div>
            </div>
        </template>

        {{-- Editor --}}
        <template x-if="!loading && !loadError && isAuthenticated && article">
            <div class="max-w-4xl mx-auto">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <a :href="'/articles/' + slug" class="text-neutral-500 hover:text-neutral-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                        <h1 class="text-xl font-bold text-neutral-900">글 수정</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="btn-outline" @click="showPreview = !showPreview">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span x-text="showPreview ? '편집' : '미리보기'"></span>
                        </button>
                        <button
                            type="button"
                            class="btn-outline text-red-600 border-red-200 hover:bg-red-50"
                            :disabled="deleting"
                            @click="confirmDelete()"
                        >
                            <span x-show="!deleting">삭제</span>
                            <span x-show="deleting">삭제 중...</span>
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            :disabled="saving || !canSave"
                            @click="saveArticle()"
                        >
                            <span x-show="!saving">저장하기</span>
                            <span x-show="saving">저장 중...</span>
                        </button>
                    </div>
                </div>

                {{-- Error Message --}}
                <div x-show="error" x-cloak class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <span x-text="error"></span>
                </div>

                {{-- Success Message --}}
                <div x-show="success" x-cloak class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    <span x-text="success"></span>
                </div>

                {{-- Delete Confirmation Modal --}}
                <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showDeleteModal = false">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.outside="showDeleteModal = false">
                        <h3 class="text-lg font-bold text-neutral-900 mb-2">글 삭제</h3>
                        <p class="text-neutral-600 mb-6">정말로 이 글을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.</p>
                        <div class="flex justify-end gap-3">
                            <button type="button" class="btn-outline" @click="showDeleteModal = false">취소</button>
                            <button
                                type="button"
                                class="btn-primary bg-red-600 hover:bg-red-700"
                                :disabled="deleting"
                                @click="deleteArticle()"
                            >
                                <span x-show="!deleting">삭제하기</span>
                                <span x-show="deleting">삭제 중...</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Editor --}}
                <div class="card" x-show="!showPreview">
                    <div class="p-6 space-y-6">
                        {{-- Category --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-neutral-700 mb-1.5">카테고리 <span class="text-red-500">*</span></label>
                            <select id="category" x-model="category" class="input max-w-xs">
                                <option value="">카테고리 선택</option>
                                <option value="기술">기술</option>
                                <option value="커리어">커리어</option>
                                <option value="일상">일상</option>
                                <option value="리뷰">리뷰</option>
                            </select>
                        </div>

                        {{-- Title --}}
                        <div>
                            <input
                                type="text"
                                x-model="title"
                                placeholder="제목을 입력하세요 *"
                                class="w-full text-3xl font-bold border-0 border-b border-neutral-200 pb-4 focus:border-primary-500 focus:ring-0 placeholder-neutral-400"
                            >
                        </div>

                        {{-- Tags --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1.5">태그 (최대 5개)</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="tag in tags" :key="tag">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(tag)" class="hover:text-primary-900">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2" x-show="tags.length < 5">
                                <input
                                    type="text"
                                    x-model="tagInput"
                                    @keydown.enter.prevent="addTag()"
                                    placeholder="태그 입력 후 Enter"
                                    class="input flex-1 max-w-xs"
                                >
                                <button type="button" @click="addTag()" class="btn-outline">추가</button>
                            </div>
                        </div>

                        {{-- Content Editor --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1.5">본문 <span class="text-red-500">*</span></label>
                            {{-- Toolbar --}}
                            <div class="flex items-center gap-1 p-2 border border-b-0 border-neutral-200 rounded-t-lg bg-neutral-50">
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="굵게" @click="insertMarkdown('**', '**')">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h8a4 4 0 000-8H6v8zm0 0h9a4 4 0 010 8H6v-8z" />
                                    </svg>
                                </button>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="기울임" @click="insertMarkdown('*', '*')">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </button>
                                <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="제목" @click="insertMarkdown('## ', '')">
                                    <span class="text-sm font-bold">H</span>
                                </button>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="인용" @click="insertMarkdown('> ', '')">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                    </svg>
                                </button>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="코드" @click="insertMarkdown('`', '`')">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </button>
                                <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="목록" @click="insertMarkdown('- ', '')">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="번호 목록" @click="insertMarkdown('1. ', '')">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </button>
                                <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                                <button type="button" class="p-2 rounded hover:bg-neutral-200" title="링크" @click="insertLink()">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </button>
                                <label class="p-2 rounded hover:bg-neutral-200 cursor-pointer" title="이미지">
                                    <input type="file" class="hidden" accept="image/*" @change="uploadImage($event)">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </label>
                                <span x-show="uploading" class="text-sm text-neutral-500 ml-2">업로드 중...</span>
                            </div>
                            {{-- Textarea --}}
                            <textarea
                                x-ref="contentEditor"
                                x-model="content"
                                rows="20"
                                placeholder="마크다운으로 작성해보세요..."
                                class="w-full border border-neutral-200 rounded-b-lg p-4 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none font-mono text-sm"
                            ></textarea>
                        </div>
                    </div>
                </div>

                {{-- Preview --}}
                <div class="card" x-show="showPreview" x-cloak>
                    <div class="p-6">
                        <div class="prose prose-neutral max-w-none">
                            <h1 x-text="title || '제목 없음'" class="text-3xl font-bold mb-4"></h1>
                            <div class="flex flex-wrap gap-2 mb-6" x-show="tags.length > 0">
                                <template x-for="tag in tags" :key="tag">
                                    <span class="text-sm text-primary-600" x-text="'#' + tag"></span>
                                </template>
                            </div>
                            <div class="whitespace-pre-wrap" x-text="content || '내용을 입력하세요...'"></div>
                        </div>
                    </div>
                </div>

                {{-- Last Modified Info --}}
                <div class="mt-4 text-sm text-neutral-500 text-right" x-show="article?.updated_at">
                    마지막 수정: <span x-text="formatDate(article?.updated_at)"></span>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function articleEdit() {
            return {
                slug: '{{ $slug }}',
                article: null,
                title: '',
                content: '',
                category: '',
                tags: [],
                tagInput: '',
                showPreview: false,
                showDeleteModal: false,
                isAuthenticated: false,
                loading: true,
                loadError: null,
                saving: false,
                deleting: false,
                uploading: false,
                error: null,
                success: null,

                async init() {
                    this.isAuthenticated = window.auth?.isAuthenticated() ?? false;

                    if (!this.isAuthenticated) {
                        this.loading = false;
                        return;
                    }

                    await this.fetchArticle();
                },

                async fetchArticle() {
                    try {
                        const response = await fetch(`/api/articles/${this.slug}`, {
                            headers: {
                                ...window.auth.getAuthHeaders()
                            }
                        });

                        if (!response.ok) {
                            if (response.status === 404) {
                                this.loadError = '게시글을 찾을 수 없습니다.';
                            } else if (response.status === 403) {
                                this.loadError = '이 글을 수정할 권한이 없습니다.';
                            } else {
                                this.loadError = '게시글을 불러오는데 실패했습니다.';
                            }
                            return;
                        }

                        const data = await response.json();
                        this.article = data.data;

                        // Check if current user is the author
                        const currentUser = window.auth.getUser();
                        if (currentUser && this.article.author?.id !== currentUser.id) {
                            this.loadError = '이 글을 수정할 권한이 없습니다.';
                            return;
                        }

                        // Populate form fields
                        this.title = this.article.title || '';
                        this.content = this.article.content || '';
                        this.category = this.article.category || '';
                        this.tags = this.article.tags || [];
                    } catch (err) {
                        this.loadError = '게시글을 불러오는데 실패했습니다.';
                    } finally {
                        this.loading = false;
                    }
                },

                get canSave() {
                    return this.title.trim() && this.content.trim() && this.category;
                },

                addTag() {
                    if (this.tagInput.trim() && this.tags.length < 5 && !this.tags.includes(this.tagInput.trim())) {
                        this.tags.push(this.tagInput.trim());
                        this.tagInput = '';
                    }
                },

                removeTag(tag) {
                    this.tags = this.tags.filter(t => t !== tag);
                },

                insertMarkdown(before, after) {
                    const textarea = this.$refs.contentEditor;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = this.content;
                    const selectedText = text.substring(start, end);

                    this.content = text.substring(0, start) + before + selectedText + after + text.substring(end);

                    this.$nextTick(() => {
                        textarea.focus();
                        textarea.setSelectionRange(start + before.length, end + before.length);
                    });
                },

                insertLink() {
                    const url = prompt('링크 URL을 입력하세요:');
                    if (url) {
                        this.insertMarkdown('[', `](${url})`);
                    }
                },

                async uploadImage(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.uploading = true;
                    this.error = null;

                    try {
                        const formData = new FormData();
                        formData.append('image', file);

                        const response = await fetch('/api/images/upload', {
                            method: 'POST',
                            headers: {
                                ...window.auth.getAuthHeaders()
                            },
                            body: formData
                        });

                        if (!response.ok) {
                            throw new Error('이미지 업로드에 실패했습니다.');
                        }

                        const data = await response.json();
                        const imageUrl = data.data?.url || data.url;

                        const imageMarkdown = `![${file.name}](${imageUrl})`;
                        const textarea = this.$refs.contentEditor;
                        const start = textarea.selectionStart;

                        this.content = this.content.substring(0, start) + imageMarkdown + this.content.substring(start);
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.uploading = false;
                        event.target.value = '';
                    }
                },

                async saveArticle() {
                    if (!this.canSave) {
                        this.error = '제목, 카테고리, 본문을 모두 입력해주세요.';
                        return;
                    }

                    this.saving = true;
                    this.error = null;
                    this.success = null;

                    try {
                        const response = await fetch(`/api/articles/${this.slug}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                ...window.auth.getAuthHeaders()
                            },
                            body: JSON.stringify({
                                title: this.title,
                                content: this.content,
                                category: this.category,
                                tags: this.tags
                            })
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || '저장에 실패했습니다.');
                        }

                        const data = await response.json();
                        this.success = '글이 저장되었습니다!';
                        this.article = data.data;

                        // Update slug if it changed
                        if (data.data.slug !== this.slug) {
                            setTimeout(() => {
                                window.location.href = `/articles/${data.data.slug}/edit`;
                            }, 1000);
                        }
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.saving = false;
                    }
                },

                confirmDelete() {
                    this.showDeleteModal = true;
                },

                async deleteArticle() {
                    this.deleting = true;
                    this.error = null;

                    try {
                        const response = await fetch(`/api/articles/${this.slug}`, {
                            method: 'DELETE',
                            headers: {
                                ...window.auth.getAuthHeaders()
                            }
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || '삭제에 실패했습니다.');
                        }

                        this.showDeleteModal = false;
                        this.success = '글이 삭제되었습니다.';

                        // Redirect to home page
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 1000);
                    } catch (err) {
                        this.error = err.message;
                        this.showDeleteModal = false;
                    } finally {
                        this.deleting = false;
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('ko-KR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
