{{--
    Article Card Component - 기술 블로그용 아티클 카드

    @props([
        'href' => '#',
        'thumbnail' => null,
        'category' => null,
        'categoryColor' => 'primary',
        'title' => '',
        'excerpt' => '',
        'author' => null,
        'authorAvatar' => null,
        'date' => null,
        'readTime' => null,
        'tags' => [],
        'featured' => false,
    ])

    Usage:
    <x-ui.article-card
        href="/posts/1"
        thumbnail="/images/post.jpg"
        category="Backend"
        title="Laravel 12의 새로운 기능들"
        excerpt="Laravel 12에서 추가된 주요 기능들을 살펴봅니다..."
        author="John Doe"
        date="2026-01-15"
        readTime="5"
        :tags="['laravel', 'php']"
    />
--}}

@props([
    'href' => '#',
    'thumbnail' => null,
    'category' => null,
    'categoryColor' => 'primary',
    'title' => '',
    'excerpt' => '',
    'author' => null,
    'authorAvatar' => null,
    'date' => null,
    'readTime' => null,
    'tags' => [],
    'featured' => false,
])

@php
    $categoryColors = [
        'backend' => 'bg-green-100 text-green-800',
        'frontend' => 'bg-blue-100 text-blue-800',
        'devops' => 'bg-orange-100 text-orange-800',
        'ai' => 'bg-purple-100 text-purple-800',
        'database' => 'bg-red-100 text-red-800',
        'default' => 'bg-primary-100 text-primary-800',
    ];
    $categoryClass = $categoryColors[strtolower($category ?? '')] ?? $categoryColors['default'];
@endphp

<article {{ $attributes->merge(['class' => 'card group overflow-hidden transition-all duration-300 hover:shadow-lg']) }}>
    <a href="{{ $href }}" class="block">
        {{-- Thumbnail --}}
        @if($thumbnail || $featured)
        <div class="relative aspect-video overflow-hidden bg-neutral-100">
            @if($thumbnail)
                <img
                    src="{{ $thumbnail }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                >
            @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-500 to-secondary-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            @endif

            {{-- Category Badge on Image --}}
            @if($category)
            <div class="absolute left-4 top-4">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $categoryClass }}">
                    {{ $category }}
                </span>
            </div>
            @endif
        </div>
        @endif

        <div class="p-5">
            {{-- Category (if no thumbnail) --}}
            @if($category && !$thumbnail && !$featured)
            <div class="mb-2">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $categoryClass }}">
                    {{ $category }}
                </span>
            </div>
            @endif

            {{-- Title --}}
            <h3 class="text-lg font-bold text-neutral-900 line-clamp-2 group-hover:text-primary-600 transition-colors {{ $featured ? 'text-xl' : '' }}">
                {{ $title }}
            </h3>

            {{-- Excerpt --}}
            @if($excerpt)
            <p class="mt-2 text-sm text-neutral-600 line-clamp-2">
                {{ $excerpt }}
            </p>
            @endif

            {{-- Tags --}}
            @if(count($tags) > 0)
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach(array_slice($tags, 0, 3) as $tag)
                <span class="text-xs text-neutral-500 before:content-['#']">{{ $tag }}</span>
                @endforeach
            </div>
            @endif

            {{-- Meta Info --}}
            <div class="mt-4 flex items-center justify-between border-t border-neutral-100 pt-4">
                <div class="flex items-center gap-2">
                    @if($authorAvatar)
                        <img src="{{ $authorAvatar }}" alt="{{ $author }}" class="h-6 w-6 rounded-full object-cover">
                    @elseif($author)
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-neutral-200 text-xs font-medium text-neutral-600">
                            {{ strtoupper(substr($author, 0, 1)) }}
                        </div>
                    @endif
                    @if($author)
                        <span class="text-sm font-medium text-neutral-700">{{ $author }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-neutral-500">
                    @if($date)
                    <span>{{ $date }}</span>
                    @endif
                    @if($readTime)
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $readTime }}분
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </a>
</article>
