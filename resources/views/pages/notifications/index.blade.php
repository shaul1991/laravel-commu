<x-layouts.app>
    <x-slot:title>알림</x-slot:title>

    <div class="py-8" x-data="notificationsPage()" x-init="fetchNotifications()">
        <div class="max-w-2xl mx-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-neutral-900">알림</h1>
                <button
                    x-show="hasUnread"
                    @click="markAllAsRead()"
                    class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                >
                    모두 읽음 처리
                </button>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-4 mb-6 border-b border-neutral-200">
                <button
                    @click="filter = 'all'; fetchNotifications()"
                    :class="filter === 'all' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                    class="pb-3 border-b-2 font-medium transition-colors"
                >
                    전체
                </button>
                <button
                    @click="filter = 'unread'; fetchNotifications()"
                    :class="filter === 'unread' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700'"
                    class="pb-3 border-b-2 font-medium transition-colors"
                >
                    읽지 않음
                    <span x-show="unreadCount > 0" class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 text-red-600 rounded-full" x-text="unreadCount"></span>
                </button>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="space-y-4">
                <template x-for="i in 5">
                    <div class="animate-pulse card p-4">
                        <div class="flex gap-4">
                            <div class="h-12 w-12 rounded-full bg-neutral-200"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-3/4 rounded bg-neutral-200"></div>
                                <div class="h-3 w-1/2 rounded bg-neutral-200"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Notifications List --}}
            <div x-show="!loading" class="space-y-3">
                <template x-if="notifications.length === 0">
                    <div class="card p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="mt-4 text-neutral-500">
                            <span x-show="filter === 'all'">알림이 없습니다</span>
                            <span x-show="filter === 'unread'">읽지 않은 알림이 없습니다</span>
                        </p>
                    </div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <div
                        @click="handleClick(notification)"
                        class="card p-4 cursor-pointer transition-all hover:shadow-md"
                        :class="{ 'bg-primary-50 border-primary-200': !notification.is_read }"
                    >
                        <div class="flex gap-4">
                            {{-- Icon --}}
                            <div
                                class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-full"
                                :class="getIconClass(notification.type)"
                            >
                                <template x-if="notification.type === 'comment'">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </template>
                                <template x-if="notification.type === 'reply'">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </template>
                                <template x-if="notification.type === 'follow'">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </template>
                                <template x-if="notification.type === 'like'">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </template>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-neutral-800" x-text="notification.message"></p>
                                <p class="mt-1 text-sm text-neutral-500" x-text="formatDate(notification.created_at)"></p>
                            </div>

                            {{-- Unread indicator --}}
                            <div x-show="!notification.is_read" class="flex-shrink-0 self-center">
                                <span class="block h-3 w-3 rounded-full bg-primary-500"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Load More --}}
                <div x-show="meta.current_page < meta.last_page" class="pt-4 text-center">
                    <button @click="loadMore()" class="btn-outline" :disabled="loadingMore">
                        <span x-show="!loadingMore">더 보기</span>
                        <span x-show="loadingMore" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            로딩 중...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
function notificationsPage() {
    return {
        notifications: [],
        meta: { current_page: 1, last_page: 1, total: 0 },
        filter: 'all',
        loading: true,
        loadingMore: false,
        unreadCount: 0,

        get hasUnread() {
            return this.notifications.some(n => !n.is_read);
        },

        async fetchNotifications(page = 1) {
            if (page === 1) this.loading = true;
            else this.loadingMore = true;

            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 20
                });
                if (this.filter === 'unread') {
                    params.append('unread_only', 'true');
                }

                const response = await window.auth.fetch(`/api/notifications?${params}`);

                if (response.ok) {
                    const data = await response.json();
                    if (page === 1) {
                        this.notifications = data.data;
                    } else {
                        this.notifications = [...this.notifications, ...data.data];
                    }
                    this.meta = data.meta;
                    this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                }
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },

        async markAllAsRead() {
            try {
                const response = await window.auth.fetch('/api/notifications/read-all', {
                    method: 'POST'
                });

                if (response.ok) {
                    this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },

        async handleClick(notification) {
            // Mark as read
            if (!notification.is_read) {
                try {
                    await window.auth.fetch(`/api/notifications/${notification.id}/read`, {
                        method: 'POST'
                    });

                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch (error) {
                    console.error('Failed to mark as read:', error);
                }
            }

            // Navigate
            if (notification.data?.article_slug) {
                window.location.href = `/articles/${notification.data.article_slug}`;
            } else if (notification.data?.username) {
                window.location.href = `/users/${notification.data.username}`;
            }
        },

        loadMore() {
            this.fetchNotifications(this.meta.current_page + 1);
        },

        getIconClass(type) {
            const classes = {
                comment: 'bg-blue-100 text-blue-600',
                reply: 'bg-green-100 text-green-600',
                follow: 'bg-purple-100 text-purple-600',
                like: 'bg-red-100 text-red-600'
            };
            return classes[type] || 'bg-neutral-100 text-neutral-600';
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
