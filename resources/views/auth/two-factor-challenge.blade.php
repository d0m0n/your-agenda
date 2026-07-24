<x-guest-layout>
    <div x-data="{ useRecoveryCode: false }">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-semibold text-ink-800 dark:text-paper-100">{{ __('二段階認証') }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-show="! useRecoveryCode">
                {{ __('認証アプリに表示されている6桁のコードを入力してください。') }}
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-show="useRecoveryCode" x-cloak>
                {{ __('発行済みのリカバリーコードを1つ入力してください。') }}
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5">
            @csrf

            <div x-show="! useRecoveryCode">
                <x-input-label for="code" :value="__('6桁のコード')" />
                <x-text-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" name="code" autofocus />
            </div>

            <div x-show="useRecoveryCode" x-cloak>
                <x-input-label for="recovery_code" :value="__('リカバリーコード')" />
                <x-text-input id="recovery_code" class="block mt-1 w-full" type="text" autocomplete="off" name="recovery_code" />
            </div>

            <x-input-error :messages="$errors->get('code')" class="mt-2" />

            <x-primary-button class="w-full justify-center py-2.5">
                {{ __('ログイン') }}
            </x-primary-button>

            <button type="button" class="text-sm text-leather-500 dark:text-leather-300 hover:underline" x-on:click="useRecoveryCode = ! useRecoveryCode">
                <span x-show="! useRecoveryCode">{{ __('リカバリーコードを使う') }}</span>
                <span x-show="useRecoveryCode" x-cloak>{{ __('認証アプリのコードを使う') }}</span>
            </button>
        </form>
    </div>
</x-guest-layout>
