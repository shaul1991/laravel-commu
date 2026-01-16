{{--
    Table of Contents Component - 목차

    @props([
        'items' => [], // [{id, title, level, children: [{id, title}]}]
    ])

    Usage:
    <x-ui.toc :items="[
        ['id' => 'introduction', 'title' => '소개', 'children' => []],
        ['id' => 'installation', 'title' => '설치', 'children' => [
            ['id' => 'requirements', 'title' => '요구 사항'],
            ['id' => 'setup', 'title' => '설정'],
        ]],
    ]" />
--}}

@props([
    'items' => [],
])

{{-- Desktop TOC (Sidebar) --}}
<nav
    {{ $attributes->merge(['class' => 'sticky top-24 hidden lg:block']) }}
    aria-label="Table of Contents"
    x-data="tocScroll()"
>
    <div class="rounded-xl border border-neutral-200 bg-white p-4">
        <h2 class="mb-4 text-sm font-semibold text-neutral-900">목차</h2>
        <ul class="space-y-2 text-sm">
            @foreach($items as $item)
                <li>
                    <a
                        href="#{{ $item['id'] }}"
                        class="block text-neutral-600 transition-colors hover:text-primary-600"
                        :class="{ 'font-medium text-primary-600': activeId === '{{ $item['id'] }}' }"
                        @click.prevent="scrollTo('{{ $item['id'] }}')"
                    >
                        {{ $item['title'] }}
                    </a>
                    @if(isset($item['children']) && count($item['children']) > 0)
                        <ul class="ml-3 mt-2 space-y-1 border-l border-neutral-200 pl-3">
                            @foreach($item['children'] as $child)
                                <li>
                                    <a
                                        href="#{{ $child['id'] }}"
                                        class="block text-neutral-500 transition-colors hover:text-primary-600"
                                        :class="{ 'font-medium text-primary-600': activeId === '{{ $child['id'] }}' }"
                                        @click.prevent="scrollTo('{{ $child['id'] }}')"
                                    >
                                        {{ $child['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</nav>

{{-- Mobile TOC (Floating Button + Bottom Sheet) --}}
<div
    class="lg:hidden"
    x-data="{ tocOpen: false }"
>
    {{-- Floating Button --}}
    <button
        @click="tocOpen = true"
        class="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg transition-transform hover:scale-105 active:scale-95"
        aria-label="목차 열기"
        aria-expanded="false"
        :aria-expanded="tocOpen"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
    </button>

    {{-- Bottom Sheet --}}
    <div
        x-show="tocOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50"
        @keydown.escape.window="tocOpen = false"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/50"
            @click="tocOpen = false"
        ></div>

        {{-- Content --}}
        <div
            x-show="tocOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="absolute bottom-0 left-0 right-0 max-h-[70vh] overflow-y-auto rounded-t-2xl bg-white p-6"
        >
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-neutral-900">목차</h2>
                <button
                    @click="tocOpen = false"
                    class="rounded-lg p-2 text-neutral-500 hover:bg-neutral-100"
                    aria-label="닫기"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <ul class="space-y-3 text-base">
                @foreach($items as $item)
                    <li>
                        <a
                            href="#{{ $item['id'] }}"
                            class="block py-1 text-neutral-700 hover:text-primary-600"
                            @click="tocOpen = false"
                        >
                            {{ $item['title'] }}
                        </a>
                        @if(isset($item['children']) && count($item['children']) > 0)
                            <ul class="ml-4 mt-2 space-y-2 border-l border-neutral-200 pl-4">
                                @foreach($item['children'] as $child)
                                    <li>
                                        <a
                                            href="#{{ $child['id'] }}"
                                            class="block py-0.5 text-sm text-neutral-500 hover:text-primary-600"
                                            @click="tocOpen = false"
                                        >
                                            {{ $child['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
    function tocScroll() {
        return {
            activeId: '',
            init() {
                this.observeHeadings();
            },
            scrollTo(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                    this.activeId = id;
                }
            },
            observeHeadings() {
                const headings = document.querySelectorAll('article h2[id], article h3[id]');
                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                this.activeId = entry.target.id;
                            }
                        });
                    },
                    { rootMargin: '-20% 0px -80% 0px' }
                );
                headings.forEach((heading) => observer.observe(heading));
            },
        };
    }
</script>
@endPushOnce
