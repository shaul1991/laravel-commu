{{--
    Comment Section Component
    게시글 상세 페이지의 댓글 섹션

    @props(['articleSlug'])
--}}

@props(['articleSlug'])

<section
    class="mt-12"
    x-data="commentSection('{{ $articleSlug }}')"
    x-init="init()"
>
    <h2 class="mb-6 text-xl font-bold text-neutral-900">
        댓글 <span class="text-neutral-500" x-text="'(' + (meta?.total || 0) + ')'"></span>
    </h2>

    {{-- Comment Form (로그인 사용자) --}}
    <form x-show="isAuthenticated" @submit.prevent="submitComment()" class="mb-8" x-cloak>
        <div class="rounded-xl border border-neutral-200 bg-white p-4">
            <textarea
                x-model="newComment"
                rows="3"
                class="w-full resize-none border-0 bg-transparent text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-0"
                placeholder="댓글을 작성하세요..."
                maxlength="1000"
            ></textarea>
            <div class="mt-3 flex items-center justify-between">
                <span class="text-sm text-neutral-500">
                    <span x-text="newComment.length"></span>/1000
                </span>
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="!newComment.trim() || submitting"
                    :class="{ 'opacity-50 cursor-not-allowed': !newComment.trim() || submitting }"
                >
                    <span x-show="!submitting">댓글 작성</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        작성 중...
                    </span>
                </button>
            </div>
        </div>
    </form>

    {{-- Login Prompt (비로그인 사용자) --}}
    <div x-show="!isAuthenticated" class="mb-8 rounded-xl border border-neutral-200 bg-neutral-50 p-6 text-center" x-cloak>
        <p class="text-neutral-600">댓글을 작성하려면 로그인이 필요합니다.</p>
        <a href="{{ route('login') }}" class="btn-primary mt-3 inline-block">로그인</a>
    </div>

    {{-- Sort Options --}}
    <div class="mb-4 flex items-center gap-4">
        <button
            @click="sortBy = 'latest'; fetchComments()"
            :class="sortBy === 'latest' ? 'text-primary-600 font-medium' : 'text-neutral-500 hover:text-neutral-700'"
            class="text-sm transition-colors"
        >
            최신순
        </button>
        <button
            @click="sortBy = 'popular'; fetchComments()"
            :class="sortBy === 'popular' ? 'text-primary-600 font-medium' : 'text-neutral-500 hover:text-neutral-700'"
            class="text-sm transition-colors"
        >
            좋아요순
        </button>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="space-y-4">
        <template x-for="i in 3">
            <div class="animate-pulse rounded-xl border border-neutral-200 bg-white p-5">
                <div class="flex gap-3">
                    <div class="h-10 w-10 rounded-full bg-neutral-200"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-24 rounded bg-neutral-200"></div>
                        <div class="h-4 w-full rounded bg-neutral-200"></div>
                        <div class="h-4 w-3/4 rounded bg-neutral-200"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Comments List --}}
    <div x-show="!loading" class="space-y-4">
        <template x-for="comment in comments" :key="comment.id">
            <div class="rounded-xl border border-neutral-200 bg-white p-5">
                {{-- Comment Header --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <template x-if="comment.author.avatar">
                            <img :src="comment.author.avatar" :alt="comment.author.name" class="h-10 w-10 rounded-full object-cover">
                        </template>
                        <template x-if="!comment.author.avatar">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-sm font-bold text-white">
                                <span x-text="comment.author.name.charAt(0)"></span>
                            </div>
                        </template>
                        <div>
                            <a :href="'/users/' + comment.author.username" class="font-semibold text-neutral-900 hover:text-primary-600" x-text="comment.author.name"></a>
                            <p class="text-sm text-neutral-500" x-text="formatDate(comment.created_at)"></p>
                        </div>
                    </div>

                    {{-- Actions Dropdown (로그인 사용자만) --}}
                    <div x-show="isAuthenticated" class="relative" x-data="{ open: false }" x-cloak>
                        <button @click="open = !open" class="rounded p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600" :dusk="'comment-menu-' + comment.id">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-1 w-32 rounded-lg border border-neutral-200 bg-white py-1 shadow-lg">
                            <button
                                x-show="comment.is_mine"
                                @click="editComment(comment); open = false"
                                class="block w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100"
                                :dusk="'comment-edit-' + comment.id"
                            >
                                수정
                            </button>
                            <button
                                x-show="comment.is_mine"
                                @click="deleteComment(comment.id); open = false"
                                class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                :dusk="'comment-delete-' + comment.id"
                            >
                                삭제
                            </button>
                            <button
                                x-show="!comment.is_mine"
                                @click="open = false"
                                class="block w-full px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-100"
                                :dusk="'comment-report-' + comment.id"
                            >
                                신고
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Comment Content --}}
                <div class="mt-3">
                    <template x-if="comment.is_deleted">
                        <p class="italic text-neutral-400">삭제된 댓글입니다.</p>
                    </template>
                    <template x-if="!comment.is_deleted && editingComment?.id !== comment.id">
                        <p class="whitespace-pre-wrap text-neutral-700" x-text="comment.content"></p>
                    </template>
                    {{-- Edit Form --}}
                    <template x-if="!comment.is_deleted && editingComment?.id === comment.id">
                        <form @submit.prevent="updateComment(comment)" class="space-y-3">
                            <textarea
                                x-model="editContent"
                                rows="3"
                                class="input w-full resize-none"
                                maxlength="1000"
                            ></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="cancelEdit()" class="btn-outline text-sm">취소</button>
                                <button
                                    type="submit"
                                    class="btn-primary text-sm"
                                    :disabled="!editContent.trim() || submitting"
                                >
                                    수정
                                </button>
                            </div>
                        </form>
                    </template>
                </div>

                {{-- Comment Actions --}}
                <div class="mt-4 flex items-center gap-4" x-show="!comment.is_deleted">
                    {{-- 로그인 사용자: 좋아요/답글 버튼 --}}
                    <template x-if="isAuthenticated">
                        <div class="flex items-center gap-4">
                            <button
                                @click="toggleLike(comment)"
                                class="flex items-center gap-1 text-sm transition-colors"
                                :class="comment.is_liked ? 'text-red-500' : 'text-neutral-500 hover:text-red-500'"
                                :dusk="'comment-like-button-' + comment.id"
                            >
                                <svg class="h-4 w-4" :fill="comment.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span x-text="comment.like_count"></span>
                            </button>
                            <button
                                @click="startReply(comment)"
                                class="flex items-center gap-1 text-sm text-neutral-500 transition-colors hover:text-primary-600"
                                :dusk="'comment-reply-button-' + comment.id"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                답글
                            </button>
                        </div>
                    </template>
                    {{-- 비로그인 사용자: 좋아요 수만 표시 --}}
                    <template x-if="!isAuthenticated">
                        <span class="flex items-center gap-1 text-sm text-neutral-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span x-text="comment.like_count"></span>
                        </span>
                    </template>
                </div>

                {{-- Reply Form (로그인 사용자만) --}}
                <div x-show="isAuthenticated && replyingTo === comment.id" class="mt-4 border-t border-neutral-100 pt-4" x-cloak>
                    <form @submit.prevent="submitReply(comment.id)">
                        <textarea
                            x-model="replyContent"
                            rows="2"
                            class="input w-full resize-none"
                            placeholder="답글을 작성하세요..."
                            maxlength="1000"
                        ></textarea>
                        <div class="mt-2 flex justify-end gap-2">
                            <button type="button" @click="cancelReply()" class="btn-outline text-sm">취소</button>
                            <button
                                type="submit"
                                class="btn-primary text-sm"
                                :disabled="!replyContent.trim() || submitting"
                                :dusk="'comment-reply-submit-' + comment.id"
                            >
                                답글 작성
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Replies --}}
                <div x-show="comment.replies && comment.replies.length > 0" class="mt-4 space-y-4 border-l-2 border-neutral-100 pl-4">
                    <template x-for="reply in comment.replies" :key="reply.id">
                        <div class="rounded-lg bg-neutral-50 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <template x-if="reply.author.avatar">
                                        <img :src="reply.author.avatar" :alt="reply.author.name" class="h-8 w-8 rounded-full object-cover">
                                    </template>
                                    <template x-if="!reply.author.avatar">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-xs font-bold text-white">
                                            <span x-text="reply.author.name.charAt(0)"></span>
                                        </div>
                                    </template>
                                    <div>
                                        <a :href="'/users/' + reply.author.username" class="text-sm font-semibold text-neutral-900 hover:text-primary-600" x-text="reply.author.name"></a>
                                        <p class="text-xs text-neutral-500" x-text="formatDate(reply.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <template x-if="reply.is_deleted">
                                    <p class="text-sm italic text-neutral-400">삭제된 댓글입니다.</p>
                                </template>
                                <template x-if="!reply.is_deleted">
                                    <p class="whitespace-pre-wrap text-sm text-neutral-700" x-text="reply.content"></p>
                                </template>
                            </div>
                            <div class="mt-2 flex items-center gap-4" x-show="!reply.is_deleted">
                                {{-- 로그인 사용자: 답글 좋아요 버튼 --}}
                                <template x-if="isAuthenticated">
                                    <button
                                        @click="toggleLike(reply)"
                                        class="flex items-center gap-1 text-xs transition-colors"
                                        :class="reply.is_liked ? 'text-red-500' : 'text-neutral-500 hover:text-red-500'"
                                    >
                                        <svg class="h-3.5 w-3.5" :fill="reply.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span x-text="reply.like_count"></span>
                                    </button>
                                </template>
                                {{-- 비로그인 사용자: 답글 좋아요 수만 표시 --}}
                                <template x-if="!isAuthenticated">
                                    <span class="flex items-center gap-1 text-xs text-neutral-500">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span x-text="reply.like_count"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="(comments || []).length === 0 && !loading" class="rounded-xl border border-neutral-200 bg-white p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="mt-4 text-neutral-500">아직 댓글이 없습니다. 첫 댓글을 작성해보세요!</p>
        </div>

        {{-- Load More --}}
        <div x-show="meta?.current_page < meta?.last_page" class="text-center">
            <button @click="loadMore()" class="btn-outline" :disabled="loadingMore">
                <span x-show="!loadingMore">더 보기</span>
                <span x-show="loadingMore">로딩 중...</span>
            </button>
        </div>
    </div>
