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
                <p class="mt-2 text-sm text-neutral-600">계정에 로그인하여 커뮤니티에 참여하세요</p>
            </div>

            {{-- Login Form --}}
            <div class="card p-8" x-data="loginForm()">
                <form @submit.prevent="submit" class="space-y-6">
                    {{-- Session Expired Message --}}
                    <div x-show="sessionExpired" x-cloak class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-amber-700">세션이 만료되었습니다. 다시 로그인해 주세요.</p>
                        </div>
                    </div>

                    {{-- Error Message --}}
                    <div x-show="error" x-cloak class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-red-700" x-text="error"></p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">이메일</label>
                        <input
                            type="email"
                            id="email"
                            x-model="form.email"
                            placeholder="name@example.com"
                            class="input"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.email }"
                            required
                        >
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-500"></p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 mb-1.5">비밀번호</label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                x-model="form.password"
                                placeholder="••••••••"
                                class="input pr-10"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.password }"
                                required
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600"
                                tabindex="-1"
                            >
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-500"></p>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="remember"
                            x-model="form.remember"
                            class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                        >
                        <label for="remember" class="ml-2 block text-sm text-neutral-700">로그인 상태 유지</label>
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="btn-primary w-full justify-center py-2.5"
                        :disabled="loading"
                    >
                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? '로그인 중...' : '로그인'"></span>
                    </button>

                    {{-- Forgot Password --}}
                    <div class="text-center">
                        <a href="/forgot-password" class="text-sm text-primary-600 hover:text-primary-700">비밀번호를 잊으셨나요?</a>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-neutral-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-white px-4 text-neutral-500">또는</span>
                    </div>
                </div>

                {{-- Social Login --}}
                <div class="space-y-3">
                    <a href="/api/auth/oauth/google/redirect" class="btn-outline w-full justify-center py-2.5">
                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Google로 계속하기
                    </a>
                    <a href="/api/auth/oauth/github/redirect" class="btn-outline w-full justify-center py-2.5">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub로 계속하기
                    </a>
                </div>
            </div>

            {{-- Sign Up Link --}}
            <p class="mt-6 text-center text-sm text-neutral-600">
                계정이 없으신가요?
                <a href="/register" class="font-medium text-primary-600 hover:text-primary-700">회원가입</a>
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        function loginForm() {
            return {
                form: {
                    email: '',
                    password: '',
                    remember: false,
                },
                showPassword: false,
                loading: false,
                error: null,
                errors: {},
                sessionExpired: new URLSearchParams(window.location.search).has('session_expired'),

                async submit() {
                    this.loading = true;
                    this.error = null;
                    this.errors = {};

                    try {
                        await window.auth.login(
                            this.form.email,
                            this.form.password,
                            this.form.remember
                        );

                        // Redirect to home or intended page (with open-redirect protection)
                        const urlRedirect = new URLSearchParams(window.location.search).get('redirect');
                        const sessionRedirect = sessionStorage.getItem('redirect_after_login');
                        sessionStorage.removeItem('redirect_after_login');

                        const safeRedirect = (url) => {
                            if (!url || typeof url !== 'string') return '/';
                            // Reject protocol-relative URLs (//example.com) and URLs with schemes
                            if (url.startsWith('//') || /^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(url)) return '/';
                            // Allow only relative paths starting with single '/'
                            if (url.startsWith('/') && !url.startsWith('//')) return url;
                            return '/';
                        };

                        window.location.href = safeRedirect(urlRedirect) !== '/'
                            ? safeRedirect(urlRedirect)
                            : safeRedirect(sessionRedirect);
                    } catch (e) {
                        if (e.errors) {
                            this.errors = {};
                            for (const [key, value] of Object.entries(e.errors)) {
                                this.errors[key] = Array.isArray(value) ? value[0] : value;
                            }
                        }
                        this.error = e.message || '로그인에 실패했습니다. 이메일과 비밀번호를 확인해주세요.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
