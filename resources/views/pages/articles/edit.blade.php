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
                                <option value="tech">기술</option>
                                <option value="career">커리어</option>
                                <option value="life">일상</option>
                                <option value="news">뉴스</option>
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
                                <div class="w-px h-5 bg-neutral-300 mx-1"></div>
                                {{-- Mermaid Dropdown --}}
                                <div class="relative">
                                    <button type="button" class="p-2 rounded hover:bg-neutral-200" title="다이어그램" @click="showMermaidDropdown = !showMermaidDropdown">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                    </button>
                                    <div x-show="showMermaidDropdown" x-cloak @click.outside="showMermaidDropdown = false" @keydown.escape.window="showMermaidDropdown = false"
                                         class="absolute left-0 top-full mt-1 w-48 bg-white border border-neutral-200 rounded-lg shadow-lg z-10 py-1">
                                        <button type="button" @click="insertMermaid('flowchart')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                            플로우차트
                                        </button>
                                        <button type="button" @click="insertMermaid('sequence')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            시퀀스 다이어그램
                                        </button>
                                        <button type="button" @click="insertMermaid('class')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            클래스 다이어그램
                                        </button>
                                        <button type="button" @click="insertMermaid('state')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            상태 다이어그램
                                        </button>
                                        <button type="button" @click="insertMermaid('er')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                                            ER 다이어그램
                                        </button>
                                        <button type="button" @click="insertMermaid('gantt')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            간트 차트
                                        </button>
                                        <button type="button" @click="insertMermaid('pie')" class="w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                                            파이 차트
                                        </button>
                                    </div>
                                </div>
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
                            <div x-ref="previewContent" x-html="renderPreview()" x-effect="if (showPreview) { $nextTick(() => window.mermaid?.render($refs.previewContent)) }"></div>
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
                showMermaidDropdown: false,
                isAuthenticated: false,
                loading: true,
                loadError: null,
                saving: false,
                deleting: false,
                uploading: false,
                error: null,
                success: null,

                // Mermaid 템플릿
                mermaidTemplates: {
                    flowchart: `flowchart TD
    A[시작] --> B{조건}
    B -->|Yes| C[작업 1]
    B -->|No| D[작업 2]
    C --> E[종료]
    D --> E`,
                    sequence: `sequenceDiagram
    participant User
    participant Server
    User->>Server: 요청
    Server-->>User: 응답`,
                    class: `classDiagram
    class Animal {
        +String name
        +makeSound()
    }
    class Dog {
        +bark()
    }
    Animal <|-- Dog`,
                    state: `stateDiagram-v2
    [*] --> 대기
    대기 --> 처리중: 시작
    처리중 --> 완료: 성공
    처리중 --> 오류: 실패
    완료 --> [*]
    오류 --> 대기: 재시도`,
                    er: `erDiagram
    USER ||--o{ POST : writes
    POST ||--o{ COMMENT : has`,
                    gantt: `gantt
    title 프로젝트 일정
    dateFormat YYYY-MM-DD
    section 개발
    기능 A: 2024-01-01, 5d
    기능 B: 2024-01-06, 3d`,
                    pie: `pie title 비율
    "A" : 40
    "B" : 30
    "C" : 30`
                },

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
                        const response = await window.auth.fetch(`/api/articles/${this.slug}`, {
                            method: 'GET'
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
                        if (err.status === 401) {
                            return; // Already redirected to login
                        }
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

                insertMermaid(type) {
                    const template = this.mermaidTemplates[type] || this.mermaidTemplates.flowchart;
                    const mermaidBlock = '```mermaid\n' + template + '\n```';

                    const textarea = this.$refs.contentEditor;
                    const start = textarea.selectionStart;
                    const text = this.content;

                    // 앞뒤에 빈 줄 추가
                    const before = start > 0 && text[start - 1] !== '\n' ? '\n\n' : (start > 0 ? '\n' : '');
                    const after = '\n\n';

                    this.content = text.substring(0, start) + before + mermaidBlock + after + text.substring(start);

                    this.showMermaidDropdown = false;

                    this.$nextTick(() => {
                        textarea.focus();
                        const newPos = start + before.length + mermaidBlock.length + after.length;
                        textarea.setSelectionRange(newPos, newPos);
                    });
                },

                renderPreview() {
                    if (!this.content) {
                        return '<p class="text-neutral-400">내용을 입력하세요...</p>';
                    }

                    // 간단한 마크다운 → HTML 변환 (클라이언트 사이드)
                    let html = this.content;

                    // Mermaid 코드 블록 처리
                    html = html.replace(/```mermaid\n([\s\S]*?)```/g, (match, code) => {
                        return `<pre class="mermaid">${this.escapeHtml(code.trim())}</pre>`;
                    });

                    // 일반 코드 블록
                    html = html.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
                        return `<pre><code class="language-${lang || ''}">${this.escapeHtml(code.trim())}</code></pre>`;
                    });

                    // 인라인 코드
                    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

                    // 제목
                    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
                    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
                    html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');

                    // 굵게, 기울임
                    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

                    // 링크
                    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary-600 hover:underline">$1</a>');

                    // 이미지
                    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="max-w-full rounded">');

                    // 인용
                    html = html.replace(/^> (.+)$/gm, '<blockquote class="border-l-4 border-neutral-300 pl-4 italic text-neutral-600">$1</blockquote>');

                    // 리스트
                    html = html.replace(/^- (.+)$/gm, '<li>$1</li>');
                    html = html.replace(/(<li>.*<\/li>\n?)+/g, '<ul class="list-disc pl-6">$&</ul>');

                    // 번호 리스트
                    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

                    // 줄바꿈
                    html = html.replace(/\n\n/g, '</p><p>');
                    html = '<p>' + html + '</p>';

                    return html;
                },

                escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
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
                        if (err.status === 401) {
                            return; // Already redirected to login
                        }
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
                        const response = await window.auth.fetch(`/api/articles/${this.slug}`, {
                            method: 'PUT',
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
                        if (err.status === 401) {
                            return; // Already redirected to login
                        }
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
                        const response = await window.auth.fetch(`/api/articles/${this.slug}`, {
                            method: 'DELETE'
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
                        if (err.status === 401) {
                            return; // Already redirected to login
                        }
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