</section>

<script>
function commentSection(articleSlug) {
    return {
        articleSlug: articleSlug,
        comments: [],
        meta: { total: 0, current_page: 1, last_page: 1 },
        newComment: '',
        replyContent: '',
        replyingTo: null,
        editingComment: null,
        editContent: '',
        sortBy: 'latest',
        loading: true,
        loadingMore: false,
        submitting: false,
        isAuthenticated: false,

        init() {
            // 클라이언트 사이드에서 인증 상태 확인
            this.isAuthenticated = window.auth?.isAuthenticated() || false;
            this.fetchComments();
        },

        async fetchComments(page = 1) {
            if (page === 1) this.loading = true;
            else this.loadingMore = true;

            try {
                const sort = this.sortBy === 'popular' ? 'popular' : 'latest';
                const url = `/api/articles/${this.articleSlug}/comments?page=${page}&sort=${sort}`;
                // 로그인 사용자는 인증 토큰 포함하여 요청 (is_mine 필드 정확히 반환받기 위함)
                const response = this.isAuthenticated
                    ? await window.auth.fetch(url)
                    : await fetch(url);

                if (!response.ok) {
                    console.warn('Comments API returned:', response.status);
                    return;
                }

                const data = await response.json();

                if (page === 1) {
                    this.comments = data?.data || [];
                } else {
                    this.comments = [...this.comments, ...(data?.data || [])];
                }
                this.meta = data?.meta || { total: 0, current_page: 1, last_page: 1 };
            } catch (error) {
                console.error('Failed to fetch comments:', error);
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },

        async submitComment() {
            if (!this.newComment.trim() || this.submitting) return;

            this.submitting = true;
            try {
                const response = await window.auth.fetch(`/api/articles/${this.articleSlug}/comments`, {
                    method: 'POST',
                    body: JSON.stringify({ content: this.newComment })
                });

                if (response.ok) {
                    this.newComment = '';
                    await this.fetchComments();
                } else {
                    const error = await response.json();
                    alert(error.message || '댓글 작성에 실패했습니다.');
                }
            } catch (error) {
                if (error.status === 401) return; // Already handled by auth.fetch
                console.error('Failed to submit comment:', error);
                alert('댓글 작성에 실패했습니다.');
            } finally {
                this.submitting = false;
            }
        },

        startReply(comment) {
            this.replyingTo = comment.id;
            this.replyContent = '';
        },

        cancelReply() {
            this.replyingTo = null;
            this.replyContent = '';
        },

        async submitReply(commentId) {
            if (!this.replyContent.trim() || this.submitting) return;

            this.submitting = true;
            try {
                const response = await window.auth.fetch(`/api/comments/${commentId}/replies`, {
                    method: 'POST',
                    body: JSON.stringify({ content: this.replyContent })
                });

                if (response.ok) {
                    this.replyContent = '';
                    this.replyingTo = null;
                    await this.fetchComments();
                } else {
                    const error = await response.json();
                    alert(error.message || '답글 작성에 실패했습니다.');
                }
            } catch (error) {
                if (error.status === 401) return; // Already handled by auth.fetch
                console.error('Failed to submit reply:', error);
                alert('답글 작성에 실패했습니다.');
            } finally {
                this.submitting = false;
            }
        },

        async toggleLike(comment) {
            try {
                const response = await window.auth.fetch(`/api/comments/${comment.id}/like`, {
                    method: 'POST'
                });

                if (response.ok) {
                    const data = await response.json();
                    comment.is_liked = data.data.is_liked;
                    comment.like_count = data.data.like_count;
                }
            } catch (error) {
                if (error.status === 401) return; // Already handled by auth.fetch
                console.error('Failed to toggle like:', error);
            }
        },

        editComment(comment) {
            this.editingComment = comment;
            this.editContent = comment.content;
        },

        cancelEdit() {
            this.editingComment = null;
            this.editContent = '';
        },

        async updateComment(comment) {
            if (!this.editContent.trim() || this.submitting) return;

            this.submitting = true;
            try {
                const response = await window.auth.fetch(`/api/comments/${comment.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ content: this.editContent })
                });

                if (response.ok) {
                    comment.content = this.editContent;
                    this.cancelEdit();
                } else {
                    const error = await response.json();
                    alert(error.message || '댓글 수정에 실패했습니다.');
                }
            } catch (error) {
                if (error.status === 401) return; // Already handled by auth.fetch
                console.error('Failed to update comment:', error);
                alert('댓글 수정에 실패했습니다.');
            } finally {
                this.submitting = false;
            }
        },

        async deleteComment(commentId) {
            if (!confirm('정말 이 댓글을 삭제하시겠습니까?')) return;

            try {
                const response = await window.auth.fetch(`/api/comments/${commentId}`, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    await this.fetchComments();
                } else {
                    const error = await response.json();
                    alert(error.message || '댓글 삭제에 실패했습니다.');
                }
            } catch (error) {
                if (error.status === 401) return; // Already handled by auth.fetch
                console.error('Failed to delete comment:', error);
                alert('댓글 삭제에 실패했습니다.');
            }
        },

        loadMore() {
            this.fetchComments(this.meta.current_page + 1);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return '방금 전';
            if (minutes < 60) return `${minutes}분 전`;
            if (hours < 24) return `${hours}시간 전`;
            if (days < 7) return `${days}일 전`;

            return date.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long', day: 'numeric' });
        }
    };
}
</script>
