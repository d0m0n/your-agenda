<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-brass-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-900 uppercase tracking-widest hover:bg-ink-700 dark:hover:bg-brass-300 focus:bg-ink-700 dark:focus:bg-brass-300 active:bg-ink-900 dark:active:bg-brass-500 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 dark:focus:ring-offset-ink-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
