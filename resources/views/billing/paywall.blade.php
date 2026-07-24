@php
    $isGeneral = Auth::user()->isGeneral();
    $trialExpired = $organization && ! $organization->onGenericTrial() && ! $organization->subscribed('default');
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('お支払い情報の登録') }}</x-slot>

    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('お支払い情報の登録') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-8 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brass-100 dark:bg-brass-600/25">
                    <svg class="h-6 w-6 text-brass-600 dark:text-brass-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="6" width="18" height="13" rx="2" />
                        <path d="M3 10h18" />
                    </svg>
                </div>

                <h3 class="mt-4 font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">
                    @if ($trialExpired)
                        {{ __('無料お試し期間(14日間)が終了しました') }}
                    @else
                        {{ __('ご利用の継続にはお支払い手続きが必要です') }}
                    @endif
                </h3>

                <p class="mt-2 text-sm text-ink-600 dark:text-paper-100/70">
                    {{ __('引き続きご利用いただくには、月額料金のお支払い情報をご登録ください。') }}
                </p>

                <p class="mt-6 font-serif text-3xl font-bold text-ink-800 dark:text-paper-100">
                    &yen;{{ number_format(config('billing.monthly_price_yen')) }}
                    <span class="text-base font-normal text-ink-500 dark:text-paper-100/60">/ {{ __('月(税込)') }}</span>
                </p>

                @if ($isGeneral)
                    <form method="POST" action="{{ route('billing.checkout') }}" class="mt-8">
                        @csrf
                        <x-primary-button class="w-full justify-center py-2.5">
                            {{ __('お支払い情報を登録して利用を再開する') }}
                        </x-primary-button>
                    </form>
                    <p class="mt-4 text-xs text-ink-400 dark:text-paper-100/40">
                        {{ __('クレジットカード情報は決済代行会社(Stripe)のページで入力し、当サービスのサーバーには保存されません。') }}
                    </p>
                @else
                    <div class="mt-8 px-4 py-3 rounded-md bg-paper-100 dark:bg-ink-900/50 text-sm text-ink-600 dark:text-paper-100/70">
                        {{ __('お支払い手続きは、貴組織の一般ユーザーの方にご依頼ください。') }}
                    </div>
                @endif

                @can('manage')
                    <p class="mt-6 text-xs">
                        <a href="{{ route('settings.export') }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                            {{ __('これまでの次第データを一括ダウンロードする') }}
                        </a>
                    </p>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
