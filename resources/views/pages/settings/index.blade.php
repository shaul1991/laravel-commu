<x-layouts.app>
    <x-slot:title>설정</x-slot:title>

    <div class="py-8" x-data="settingsPage()" x-init="init()" x-cloak>
        {{-- Not Authenticated State --}}
        <div x-show="!isAuthenticated && !loading" class="max-w-md mx-auto text-center py-16">
            <svg class="mx-auto h-16 w-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h2 class="mt-4 text-xl font-bold text-neutral-900">로그인이 필요합니다</h2>
            <p class="mt-2 text-neutral-600">설정을 변경하려면 먼저 로그인해주세요.</p>
            <a href="/login" class="mt-6 inline-block btn-primary">로그인</a>
        </div>

        <div x-show="isAuthenticated" class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-neutral-900">설정</h1>
                <p class="mt-1 text-sm text-neutral-600">계정 정보와 환경설정을 관리하세요</p>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="card p-12">
                <div class="flex items-center justify-center">
                    <svg class="h-8 w-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <div x-show="!loading" class="flex gap-8">
                {{-- Sidebar --}}
                <nav class="w-48 flex-shrink-0">
                    <ul class="space-y-1">
                        <li>
                            <button
                                @click="tab = 'profile'"
                                :class="tab === 'profile' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50'"
                                class="w-full text-left px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                프로필
                            </button>
                        </li>
                        <li>
                            <button
                                @click="tab = 'account'"
                                :class="tab === 'account' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50'"
                                class="w-full text-left px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                계정
                            </button>
                        </li>
                        <li>
                            <button
                                @click="tab = 'notifications'"
                                :class="tab === 'notifications' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50'"
                                class="w-full text-left px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                알림
                            </button>
                        </li>
                    </ul>
                </nav>

                {{-- Content --}}
                <div class="flex-1">
                    {{-- Success/Error Messages --}}
                    <div x-show="successMessage" x-transition class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                        <span x-text="successMessage"></span>
                    </div>
                    <div x-show="errorMessage" x-transition class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        <span x-text="errorMessage"></span>
                    </div>

                    {{-- Profile Tab --}}
                    <div x-show="tab === 'profile'" class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">프로필 정보</h2>

                        <form @submit.prevent="updateProfile()" class="space-y-6">
                            {{-- Avatar --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">프로필 사진</label>
                                <div class="flex items-center gap-4">
                                    <template x-if="profile.avatar">
                                        <img :src="profile.avatar" alt="프로필" class="w-20 h-20 rounded-full object-cover">
                                    </template>
                                    <template x-if="!profile.avatar">
                                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-2xl font-bold">
                                            <span x-text="profile.name ? profile.name.charAt(0) : ''"></span>
                                        </div>
                                    </template>
                                    <div>
                                        <button type="button" class="btn-outline text-sm" disabled>사진 변경 (준비중)</button>
                                        <p class="mt-1 text-xs text-neutral-500">JPG, PNG 형식. 최대 2MB</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-neutral-700 mb-1.5">이름</label>
                                <input type="text" id="name" x-model="profile.name" class="input max-w-md" required>
                                <template x-if="errors.name">
                                    <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                                </template>
                            </div>

                            {{-- Bio --}}
                            <div>
                                <label for="bio" class="block text-sm font-medium text-neutral-700 mb-1.5">소개</label>
                                <textarea id="bio" x-model="profile.bio" rows="3" class="input resize-none" maxlength="200"></textarea>
                                <p class="mt-1 text-xs text-neutral-500"><span x-text="(profile.bio || '').length"></span>/200</p>
                                <template x-if="errors.bio">
                                    <p class="mt-1 text-sm text-red-600" x-text="errors.bio[0]"></p>
                                </template>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary" :disabled="saving">
                                    <span x-show="!saving">변경사항 저장</span>
                                    <span x-show="saving">저장 중...</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Account Tab --}}
                    <div x-show="tab === 'account'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">계정 정보</h2>

                        {{-- Current Email Display (Read-only) --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-neutral-700 mb-1.5">이메일</label>
                            <p class="text-neutral-900" x-text="currentEmail"></p>
                            <p class="mt-1 text-xs text-neutral-500">이메일은 소셜 로그인 계정에서 가져옵니다.</p>
                        </div>

                        {{-- Social Account Linking Section --}}
                        <div class="pt-6 border-t border-neutral-200">
                            <h3 class="text-lg font-bold text-neutral-900 mb-2">소셜 계정 연동</h3>
                            <p class="text-sm text-neutral-600 mb-4">소셜 계정을 연동하여 간편하게 로그인하세요</p>

                            <div class="space-y-3">
                                {{-- GitHub --}}
                                <div class="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div :class="socialAccounts.github ? 'bg-neutral-900' : 'bg-neutral-100'" class="w-10 h-10 flex items-center justify-center rounded-full">
                                            <svg class="w-5 h-5" :class="socialAccounts.github ? 'text-white' : 'text-neutral-400'" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-900">GitHub</span>
                                            <p class="text-sm" :class="socialAccounts.github ? 'text-neutral-600' : 'text-neutral-500'" x-text="socialAccounts.github ? (socialAccounts.github.provider_email || socialAccounts.github.nickname) : '연동되지 않음'"></p>
                                        </div>
                                    </div>
                                    <template x-if="!socialAccounts.github">
                                        <button @click="linkSocialAccount('github')" class="btn-outline text-sm" :disabled="socialLoading">연동하기</button>
                                    </template>
                                    <template x-if="socialAccounts.github">
                                        <span class="text-sm text-green-600 font-medium">연동됨</span>
                                    </template>
                                </div>
                            </div>

                            {{-- 마지막 인증 수단 안내 --}}
                            <p class="mt-3 text-sm text-neutral-600">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                소셜 계정으로만 로그인할 수 있습니다.
                            </p>
                        </div>

                        {{-- Danger Zone --}}
                        <div class="mt-12 pt-6 border-t border-neutral-200">
                            <h3 class="text-lg font-bold text-red-600 mb-4">위험 구역</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                                    <div>
                                        <h4 class="font-medium text-neutral-900">계정 삭제</h4>
                                        <p class="text-sm text-neutral-600">계정과 모든 데이터가 영구적으로 삭제됩니다. 이 작업은 되돌릴 수 없습니다.</p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showDeleteConfirm = true"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                                    >
                                        계정 삭제
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Delete Account Modal --}}
                        <div x-show="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
                            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl" @click.away="showDeleteConfirm = false">
                                <h3 class="text-lg font-bold text-red-600 mb-4">계정을 삭제하시겠습니까?</h3>
                                <p class="text-neutral-600 mb-6">이 작업은 되돌릴 수 없습니다. 모든 데이터(글, 댓글, 좋아요 등)가 영구적으로 삭제됩니다.</p>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-neutral-700 mb-1.5">확인을 위해 <span class="text-red-600 font-bold">"삭제합니다"</span>를 입력하세요</label>
                                    <input type="text" x-model="deleteConfirmation" class="input" placeholder="삭제합니다">
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="button" @click="showDeleteConfirm = false; deleteConfirmation = ''" class="btn-outline">취소</button>
                                    <button
                                        type="button"
                                        @click="deleteAccount()"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                                        :disabled="deleteConfirmation !== '삭제합니다' || deleting"
                                    >
                                        <span x-show="!deleting">계정 삭제</span>
                                        <span x-show="deleting">삭제 중...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notifications Tab --}}
                    <div x-show="tab === 'notifications'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">알림 설정</h2>

                        <form @submit.prevent="updateNotifications()" class="space-y-6">
                            <div>
                                <h3 class="font-medium text-neutral-900 mb-4">이메일 알림</h3>
                                <div class="space-y-4">
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">댓글 알림</span>
                                            <p class="text-sm text-neutral-500">내 글에 댓글이 달리면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_on_comment" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">답글 알림</span>
                                            <p class="text-sm text-neutral-500">내 댓글에 답글이 달리면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_on_reply" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">팔로우 알림</span>
                                            <p class="text-sm text-neutral-500">새로운 팔로워가 생기면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_on_follow" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">좋아요 알림</span>
                                            <p class="text-sm text-neutral-500">내 글이 좋아요를 받으면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_on_like" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-neutral-200">
                                <h3 class="font-medium text-neutral-900 mb-4">푸시 알림</h3>
                                <div class="space-y-4">
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">푸시 알림 활성화</span>
                                            <p class="text-sm text-neutral-500">브라우저 푸시 알림을 받습니다</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.push_enabled" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                </div>
                            </div>

                            <div class="pt-6 mt-6 border-t border-neutral-200">
                                <button type="submit" class="btn-primary" :disabled="saving">
                                    <span x-show="!saving">변경사항 저장</span>
                                    <span x-show="saving">저장 중...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function settingsPage() {
        return {
            isAuthenticated: false,
            loading: true,
            saving: false,
            tab: 'profile',
            successMessage: '',
            errorMessage: '',
            errors: {},

            profile: {
                name: '',
                bio: '',
                avatar: null
            },

            currentEmail: '',

            notifications: {
                email_on_comment: true,
                email_on_reply: true,
                email_on_follow: true,
                email_on_like: false,
                push_enabled: false
            },

            showDeleteConfirm: false,
            deleteConfirmation: '',
            deleting: false,

            // Social Account Linking
            socialAccounts: { github: null },
            socialLoading: false,

            async init() {
                this.isAuthenticated = window.auth?.isAuthenticated() ?? false;

                if (!this.isAuthenticated) {
                    this.loading = false;
                    return;
                }

                await this.fetchSettings();

                if (this.isAuthenticated) {
                    await this.fetchSocialAccounts();
                }
            },

            async fetchSettings() {
                this.loading = true;
                try {
                    // Get current user info from auth
                    const currentUser = window.auth?.getUser();
                    if (currentUser) {
                        this.profile.name = currentUser.name || '';
                        this.profile.bio = currentUser.bio || '';
                        this.profile.avatar = currentUser.avatar || currentUser.avatar_url || null;
                        this.currentEmail = currentUser.email || '';
                    }

                    // Fetch notification settings
                    const notifResponse = await window.auth.fetch('/api/settings/notifications');

                    if (notifResponse.ok) {
                        const notifData = await notifResponse.json();
                        if (notifData.data) {
                            this.notifications = { ...this.notifications, ...notifData.data };
                        }
                    }
                } catch (error) {
                    console.error('Failed to fetch settings:', error);
                    this.showError('설정을 불러오는데 실패했습니다.');
                } finally {
                    this.loading = false;
                }
            },

            async updateProfile() {
                this.saving = true;
                this.clearMessages();
                this.errors = {};

                try {
                    const response = await window.auth.fetch('/api/users/me', {
                        method: 'PUT',
                        body: JSON.stringify({
                            name: this.profile.name,
                            bio: this.profile.bio
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Update local auth user data
                        if (window.auth && data.data) {
                            const currentUser = window.auth.getUser();
                            if (currentUser) {
                                currentUser.name = data.data.name;
                                currentUser.bio = data.data.bio;
                                localStorage.setItem('user', JSON.stringify(currentUser));
                            }
                        }
                        this.showSuccess('프로필이 업데이트되었습니다.');
                    } else if (response.status === 422) {
                        this.errors = data.errors || {};
                        this.showError('입력값을 확인해주세요.');
                    } else {
                        this.showError(data.message || '프로필 업데이트에 실패했습니다.');
                    }
                } catch (error) {
                    console.error('Failed to update profile:', error);
                    this.showError('프로필 업데이트에 실패했습니다.');
                } finally {
                    this.saving = false;
                }
            },

            async updateNotifications() {
                this.saving = true;
                this.clearMessages();

                try {
                    const response = await window.auth.fetch('/api/settings/notifications', {
                        method: 'PUT',
                        body: JSON.stringify(this.notifications)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showSuccess('알림 설정이 저장되었습니다.');
                    } else {
                        this.showError(data.message || '알림 설정 저장에 실패했습니다.');
                    }
                } catch (error) {
                    console.error('Failed to update notifications:', error);
                    this.showError('알림 설정 저장에 실패했습니다.');
                } finally {
                    this.saving = false;
                }
            },

            async deleteAccount() {
                if (this.deleteConfirmation !== '삭제합니다') return;

                this.deleting = true;
                this.clearMessages();

                try {
                    const response = await window.auth.fetch('/api/settings/account', {
                        method: 'DELETE',
                        body: JSON.stringify({
                            confirmation: this.deleteConfirmation
                        })
                    });

                    if (response.ok) {
                        // Clear auth and redirect
                        if (window.auth) {
                            window.auth.logout();
                        }
                        window.location.href = '/';
                    } else {
                        const data = await response.json();
                        this.showError(data.message || '계정 삭제에 실패했습니다.');
                        this.showDeleteConfirm = false;
                        this.deleteConfirmation = '';
                    }
                } catch (error) {
                    console.error('Failed to delete account:', error);
                    this.showError('계정 삭제에 실패했습니다.');
                } finally {
                    this.deleting = false;
                }
            },

            showSuccess(message) {
                this.successMessage = message;
                setTimeout(() => { this.successMessage = ''; }, 5000);
            },

            showError(message) {
                this.errorMessage = message;
                setTimeout(() => { this.errorMessage = ''; }, 5000);
            },

            clearMessages() {
                this.successMessage = '';
                this.errorMessage = '';
            },

            // Social Account Linking Methods
            async fetchSocialAccounts() {
                try {
                    const response = await window.auth.fetch('/api/auth/social-accounts');
                    if (response.ok) {
                        const data = await response.json();
                        this.socialAccounts = data.data || { github: null };
                    }
                } catch (error) {
                    console.error('Failed to fetch social accounts:', error);
                }
            },

            linkSocialAccount(provider) {
                window.location.href = `/api/auth/oauth/${provider}/redirect`;
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
