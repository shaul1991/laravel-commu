<x-layouts.app>
    <x-slot:title>글 작성</x-slot:title>

    <div class="py-8" x-data="articleWrite()" x-init="init()">
        {{-- Authentication Required --}}
        <template x-if="!isAuthenticated">
            <div class="max-w-4xl mx-auto">
                <div class="card p-8 text-center">
                    <svg class="h-16 w-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="text-xl font-bold text-neutral-900 mb-2">로그인이 필요합니다</h2>
                    <p class="text-neutral-600 mb-6">글을 작성하려면 로그인이 필요합니다.</p>
                    <a href="/login?redirect=/write" class="btn-primary">로그인하기</a>
                </div>
            </div>
        </template>

        {{-- Editor --}}
        <template x-if="isAuthenticated">
            <div class="max-w-4xl mx-auto">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <a href="/" class="text-neutral-500 hover:text-neutral-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                        <h1 class="text-xl font-bold text-neutral-900">새 글 작성</h1>
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
                            class="btn-outline"
                            :disabled="saving"
                            @click="saveDraft()"
                        >
                            <span x-show="!savingDraft">임시저장</span>
                            <span x-show="savingDraft">저장 중...</span>
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            :disabled="saving || !canPublish"
                            @click="publish()"
                        >
                            <span x-show="!publishing">발행하기</span>
                            <span x-show="publishing">발행 중...</span>
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

                {{-- Editor --}}
                <div x-show="!showPreview" class="space-y-4">
                    {{-- Required Fields Section --}}
                    <div class="card">
                        <div class="p-6 space-y-6">
                            {{-- Title --}}
                            <div>
                                <input
                                    type="text"
                                    x-model="title"
                                    placeholder="제목을 입력하세요"
                                    class="w-full text-3xl font-bold border-0 border-b border-neutral-200 pb-4 focus:border-primary-500 focus:ring-0 placeholder-neutral-400"
                                >
                            </div>

                            {{-- Content Editor --}}
                            <div>
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

                    {{-- Optional Fields Section --}}
                    <div class="card bg-neutral-50/50">
                        {{-- Toggle Header --}}
                        <button
                            type="button"
                            @click="showOptionalFields = !showOptionalFields"
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-neutral-100/50 transition-colors rounded-t-lg"
                        >
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-medium text-neutral-500 uppercase tracking-wide">추가 설정 (선택사항)</h3>
                                <span x-show="category || tags.length > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700">
                                    <span x-text="(category ? 1 : 0) + tags.length"></span>개 설정됨
                                </span>
                            </div>
                            <svg
                                class="h-5 w-5 text-neutral-400 transition-transform duration-200"
                                :class="{'rotate-180': showOptionalFields}"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Collapsible Content --}}
                        <div
                            x-show="showOptionalFields"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            x-cloak
                        >
                            <div class="px-6 pb-6 pt-2 space-y-5 border-t border-neutral-200/50">
                                <div class="space-y-5">
                                {{-- Category --}}
                                <div>
                                    <label for="category" class="block text-sm font-medium text-neutral-700 mb-1.5">카테고리</label>
                                    <select id="category" x-model="category" class="input max-w-xs">
                                        <option value="">카테고리 선택</option>
                                        <option value="tech">기술</option>
                                        <option value="career">커리어</option>
                                        <option value="life">일상</option>
                                        <option value="news">뉴스</option>
                                    </select>
                                </div>

                                {{-- Tags with Autocomplete --}}
                                <div class="relative">
                                    <label class="block text-sm font-medium text-neutral-700 mb-1.5">태그</label>
                                    {{-- Tag Input --}}
                                    <div class="relative max-w-md">
                                        <input
                                            type="text"
                                            x-model="tagInput"
                                            @input.debounce.300ms="searchTags()"
                                            @keydown.enter.prevent="if(!$event.isComposing) addTagFromInput()"
                                            @keydown.arrow-down.prevent="navigateSuggestion(1)"
                                            @keydown.arrow-up.prevent="navigateSuggestion(-1)"
                                            @keydown.escape="closeSuggestions()"
                                            @focus="tagInput.length >= 1 && searchTags()"
                                            @blur.debounce.200ms="closeSuggestions()"
                                            placeholder="태그를 입력하세요 (Enter로 추가)"
                                            class="input w-full"
                                        >
                                        {{-- Autocomplete Dropdown --}}
                                        <div
                                            x-show="showTagSuggestions && tagSuggestions.length > 0"
                                            x-cloak
                                            class="absolute top-full left-0 right-0 mt-1 rounded-lg border border-neutral-200 bg-white shadow-lg max-h-48 overflow-y-auto z-50"
                                        >
                                            <template x-for="(suggestion, index) in tagSuggestions" :key="suggestion.id">
                                                <button
                                                    type="button"
                                                    @mousedown.prevent="selectTagSuggestion(suggestion)"
                                                    :class="{'bg-primary-50': selectedSuggestionIndex === index}"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-neutral-100 flex items-center justify-between"
                                                >
                                                    <span>
                                                        <span class="text-neutral-400">#</span>
                                                        <span x-text="suggestion.name"></span>
                                                    </span>
                                                    <span class="text-xs text-neutral-400" x-text="suggestion.article_count + '개의 글'"></span>
                                                </button>
                                            </template>
                                            {{-- Create new tag option --}}
                                            <button
                                                type="button"
                                                x-show="tagInput.trim() && !tagSuggestions.find(s => s.name.toLowerCase() === tagInput.trim().toLowerCase())"
                                                @mousedown.prevent="addTagFromInput()"
                                                :class="{'bg-primary-50': selectedSuggestionIndex === tagSuggestions.length}"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-neutral-100 border-t border-neutral-100"
                                            >
                                                <span class="text-primary-600">+ 새 태그 "</span>
                                                <span class="font-medium text-primary-700" x-text="tagInput.trim()"></span>
                                                <span class="text-primary-600">" 만들기</span>
                                            </button>
                                        </div>
                                    </div>
                                    {{-- Selected Tags --}}
                                    <div class="flex flex-wrap gap-2 mt-2" x-show="tags.length > 0">
                                        <template x-for="tag in tags" :key="tag">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-sm">
                                                <span class="text-primary-400">#</span>
                                                <span x-text="tag"></span>
                                                <button type="button" @click="removeTag(tag)" class="hover:text-primary-900">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                </div>
                            </div>
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
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function articleWrite() {
            return {
                title: '',
                content: '',
                category: '',
                tags: [],
                tagInput: '',
                tagSuggestions: [],
                showTagSuggestions: false,
                selectedSuggestionIndex: -1,
                searchingTags: false,
                showPreview: false,
                showOptionalFields: false,
                isAuthenticated: false,
                saving: false,
                savingDraft: false,
                publishing: false,
                uploading: false,
                error: null,
                success: null,

                init() {
                    this.isAuthenticated = window.auth?.isAuthenticated() ?? false;
                },

                get canPublish() {
                    return this.title.trim() && this.content.trim();
                },

                async searchTags() {
                    const query = this.tagInput.trim();
                    if (query.length < 1) {
                        this.tagSuggestions = [];
                        this.showTagSuggestions = false;
                        return;
                    }

                    this.searchingTags = true;
                    try {
                        const response = await fetch(`/api/tags/search?q=${encodeURIComponent(query)}&limit=5`);
                        if (response.ok) {
                            const data = await response.json();
                            this.tagSuggestions = data.data.filter(tag =>
                                !this.tags.includes(tag.name)
                            );
                            this.showTagSuggestions = true;
                            this.selectedSuggestionIndex = -1;
                        }
                    } catch (err) {
                        console.error('Tag search failed:', err);
                    } finally {
                        this.searchingTags = false;
                    }
                },

                selectTagSuggestion(suggestion) {
                    if (!this.tags.includes(suggestion.name)) {
                        this.tags.push(suggestion.name);
                    }
                    this.tagInput = '';
                    this.tagSuggestions = [];
                    this.showTagSuggestions = false;
                    this.selectedSuggestionIndex = -1;
                },

                addTagFromInput() {
                    // If a suggestion is selected, use it
                    if (this.selectedSuggestionIndex >= 0 && this.selectedSuggestionIndex < this.tagSuggestions.length) {
                        this.selectTagSuggestion(this.tagSuggestions[this.selectedSuggestionIndex]);
                        return;
                    }

                    // Otherwise, add the input as a new tag
                    const tagName = this.tagInput.trim();
                    if (tagName && !this.tags.includes(tagName)) {
                        this.tags.push(tagName);
                    }
                    this.tagInput = '';
                    this.tagSuggestions = [];
                    this.showTagSuggestions = false;
                    this.selectedSuggestionIndex = -1;
                },

                navigateSuggestion(direction) {
                    if (!this.showTagSuggestions) return;

                    const maxIndex = this.tagSuggestions.length; // Include "create new" option
                    this.selectedSuggestionIndex += direction;

                    if (this.selectedSuggestionIndex < -1) {
                        this.selectedSuggestionIndex = maxIndex - 1;
                    } else if (this.selectedSuggestionIndex >= maxIndex) {
                        this.selectedSuggestionIndex = -1;
                    }
                },

                closeSuggestions() {
                    this.showTagSuggestions = false;
                    this.selectedSuggestionIndex = -1;
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

                        const response = await window.auth.fetch('/api/images/upload', {
                            method: 'POST',
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

                async saveDraft() {
                    if (!this.title.trim()) {
                        this.error = '제목을 입력해주세요.';
                        return;
                    }

                    this.savingDraft = true;
                    this.saving = true;
                    this.error = null;
                    this.success = null;

                    try {
                        const response = await window.auth.fetch('/api/articles', {
                            method: 'POST',
                            body: JSON.stringify({
                                title: this.title,
                                content: this.content,
                                category: this.category || null,
                                tags: this.tags,
                                is_draft: true
                            })
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || '임시저장에 실패했습니다.');
                        }

                        const data = await response.json();
                        this.success = '임시저장되었습니다.';

                        // Redirect to edit page after draft is saved
                        setTimeout(() => {
                            window.location.href = `/articles/${data.data.slug}/edit`;
                        }, 1000);
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.savingDraft = false;
                        this.saving = false;
                    }
                },

                async publish() {
                    if (!this.canPublish) {
                        this.error = '제목과 본문을 입력해주세요.';
                        return;
                    }

                    this.publishing = true;
                    this.saving = true;
                    this.error = null;
                    this.success = null;

                    try {
                        const response = await window.auth.fetch('/api/articles', {
                            method: 'POST',
                            body: JSON.stringify({
                                title: this.title,
                                content: this.content,
                                category: this.category || null,
                                tags: this.tags,
                                is_draft: false
                            })
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || '발행에 실패했습니다.');
                        }

                        const data = await response.json();
                        this.success = '글이 발행되었습니다!';

                        // Redirect to the published article
                        setTimeout(() => {
                            window.location.href = `/articles/${data.data.slug}`;
                        }, 1000);
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.publishing = false;
                        this.saving = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
