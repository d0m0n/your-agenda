@if (($trialDaysRemaining ?? null) !== null && $trialDaysRemaining <= 3)
    <a href="{{ route('billing.paywall') }}"
        class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium
            {{ $trialDaysRemaining <= 0
                ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                : 'bg-brass-100 text-brass-700 dark:bg-brass-600/25 dark:text-brass-300' }}"
        title="{{ __('無料トライアル期間') }}">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3.5 2" />
        </svg>
        @if ($trialDaysRemaining <= 0)
            {{ __('トライアル終了') }}
        @else
            {{ __('トライアル残り') }}{{ $trialDaysRemaining }}{{ __('日') }}
        @endif
    </a>
@endif
