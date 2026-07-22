@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-ink-600 dark:bg-ink-900 dark:text-ink-100 focus:border-leather-400 dark:focus:border-leather-400 focus:ring-leather-400 dark:focus:ring-leather-400 rounded-md shadow-sm']) }}>
