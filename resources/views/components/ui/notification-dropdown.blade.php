{{--
    Notification Dropdown Component
    헤더에서 사용하는 알림 드롭다운
--}}

<div
    class="relative"
    x-data="notificationDropdown()"
    x-init="fetchUnreadCount()"
    @click.away="open = false"
>
    {{-- Trigger Button --}}
    <button
        @click="toggle()"
        class="relative rounded-full p-2 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-700 transition-colors"
        aria-label="알림"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- Unread Badge --}}
        <span
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white"
        ></span>
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-xl border border-neutral-200 bg-white shadow-lg"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-neutral-100 px-4 py-3">
            <h3 class="font-semibold text-neutral-900">알림</h3>
            <button
                x-show="unreadCount > 0"
                @click="markAllAsRead()"
                class="text-sm text-primary-600 hover:text-primary-700"
            >
                모두 읽음
            </button>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="p-4">
            <div class="space-y-3">
                <template x-for="i in 3">
                    <div class="flex animate-pulse gap-3">
                        <div class="h-10 w-10 rounded-full bg-neutral-200"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 w-3/4 rounded bg-neutral-200"></div>
                            <div class="h-3 w-1/2 rounded bg-neutral-200"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Notifications List --}}
        <div x-show="!loading" class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-2 text-sm text-neutral-500">알림이 없습니다</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <button
                    @click="handleNotificationClick(notification)"
                    class="flex w-full gap-3 px-4 py-3 text-left transition-colors hover:bg-neutral-50"
                    :class="{ 'bg-primary-50': !notification.is_read }"
                >
                    {{-- Icon --}}
                    <div class="flex-shrink-0">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            :class="getIconClass(notification.type)"
                        >
                            <template x-if="notification.type === 'comment'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </template>
                            <template x-if="notification.type === 'reply'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </template>
                            <template x-if="notification.type === 'follow'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </template>
                            <template x-if="notification.type === 'like'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </template>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-neutral-700" x-text="notification.message"></p>
                        <p class="mt-0.5 text-xs text-neutral-500" x-text="formatDate(notification.created_at)"></p>
                    </div>

                    {{-- Unread indicator --}}
                    <div x-show="!notification.is_read" class="flex-shrink-0">
                        <span class="block h-2 w-2 rounded-full bg-primary-500"></span>
                    </div>
                </button>
            </template>
        </div>

        {{-- Footer --}}
        <div class="border-t border-neutral-100 px-4 py-3">
            <a href="/notifications" class="block text-center text-sm font-medium text-primary-600 hover:text-primary-700">
                모든 알림 보기
            </a>
        </div>
    </div>
</div>

<script>
function notificationDropdown() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        pollingInterval: null,

        init() {
            // Poll for new notifications every 30 seconds
            this.pollingInterval = setInterval(() => {
                this.fetchUnreadCount();
            }, 30000);
        },

        destroy() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await window.auth.fetch('/api/notifications/unread-count');

                if (response.ok) {
                    const data = await response.json();
                    this.unreadCount = data.data.count;
                }
            } catch (error) {
                console.error('Failed to fetch unread count:', error);
            }
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await window.auth.fetch('/api/notifications?per_page=10');

                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.data;
                }
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            } finally {
                this.loading = false;
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

        async handleNotificationClick(notification) {
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

            // Navigate to related content
            if (notification.data?.article_slug) {
                window.location.href = `/articles/${notification.data.article_slug}`;
            } else if (notification.data?.username) {
                window.location.href = `/users/${notification.data.username}`;
            }

            this.open = false;
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

            return date.toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        }
    };
}
</script>
