<x-layouts.app>
    <x-slot:title>비밀번호 찾기</x-slot:title>

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
                <h1 class="text-2xl font-bold text-neutral-900">비밀번호 찾기</h1>
                <p class="mt-2 text-sm text-neutral-600">가입한 이메일 주소를 입력하시면<br>비밀번호 재설정 링크를 보내드립니다</p>
            </div>

            {{-- Forgot Password Form --}}
            <div class="card p-8" x-data="{ submitted: false }">
                {{-- Form --}}
                <form x-show="!submitted" class="space-y-6" @submit.prevent="submitted = true">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">이메일</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="name@example.com"
                            class="input"
                            required
                        >
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        재설정 링크 보내기
                    </button>
                </form>

                {{-- Success Message --}}
                <div x-show="submitted" x-cloak class="text-center py-4">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 mb-2">이메일을 확인해주세요</h3>
                    <p class="text-sm text-neutral-600 mb-6">
                        비밀번호 재설정 링크가 포함된 이메일을<br>발송했습니다. 메일함을 확인해주세요.
                    </p>
                    <button @click="submitted = false" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        다른 이메일로 다시 시도
                    </button>
                </div>
            </div>

            {{-- Back to Login --}}
            <p class="mt-6 text-center text-sm text-neutral-600">
                <a href="/login" class="font-medium text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    로그인으로 돌아가기
                </a>
            </p>
        </div>
    </div>
</x-layouts.app>
