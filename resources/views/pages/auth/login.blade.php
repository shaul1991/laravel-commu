<x-layouts.app>
    <x-slot:title>로그인</x-slot:title>

    <div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12">
        <div class="w-full max-w-md">
            {{-- Logo & Title --}}
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center gap-2 mb-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-neutral-900">Community</span>
                </a>
                <h1 class="text-2xl font-bold text-neutral-900">로그인</h1>
                <p class="mt-2 text-sm text-neutral-600">소셜 계정으로 간편하게 로그인하세요</p>
            </div>

            {{-- Login Card --}}
            <div class="card p-8" x-data="{ sessionExpired: new URLSearchParams(window.location.search).has('session_expired'), error: new URLSearchParams(window.location.search).get('error') }">
                {{-- Session Expired Message --}}
                <div x-show="sessionExpired" x-cloak class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-amber-700">세션이 만료되었습니다. 다시 로그인해 주세요.</p>
                    </div>
                </div>

                {{-- OAuth Error Message --}}
                <div x-show="error" x-cloak class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-red-700" x-text="error === 'account_deleted' ? '탈퇴한 계정입니다. 재가입은 고객센터로 문의해주세요.' : (error === 'oauth_failed' ? '소셜 로그인에 실패했습니다. 다시 시도해주세요.' : '로그인에 실패했습니다.')"></p>
                    </div>
                </div>

                {{-- Social Login Buttons --}}
                <div class="space-y-3">
                    <a href="/api/auth/oauth/github/redirect" class="flex items-center justify-center gap-3 w-full py-3 px-4 bg-neutral-900 text-white rounded-lg hover:bg-neutral-800 font-medium transition-colors">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub로 계속하기
                    </a>
                </div>

                {{-- Info Text --}}
                <p class="mt-6 text-center text-xs text-neutral-500">
                    로그인 시 처음 방문하시는 경우 자동으로 계정이 생성됩니다.
                </p>
            </div>

            {{-- Back to Home --}}
            <p class="mt-6 text-center text-sm text-neutral-600">
                <a href="/" class="font-medium text-primary-600 hover:text-primary-700">홈으로 돌아가기</a>
            </p>
        </div>
    </div>
</x-layouts.app>
