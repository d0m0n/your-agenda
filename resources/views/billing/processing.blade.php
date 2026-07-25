<x-app-layout>
    <x-slot name="title">{{ __('お支払いを確認しています') }}</x-slot>

    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('お支払いを確認しています') }}
        </h2>
    </x-slot>

    <div class="py-12"
        x-data="{
            checking: true,
            attempts: 0,
            maxAttempts: 20,
            async poll() {
                this.attempts++;
                try {
                    const response = await fetch('{{ route('billing.status') }}', { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    if (data.active) {
                        window.location.href = '{{ $redirectUrl }}';
                        return;
                    }
                } catch (e) {}
                if (this.attempts >= this.maxAttempts) {
                    this.checking = false;
                    return;
                }
                setTimeout(() => this.poll(), 1500);
            },
        }"
        x-init="poll()">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-8 text-center">
                <div x-show="checking">
                    <svg class="mx-auto h-10 w-10 animate-spin text-leather-500 dark:text-leather-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-90" d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>

                    <h3 class="mt-4 font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">
                        {{ __('お支払いを確認しています…') }}
                    </h3>
                    <p class="mt-2 text-sm text-ink-600 dark:text-paper-100/70">
                        {{ __('お支払いは正常に処理されています。反映まで今しばらくお待ちください。') }}
                    </p>
                </div>

                <div x-show="! checking" x-cloak>
                    <h3 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">
                        {{ __('反映に時間がかかっています') }}
                    </h3>
                    <p class="mt-2 text-sm text-ink-600 dark:text-paper-100/70">
                        {{ __('お支払い自体は完了しています。少し時間をおいてから、もう一度お試しください。') }}
                    </p>
                    <a href="{{ $redirectUrl }}" class="mt-6 inline-flex items-center px-6 py-2.5 rounded-md bg-leather-500 hover:bg-leather-600 text-white font-semibold shadow-md transition-colors">
                        {{ __('もう一度確認する') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
