@if (session('status'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="print-hidden fixed top-4 right-4 z-50 flex items-start gap-3 max-w-sm rounded-lg border border-gray-100 dark:border-ink-700 bg-white dark:bg-ink-800 px-4 py-3 shadow-lg"
        role="status"
    >
        <svg class="h-5 w-5 shrink-0 text-green-500 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M9 12l2 2 4-4" />
        </svg>
        <p class="text-sm text-gray-700 dark:text-gray-200">{{ session('status') }}</p>
        <button type="button" @click="show = false" class="ml-auto shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <span class="sr-only">{{ __('閉じる') }}</span>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>
@endif
