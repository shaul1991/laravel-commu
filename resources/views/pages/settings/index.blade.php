<x-layouts.app>
    <x-slot:title>설정</x-slot:title>

    <div class="py-8" x-data="settingsPage()" x-init="init()">
        <div class="max-w-4xl mx-auto">
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
                        <li>
                            <button
                                @click="tab = 'security'"
                                :class="tab === 'security' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:bg-neutral-50'"
                                class="w-full text-left px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                보안
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
                                        <button type="button" class="btn-outline text-sm">사진 변경</button>
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

                            {{-- Username --}}
                            <div>
                                <label for="username" class="block text-sm font-medium text-neutral-700 mb-1.5">사용자명</label>
                                <div class="relative max-w-md">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">@</span>
                                    <input type="text" id="username" x-model="profile.username" class="input pl-8" required>
                                </div>
                                <p class="mt-1 text-xs text-neutral-500">영문, 숫자, 밑줄(_)만 사용 가능합니다</p>
                                <template x-if="errors.username">
                                    <p class="mt-1 text-sm text-red-600" x-text="errors.username[0]"></p>
                                </template>
                            </div>

                            {{-- Bio --}}
                            <div>
                                <label for="bio" class="block text-sm font-medium text-neutral-700 mb-1.5">소개</label>
                                <textarea id="bio" x-model="profile.bio" rows="3" class="input resize-none" maxlength="200"></textarea>
                                <p class="mt-1 text-xs text-neutral-500"><span x-text="(profile.bio || '').length"></span>/200</p>
                            </div>

                            {{-- Location --}}
                            <div>
                                <label for="location" class="block text-sm font-medium text-neutral-700 mb-1.5">위치</label>
                                <input type="text" id="location" x-model="profile.location" class="input max-w-md" placeholder="예: 서울, 대한민국">
                            </div>

                            {{-- Website --}}
                            <div>
                                <label for="website" class="block text-sm font-medium text-neutral-700 mb-1.5">웹사이트</label>
                                <input type="url" id="website" x-model="profile.website" class="input max-w-md" placeholder="https://example.com">
                            </div>

                            {{-- GitHub --}}
                            <div>
                                <label for="github" class="block text-sm font-medium text-neutral-700 mb-1.5">GitHub</label>
                                <div class="relative max-w-md">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">github.com/</span>
                                    <input type="text" id="github" x-model="profile.github" class="input pl-28">
                                </div>
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

                        <form @submit.prevent="updateEmail()" class="space-y-6">
                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">이메일</label>
                                <input type="email" id="email" x-model="account.email" class="input max-w-md" required>
                                <p class="mt-1 text-xs text-neutral-500">이메일 변경 시 확인 메일이 발송됩니다</p>
                                <template x-if="errors.email">
                                    <p class="mt-1 text-sm text-red-600" x-text="errors.email[0]"></p>
                                </template>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary" :disabled="saving">
                                    <span x-show="!saving">변경사항 저장</span>
                                    <span x-show="saving">저장 중...</span>
                                </button>
                            </div>
                        </form>

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
                                    <label class="block text-sm font-medium text-neutral-700 mb-1.5">확인을 위해 비밀번호를 입력하세요</label>
                                    <input type="password" x-model="deletePassword" class="input" placeholder="비밀번호">
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="button" @click="showDeleteConfirm = false" class="btn-outline">취소</button>
                                    <button
                                        type="button"
                                        @click="deleteAccount()"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                                        :disabled="!deletePassword || deleting"
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
                                        <input type="checkbox" x-model="notifications.email_comments" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">팔로우 알림</span>
                                            <p class="text-sm text-neutral-500">새로운 팔로워가 생기면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_follows" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">좋아요 알림</span>
                                            <p class="text-sm text-neutral-500">내 글이 좋아요를 받으면 알림</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_likes" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                    </label>
                                    <label class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-neutral-700">뉴스레터</span>
                                            <p class="text-sm text-neutral-500">주간 인기 글 요약 및 소식</p>
                                        </div>
                                        <input type="checkbox" x-model="notifications.email_newsletter" class="h-5 w-5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
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

                    {{-- Security Tab --}}
                    <div x-show="tab === 'security'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">보안 설정</h2>

                        <form @submit.prevent="updatePassword()" class="space-y-6">
                            {{-- Change Password --}}
                            <div>
                                <h3 class="font-medium text-neutral-900 mb-4">비밀번호 변경</h3>
                                <div class="space-y-4 max-w-md">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-neutral-700 mb-1.5">현재 비밀번호</label>
                                        <input type="password" id="current_password" x-model="password.current" class="input" required>
                                        <template x-if="errors.current_password">
                                            <p class="mt-1 text-sm text-red-600" x-text="errors.current_password[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-neutral-700 mb-1.5">새 비밀번호</label>
                                        <input type="password" id="new_password" x-model="password.new" class="input" required>
                                        <p class="mt-1 text-xs text-neutral-500">8자 이상, 영문/숫자/특수문자 포함</p>
                                        <template x-if="errors.password">
                                            <p class="mt-1 text-sm text-red-600" x-text="errors.password[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-neutral-700 mb-1.5">새 비밀번호 확인</label>
                                        <input type="password" id="confirm_password" x-model="password.confirm" class="input" required>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary" :disabled="saving">
                                    <span x-show="!saving">비밀번호 변경</span>
                                    <span x-show="saving">변경 중...</span>
                                </button>
                            </div>
                        </form>

                        {{-- Sessions --}}
                        <div class="mt-8 pt-6 border-t border-neutral-200">
                            <h3 class="font-medium text-neutral-900 mb-4">활성 세션</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-6 w-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-neutral-900">현재 세션</span>
                                            <p class="text-sm text-neutral-500">현재 브라우저</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-green-600 font-medium">현재</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
