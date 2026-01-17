{{-- Header Component - PC/Mobile Responsive with Client-side Auth --}}
<header
    class="sticky top-0 z-40 border-b border-neutral-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80"
    x-data="headerAuth()"
    x-init="init()"
>
    <div class="container-main">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo --}}
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-neutral-900">Community</span>
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden lg:flex lg:items-center lg:gap-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-neutral-600 transition-colors hover:text-primary-600">Home</a>
                    <a href="{{ route('articles.index') }}" class="text-sm font-medium text-neutral-600 transition-colors hover:text-primary-600">Articles</a>
                    <a href="{{ route('search') }}" class="text-sm font-medium text-neutral-600 transition-colors hover:text-primary-600">Search</a>
                </nav>
            </div>

            {{-- Desktop Actions --}}
            <div class="hidden items-center gap-4 lg:flex">
                {{-- Search --}}
                <form action="{{ route('search') }}" method="GET" class="relative">
                    <input
                        type="search"
                        name="q"
                        placeholder="검색..."
                        class="w-64 rounded-lg border border-neutral-200 bg-neutral-50 py-2 pl-10 pr-4 text-sm text-neutral-900 placeholder-neutral-400 transition-colors focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>

                {{-- Authenticated User Menu --}}
                <template x-if="isAuthenticated">
                    <div class="flex items-center gap-4">
                        {{-- Write Button --}}
                        <a href="{{ route('articles.create') }}" class="btn-primary text-sm">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            글쓰기
                        </a>

                        {{-- Notification Dropdown --}}
                        <x-ui.notification-dropdown />

                        {{-- User Menu --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 rounded-full p-1 hover:bg-neutral-100">
                                <template x-if="user && user.avatar_url">
                                    <img :src="user.avatar_url" :alt="user.name" class="h-8 w-8 rounded-full object-cover">
                                </template>
                                <template x-if="!user || !user.avatar_url">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-sm font-bold text-white">
                                        <span x-text="user ? user.name.charAt(0).toUpperCase() : '?'"></span>
                                    </div>
                                </template>
                            </button>
                            <div
                                x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                x-cloak
                                class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl border border-neutral-200 bg-white py-1 shadow-lg"
                            >
                                <div class="border-b border-neutral-100 px-4 py-3">
                                    <p class="font-semibold text-neutral-900" x-text="user ? user.name : ''"></p>
                                    <p class="text-sm text-neutral-500" x-text="user ? '@' + user.username : ''"></p>
                                </div>
                                <a :href="'/@' + (user ? user.username : '')" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">내 프로필</a>
                                <a href="{{ route('me.articles') }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">내 글</a>
                                <a href="{{ route('settings') }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">설정</a>
                                <div class="border-t border-neutral-100 mt-1 pt-1">
                                    <button @click="logout()" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">로그아웃</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Guest Buttons --}}
                <template x-if="!isAuthenticated">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="btn-ghost text-sm">로그인</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">회원가입</a>
                    </div>
                </template>
            </div>

            {{-- Mobile Menu Button --}}
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-neutral-600 transition-colors hover:bg-neutral-100 hover:text-neutral-900 lg:hidden"
                aria-label="Open menu"
                aria-expanded="false"
                aria-controls="mobile-menu"
                @click="mobileMenuOpen = !mobileMenuOpen"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            x-cloak
            class="border-t border-neutral-200 py-4 lg:hidden"
        >
            <nav class="flex flex-col gap-2">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-900 transition-colors hover:bg-neutral-100">Home</a>
                <a href="{{ route('articles.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100">Articles</a>
                <a href="{{ route('search') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100">Search</a>
            </nav>

            <div class="mt-4 flex flex-col gap-2 border-t border-neutral-200 pt-4">
                <form action="{{ route('search') }}" method="GET">
                    <input
                        type="search"
                        name="q"
                        placeholder="검색..."
                        class="input"
                    >
                </form>

                {{-- Authenticated Mobile Menu --}}
                <template x-if="isAuthenticated">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('articles.create') }}" class="btn-primary w-full justify-center text-sm">글쓰기</a>
                        <a :href="'/@' + (user ? user.username : '')" class="btn-outline w-full justify-center text-sm">내 프로필</a>
                        <button @click="logout()" class="btn-ghost w-full justify-center text-sm text-red-600">로그아웃</button>
                    </div>
                </template>

                {{-- Guest Mobile Menu --}}
                <template x-if="!isAuthenticated">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('login') }}" class="btn-outline w-full justify-center text-sm">로그인</a>
                        <a href="{{ route('register') }}" class="btn-primary w-full justify-center text-sm">회원가입</a>
                    </div>
                </template>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function headerAuth() {
        return {
            isAuthenticated: false,
            user: null,
            mobileMenuOpen: false,

            init() {
                this.checkAuth();
            },

            checkAuth() {
                this.isAuthenticated = window.auth && window.auth.isAuthenticated();
                this.user = window.auth ? window.auth.getUser() : null;
            },

            async logout() {
                if (window.auth) {
                    await window.auth.logout();
                }
                window.location.href = '/';
            }
        }
    }
</script>
@endpush
