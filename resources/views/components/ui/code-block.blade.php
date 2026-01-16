{{--
    Code Block Component - 코드 블록 (복사 기능 포함)

    @props([
        'language' => 'text',
        'filename' => null,
    ])

    Usage:
    <x-ui.code-block language="php" filename="app/Models/User.php">
        class User extends Authenticatable
        {
            // ...
        }
    </x-ui.code-block>
--}}

@props([
    'language' => 'text',
    'filename' => null,
])

<div
    {{ $attributes->merge(['class' => 'group relative my-6 overflow-hidden rounded-xl bg-neutral-900']) }}
    x-data="{ copied: false }"
>
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-neutral-700 px-4 py-2">
        <div class="flex items-center gap-3">
            {{-- Language Badge --}}
            <span class="text-xs font-medium text-neutral-400">{{ $language }}</span>
            {{-- Filename --}}
            @if($filename)
                <span class="text-xs text-neutral-500">{{ $filename }}</span>
            @endif
        </div>
        {{-- Copy Button --}}
        <button
            @click="
                const code = $el.closest('[x-data]').querySelector('code').innerText;
                navigator.clipboard.writeText(code);
                copied = true;
                setTimeout(() => copied = false, 2000);
            "
            class="rounded p-1.5 text-neutral-400 transition-all hover:bg-neutral-700 hover:text-white opacity-0 group-hover:opacity-100"
            :class="{ 'text-green-400 hover:text-green-400': copied }"
            :title="copied ? '복사됨!' : '코드 복사'"
            aria-label="코드 복사"
        >
            <template x-if="!copied">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </template>
            <template x-if="copied">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </template>
        </button>
    </div>
    {{-- Code --}}
    <pre class="overflow-x-auto p-4 text-sm font-mono leading-relaxed text-neutral-100"><code>{{ $slot }}</code></pre>
</div>
