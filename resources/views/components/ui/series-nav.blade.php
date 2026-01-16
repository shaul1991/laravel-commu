{{--
    Series Navigation Component - 시리즈 네비게이션

    @props([
        'seriesTitle' => '',
        'seriesUrl' => '#',
        'current' => 1,
        'total' => 1,
        'prevUrl' => null,
        'prevTitle' => null,
        'nextUrl' => null,
        'nextTitle' => null,
    ])

    Usage:
    <x-ui.series-nav
        seriesTitle="Laravel 12 마스터하기"
        seriesUrl="/series/laravel-12"
        :current="2"
        :total="5"
        prevUrl="/articles/laravel-12-part-1"
        prevTitle="Part 1: 시작하기"
        nextUrl="/articles/laravel-12-part-3"
        nextTitle="Part 3: 라우팅"
    />
--}}

@props([
    'seriesTitle' => '',
    'seriesUrl' => '#',
    'current' => 1,
    'total' => 1,
    'prevUrl' => null,
    'prevTitle' => null,
    'nextUrl' => null,
    'nextTitle' => null,
])

<div {{ $attributes->merge(['class' => 'my-8 rounded-xl border border-neutral-200 bg-neutral-50 p-4 lg:p-6']) }}>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Series Info --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-neutral-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                시리즈
            </span>
            <a href="{{ $seriesUrl }}" class="font-semibold text-neutral-900 hover:text-primary-600 transition-colors">
                {{ $seriesTitle }}
            </a>
            <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-700">
                {{ $current }} / {{ $total }}
            </span>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
            @if($prevUrl)
                <a
                    href="{{ $prevUrl }}"
                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-neutral-600 transition-colors hover:bg-neutral-200"
                    title="{{ $prevTitle }}"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    이전
                </a>
            @else
                <span class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-neutral-400 cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    이전
                </span>
            @endif

            @if($nextUrl)
                <a
                    href="{{ $nextUrl }}"
                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-neutral-600 transition-colors hover:bg-neutral-200"
                    title="{{ $nextTitle }}"
                >
                    다음
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-neutral-400 cursor-not-allowed">
                    다음
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </div>
</div>
