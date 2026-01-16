<x-layouts.app>
    <x-slot:title>설정</x-slot:title>

    <div class="py-8" x-data="{
        tab: 'profile',
        name: '김개발',
        username: 'devkim',
        email: 'devkim@example.com',
        bio: '5년차 백엔드 개발자입니다. Laravel과 PHP를 주로 다루며, 깔끔한 코드와 좋은 설계에 관심이 많습니다.',
        location: '서울, 대한민국',
        website: 'https://devkim.dev',
        github: 'devkim',
        notifications: {
            email_comments: true,
            email_follows: true,
            email_likes: false,
            email_newsletter: true,
        }
    }">
        <div class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-neutral-900">설정</h1>
                <p class="mt-1 text-sm text-neutral-600">계정 정보와 환경설정을 관리하세요</p>
            </div>

            <div class="flex gap-8">
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
                    {{-- Profile Tab --}}
                    <div x-show="tab === 'profile'" class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">프로필 정보</h2>

                        <form class="space-y-6">
                            {{-- Avatar --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">프로필 사진</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-2xl font-bold">
                                        김
                                    </div>
                                    <div>
                                        <button type="button" class="btn-outline text-sm">사진 변경</button>
                                        <p class="mt-1 text-xs text-neutral-500">JPG, PNG 형식. 최대 2MB</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-neutral-700 mb-1.5">이름</label>
                                <input type="text" id="name" x-model="name" class="input max-w-md">
                            </div>

                            {{-- Username --}}
                            <div>
                                <label for="username" class="block text-sm font-medium text-neutral-700 mb-1.5">사용자명</label>
                                <div class="relative max-w-md">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">@</span>
                                    <input type="text" id="username" x-model="username" class="input pl-8">
                                </div>
                                <p class="mt-1 text-xs text-neutral-500">영문, 숫자, 밑줄(_)만 사용 가능합니다</p>
                            </div>

                            {{-- Bio --}}
                            <div>
                                <label for="bio" class="block text-sm font-medium text-neutral-700 mb-1.5">소개</label>
                                <textarea id="bio" x-model="bio" rows="3" class="input resize-none" maxlength="200"></textarea>
                                <p class="mt-1 text-xs text-neutral-500"><span x-text="bio.length"></span>/200</p>
                            </div>

                            {{-- Location --}}
                            <div>
                                <label for="location" class="block text-sm font-medium text-neutral-700 mb-1.5">위치</label>
                                <input type="text" id="location" x-model="location" class="input max-w-md" placeholder="예: 서울, 대한민국">
                            </div>

                            {{-- Website --}}
                            <div>
                                <label for="website" class="block text-sm font-medium text-neutral-700 mb-1.5">웹사이트</label>
                                <input type="url" id="website" x-model="website" class="input max-w-md" placeholder="https://example.com">
                            </div>

                            {{-- GitHub --}}
                            <div>
                                <label for="github" class="block text-sm font-medium text-neutral-700 mb-1.5">GitHub</label>
                                <div class="relative max-w-md">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">github.com/</span>
                                    <input type="text" id="github" x-model="github" class="input pl-28">
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary">변경사항 저장</button>
                            </div>
                        </form>
                    </div>

                    {{-- Account Tab --}}
                    <div x-show="tab === 'account'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">계정 정보</h2>

                        <form class="space-y-6">
                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">이메일</label>
                                <input type="email" id="email" x-model="email" class="input max-w-md">
                                <p class="mt-1 text-xs text-neutral-500">이메일 변경 시 확인 메일이 발송됩니다</p>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary">변경사항 저장</button>
                            </div>
                        </form>

                        {{-- Danger Zone --}}
                        <div class="mt-12 pt-6 border-t border-neutral-200">
                            <h3 class="text-lg font-bold text-red-600 mb-4">위험 구역</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                                    <div>
                                        <h4 class="font-medium text-neutral-900">계정 비활성화</h4>
                                        <p class="text-sm text-neutral-600">계정을 일시적으로 비활성화합니다. 언제든 다시 활성화할 수 있습니다.</p>
                                    </div>
                                    <button type="button" class="btn-outline text-red-600 border-red-200 hover:bg-red-100">비활성화</button>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                                    <div>
                                        <h4 class="font-medium text-neutral-900">계정 삭제</h4>
                                        <p class="text-sm text-neutral-600">계정과 모든 데이터가 영구적으로 삭제됩니다. 이 작업은 되돌릴 수 없습니다.</p>
                                    </div>
                                    <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">계정 삭제</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notifications Tab --}}
                    <div x-show="tab === 'notifications'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">알림 설정</h2>

                        <div class="space-y-6">
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
                        </div>

                        <div class="pt-6 mt-6 border-t border-neutral-200">
                            <button type="submit" class="btn-primary">변경사항 저장</button>
                        </div>
                    </div>

                    {{-- Security Tab --}}
                    <div x-show="tab === 'security'" x-cloak class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">보안 설정</h2>

                        <form class="space-y-6">
                            {{-- Change Password --}}
                            <div>
                                <h3 class="font-medium text-neutral-900 mb-4">비밀번호 변경</h3>
                                <div class="space-y-4 max-w-md">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-neutral-700 mb-1.5">현재 비밀번호</label>
                                        <input type="password" id="current_password" class="input">
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-neutral-700 mb-1.5">새 비밀번호</label>
                                        <input type="password" id="new_password" class="input">
                                        <p class="mt-1 text-xs text-neutral-500">8자 이상, 영문/숫자/특수문자 포함</p>
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-neutral-700 mb-1.5">새 비밀번호 확인</label>
                                        <input type="password" id="confirm_password" class="input">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="btn-primary">비밀번호 변경</button>
                            </div>
                        </form>

                        {{-- Connected Accounts --}}
                        <div class="mt-12 pt-6 border-t border-neutral-200">
                            <h3 class="font-medium text-neutral-900 mb-4">연결된 계정</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium text-neutral-900">Google</span>
                                            <p class="text-sm text-neutral-500">devkim@gmail.com</p>
                                        </div>
                                    </div>
                                    <button type="button" class="text-sm text-red-600 hover:text-red-700 font-medium">연결 해제</button>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium text-neutral-900">GitHub</span>
                                            <p class="text-sm text-green-600">연결됨</p>
                                        </div>
                                    </div>
                                    <button type="button" class="text-sm text-red-600 hover:text-red-700 font-medium">연결 해제</button>
                                </div>
                            </div>
                        </div>

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
                                            <span class="font-medium text-neutral-900">Chrome on macOS</span>
                                            <p class="text-sm text-neutral-500">서울, 대한민국 · 현재 세션</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-green-600 font-medium">현재</span>
                                </div>
                                <div class="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-6 w-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-neutral-900">Safari on iPhone</span>
                                            <p class="text-sm text-neutral-500">서울, 대한민국 · 2일 전</p>
                                        </div>
                                    </div>
                                    <button type="button" class="text-sm text-red-600 hover:text-red-700 font-medium">종료</button>
                                </div>
                            </div>
                            <button type="button" class="mt-4 text-sm text-red-600 hover:text-red-700 font-medium">다른 모든 세션 종료</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