function settingsPage() {
    return {
        loading: true,
        saving: false,
        tab: 'profile',
        successMessage: '',
        errorMessage: '',
        errors: {},

        profile: {
            name: '',
            username: '',
            bio: '',
            location: '',
            website: '',
            github: '',
            avatar: null
        },

        account: {
            email: ''
        },

        notifications: {
            email_comments: true,
            email_follows: true,
            email_likes: false,
            email_newsletter: true
        },

        password: {
            current: '',
            new: '',
            confirm: ''
        },

        showDeleteConfirm: false,
        deletePassword: '',
        deleting: false,

        async init() {
            await this.fetchSettings();
        },

        async fetchSettings() {
            this.loading = true;
            try {
                // Fetch profile
                const profileResponse = await fetch('/api/settings/profile', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin'
                });

                if (profileResponse.ok) {
                    const profileData = await profileResponse.json();
                    this.profile = { ...this.profile, ...profileData.data };
                    this.account.email = profileData.data.email || '';
                }

                // Fetch notification settings
                const notifResponse = await fetch('/api/settings/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin'
                });

                if (notifResponse.ok) {
                    const notifData = await notifResponse.json();
                    this.notifications = { ...this.notifications, ...notifData.data };
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
                const response = await fetch('/api/settings/profile', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name: this.profile.name,
                        username: this.profile.username,
                        bio: this.profile.bio,
                        location: this.profile.location,
                        website: this.profile.website,
                        github: this.profile.github
                    })
                });

                const data = await response.json();

                if (response.ok) {
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

        async updateEmail() {
            this.saving = true;
            this.clearMessages();
            this.errors = {};

            try {
                const response = await fetch('/api/settings/email', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        email: this.account.email
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess('이메일이 업데이트되었습니다.');
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                    this.showError('입력값을 확인해주세요.');
                } else {
                    this.showError(data.message || '이메일 업데이트에 실패했습니다.');
                }
            } catch (error) {
                console.error('Failed to update email:', error);
                this.showError('이메일 업데이트에 실패했습니다.');
            } finally {
                this.saving = false;
            }
        },

        async updateNotifications() {
            this.saving = true;
            this.clearMessages();

            try {
                const response = await fetch('/api/settings/notifications', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.notifications)
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess('알림 설정이 업데이트되었습니다.');
                } else {
                    this.showError(data.message || '알림 설정 업데이트에 실패했습니다.');
                }
            } catch (error) {
                console.error('Failed to update notifications:', error);
                this.showError('알림 설정 업데이트에 실패했습니다.');
            } finally {
                this.saving = false;
            }
        },

        async updatePassword() {
            if (this.password.new !== this.password.confirm) {
                this.showError('새 비밀번호가 일치하지 않습니다.');
                return;
            }

            this.saving = true;
            this.clearMessages();
            this.errors = {};

            try {
                const response = await fetch('/api/settings/password', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        current_password: this.password.current,
                        password: this.password.new,
                        password_confirmation: this.password.confirm
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess('비밀번호가 변경되었습니다.');
                    this.password = { current: '', new: '', confirm: '' };
                } else if (response.status === 422) {
                    this.errors = data.errors || {};
                    this.showError('입력값을 확인해주세요.');
                } else {
                    this.showError(data.message || '비밀번호 변경에 실패했습니다.');
                }
            } catch (error) {
                console.error('Failed to update password:', error);
                this.showError('비밀번호 변경에 실패했습니다.');
            } finally {
                this.saving = false;
            }
        },

        async deleteAccount() {
            if (!this.deletePassword) return;

            this.deleting = true;
            this.clearMessages();

            try {
                const response = await fetch('/api/settings/account', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        password: this.deletePassword
                    })
                });

                if (response.ok) {
                    window.location.href = '/';
                } else {
                    const data = await response.json();
                    this.showError(data.message || '계정 삭제에 실패했습니다.');
                    this.showDeleteConfirm = false;
                    this.deletePassword = '';
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
        }
    };
}
</script>
