<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-ink-800 border border-gray-300 dark:border-ink-600 rounded-md font-semibold text-xs text-ink-700 dark:text-ink-100 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-ink-700 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 dark:focus:ring-offset-ink-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
