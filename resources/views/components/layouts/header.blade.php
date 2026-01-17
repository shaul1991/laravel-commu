{{-- Header Component - PC/Mobile Responsive --}}
<header class="sticky top-0 z-40 border-b border-neutral-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
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

                @auth
                    {{-- Write Button --}}
                    <a href="{{ route('articles.write') }}" class="btn-primary text-sm">
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
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-sm font-bold text-white">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1), 'UTF-8') }}
                                </div>
                            @endif
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
                            class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl border border-neutral-200 bg-white py-1 shadow-lg"
                        >
                            <div class="border-b border-neutral-100 px-4 py-3">
                                <p class="font-semibold text-neutral-900">{{ auth()->user()->name }}</p>
                                <p class="text-sm text-neutral-500">{{ '@' . auth()->user()->username }}</p>
                            </div>
                            <a href="{{ route('profile.show', auth()->user()->username) }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">내 프로필</a>
                            <a href="{{ route('me.articles') }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">내 글</a>
                            <a href="{{ route('settings') }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">설정</a>
                            <div class="border-t border-neutral-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">로그아웃</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Auth Buttons --}}
                    <a href="{{ route('login') }}" class="btn-ghost text-sm">로그인</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm">회원가입</a>
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-neutral-600 transition-colors hover:bg-neutral-100 hover:text-neutral-900 lg:hidden"
                aria-label="Open menu"
                aria-expanded="false"
                aria-controls="mobile-menu"
                onclick="const menu = document.getElementById('mobile-menu'); const isHidden = menu.classList.toggle('hidden'); this.setAttribute('aria-expanded', String(!isHidden));"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden border-t border-neutral-200 py-4 lg:hidden">
            <nav class="flex flex-col gap-2">
                <a href="/" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-900 transition-colors hover:bg-neutral-100">Home</a>
                <a href="#" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100">Discussions</a>
                <a href="#" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100">Categories</a>
                <a href="#" class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100">Members</a>
            </nav>
            <div class="mt-4 flex flex-col gap-2 border-t border-neutral-200 pt-4">
                <input
                    type="search"
                    placeholder="Search..."
                    class="input"
                >
                <a href="#" class="btn-outline w-full justify-center text-sm">Sign In</a>
                <a href="#" class="btn-primary w-full justify-center text-sm">Sign Up</a>
            </div>
        </div>
    </div>
</header>
