{{--
    Share Buttons Component - 소셜 공유 버튼

    @props([
        'url' => '',
        'title' => '',
        'layout' => 'horizontal', // horizontal | vertical
    ])

    Usage:
    <x-ui.share-buttons
        url="https://example.com/articles/my-article"
        title="My Article Title"
        layout="vertical"
    />
--}}

@props([
    'url' => '',
    'title' => '',
    'layout' => 'horizontal',
])

@php
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
    $containerClass = $layout === 'vertical' ? 'flex flex-col gap-2' : 'flex gap-2';
@endphp

<div
    {{ $attributes->merge(['class' => $containerClass]) }}
    x-data="{ copied: false }"
>
    {{-- X (Twitter) --}}
    <a
        href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ $encodedTitle }}"
        target="_blank"
        rel="noopener noreferrer"
        class="flex h-10 w-10 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-600 transition-all hover:border-neutral-900 hover:bg-neutral-900 hover:text-white hover:shadow-sm"
        title="X에 공유"
    >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
        </svg>
    </a>

    {{-- Facebook --}}
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="flex h-10 w-10 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-600 transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-sm"
        title="Facebook에 공유"
    >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
        </svg>
    </a>

    {{-- LinkedIn --}}
    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ $encodedUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="flex h-10 w-10 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-600 transition-all hover:border-blue-700 hover:bg-blue-700 hover:text-white hover:shadow-sm"
        title="LinkedIn에 공유"
    >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
        </svg>
    </a>

    {{-- Copy Link --}}
    <button
        @click="
            navigator.clipboard.writeText('{{ $url }}');
            copied = true;
            setTimeout(() => copied = false, 2000);
        "
        class="flex h-10 w-10 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-600 transition-all hover:border-primary-300 hover:text-primary-600 hover:shadow-sm"
        :class="{ 'border-green-500 text-green-600': copied }"
        :title="copied ? '복사됨!' : '링크 복사'"
        aria-label="링크 복사"
    >
        <template x-if="!copied">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
        </template>
        <template x-if="copied">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </template>
    </button>
</div>
