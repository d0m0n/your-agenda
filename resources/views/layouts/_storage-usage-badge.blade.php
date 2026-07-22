@if (($storageUsagePercent ?? 0) >= 80)
    <a href="{{ route('settings.edit') }}"
        class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium
            {{ $storageUsagePercent >= 100
                ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                : 'bg-brass-100 text-brass-700 dark:bg-brass-600/25 dark:text-brass-300' }}"
        title="{{ __('データ容量: 使用率') }} {{ $storageUsagePercent }}%">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 9v4M12 16.5h.01" />
            <path d="M10.3 3.9L2.7 17.5a1.6 1.6 0 001.4 2.4h15.8a1.6 1.6 0 001.4-2.4L13.7 3.9a1.6 1.6 0 00-2.8 0z" />
        </svg>
        {{ __('容量') }} {{ $storageUsagePercent }}%
    </a>
@endif
