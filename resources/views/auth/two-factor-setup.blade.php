<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-serif text-2xl font-semibold text-ink-800 dark:text-paper-100">{{ __('二段階認証の設定') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ __('管理者アカウントを保護するため、初回ログイン時に認証アプリ(Google Authenticator等)の設定が必要です。以下のQRコードをアプリで読み取ってください。') }}
        </p>
    </div>

    <div class="flex justify-center bg-white p-4 rounded-lg border border-paper-200 dark:border-ink-700">
        <img src="{{ $qrCodeImage }}" alt="{{ __('二段階認証用QRコード') }}" class="h-48 w-48">
    </div>

    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
        {{ __('QRコードを読み取れない場合は、以下のキーを手動で入力してください。') }}
    </p>
    <p class="mt-1 font-mono text-sm text-ink-800 dark:text-paper-100 break-all bg-paper-100 dark:bg-ink-900 px-3 py-2 rounded-md">
        {{ $secret }}
    </p>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="code" :value="__('認証アプリに表示されている6桁のコード')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" name="code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            {{ __('設定を完了する') }}
        </x-primary-button>
    </form>
</x-guest-layout>
