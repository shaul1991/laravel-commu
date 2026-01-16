<x-layouts.app>
    <x-slot:title>글 작성</x-slot:title>

    <div class="py-8" x-data="{
        title: '',
        content: '',
        category: '',
        tags: [],
        tagInput: '',
        showPreview: false,
        addTag() {
            if (this.tagInput.trim() && this.tags.length < 5 && !this.tags.includes(this.tagInput.trim())) {
                this.tags.push(this.tagInput.trim());
                this.tagInput = '';
            }
        },
        removeTag(tag) {
            this.tags = this.tags.filter(t => t !== tag);
        }
    }">
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
                    <button type="button" class="btn-outline">임시저장</button>
                    <button type="submit" class="btn-primary">발행하기</button>
                </div>
            </div>

            {{-- Editor --}}
            <div class="card" x-show="!showPreview">
                <div class="p-6 space-y-6">
                    {{-- Category --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-neutral-700 mb-1.5">카테고리</label>
                        <select id="category" x-model="category" class="input max-w-xs">
                            <option value="">카테고리 선택</option>
                            <option value="tech">기술</option>
                            <option value="career">커리어</option>
                            <option value="life">일상</option>
                            <option value="review">리뷰</option>
                        </select>
                    </div>

                    {{-- Title --}}
                    <div>
                        <input
                            type="text"
                            x-model="title"
                            placeholder="제목을 입력하세요"
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
                        <label class="block text-sm font-medium text-neutral-700 mb-1.5">본문</label>
                        {{-- Toolbar --}}
                        <div class="flex items-center gap-1 p-2 border border-b-0 border-neutral-200 rounded-t-lg bg-neutral-50">
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="굵게">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h8a4 4 0 000-8H6v8zm0 0h9a4 4 0 010 8H6v-8z" />
                                </svg>
                            </button>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="기울임">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </button>
                            <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="제목">
                                <span class="text-sm font-bold">H</span>
                            </button>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="인용">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                </svg>
                            </button>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="코드">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </button>
                            <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="목록">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="번호 목록">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                            </button>
                            <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="링크">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </button>
                            <button type="button" class="p-2 rounded hover:bg-neutral-200" title="이미지">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                        {{-- Textarea --}}
                        <textarea
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
        </div>
    </div>
</x-layouts.app>
