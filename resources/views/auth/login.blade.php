<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-serif text-2xl font-semibold text-ink-800 dark:text-paper-100">{{ __('ログイン') }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('アカウント情報を入力してください。') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-ink-900 border-gray-300 dark:border-ink-600 text-leather-500 shadow-sm focus:ring-leather-400 dark:focus:ring-leather-400 dark:focus:ring-offset-ink-900" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('ログイン状態を保持する') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-leather-500 dark:text-leather-200 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-leather-400 dark:focus:ring-offset-ink-900" href="{{ route('password.request') }}">
                    {{ __('パスワードをお忘れですか?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            {{ __('ログイン') }}
        </x-primary-button>
    </form>
</x-guest-layout>
