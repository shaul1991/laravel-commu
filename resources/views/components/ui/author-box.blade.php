{{--
    Author Box Component - 작성자 정보 박스

    @props([
        'name' => '',
        'avatar' => null,
        'bio' => '',
        'profileUrl' => '#',
        'socialLinks' => [], // [{platform: 'github'|'twitter'|'linkedin', url: '...'}]
    ])

    Usage:
    <x-ui.author-box
        name="김개발"
        avatar="/images/avatar.jpg"
        bio="10년차 백엔드 개발자입니다."
        profileUrl="/users/1"
        :socialLinks="[
            ['platform' => 'github', 'url' => 'https://github.com/...'],
            ['platform' => 'twitter', 'url' => 'https://twitter.com/...'],
        ]"
    />
--}}

@props([
    'name' => '',
    'avatar' => null,
    'bio' => '',
    'profileUrl' => '#',
    'socialLinks' => [],
])

<div {{ $attributes->merge(['class' => 'mt-12 rounded-2xl border border-neutral-200 bg-white p-6 lg:p-8']) }}>
    <div class="flex flex-col items-center text-center lg:flex-row lg:items-start lg:text-left">
        {{-- Avatar --}}
        <a href="{{ $profileUrl }}" class="flex-shrink-0">
            @if($avatar)
                <img
                    src="{{ $avatar }}"
                    alt="{{ $name }}"
                    class="h-20 w-20 rounded-full object-cover"
                >
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-2xl font-bold text-white">
                    {{ mb_strtoupper(mb_substr($name, 0, 1), 'UTF-8') }}
                </div>
            @endif
        </a>

        {{-- Info --}}
        <div class="mt-4 lg:ml-6 lg:mt-0">
            <a href="{{ $profileUrl }}" class="text-lg font-semibold text-neutral-900 hover:text-primary-600 transition-colors">
                {{ $name }}
            </a>
            @if($bio)
            <p class="mt-2 text-sm text-neutral-600 leading-relaxed">
                {{ $bio }}
            </p>
            @endif

            {{-- Social Links --}}
            @if(count($socialLinks) > 0)
            <div class="mt-4 flex justify-center gap-3 lg:justify-start">
                @foreach($socialLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-neutral-400 hover:text-neutral-600 transition-colors"
                        title="{{ ucfirst($link['platform']) }}"
                    >
                        @switch($link['platform'])
                            @case('github')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                </svg>
                                @break
                            @case('twitter')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                                @break
                            @case('linkedin')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                </svg>
                                @break
                            @default
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                        @endswitch
                    </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Follow Button --}}
        <div class="mt-4 lg:ml-auto lg:mt-0">
            <x-ui.button variant="outline" size="sm">
                팔로우
            </x-ui.button>
        </div>
    </div>
</div>
