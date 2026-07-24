<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-serif text-2xl font-semibold text-ink-800 dark:text-paper-100">{{ __('リカバリーコード') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ __('認証アプリを利用できなくなった場合に、パスワードの代わりに1回だけ使えるコードです。安全な場所に保管してください。このコードは今しか表示されません。') }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-2 font-mono text-sm bg-paper-100 dark:bg-ink-900 rounded-lg p-4">
        @foreach ($codes as $code)
            <div class="text-ink-800 dark:text-paper-100">{{ $code }}</div>
        @endforeach
    </div>

    <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-brass-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-900 uppercase tracking-widest hover:bg-ink-700 dark:hover:bg-brass-300 transition">
        {{ __('保管しました。管理画面へ進む') }}
    </a>
</x-guest-layout>
