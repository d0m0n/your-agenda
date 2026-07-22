@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-leather-400 text-start text-base font-medium text-leather-600 dark:text-leather-200 bg-leather-50 dark:bg-ink-700/50 focus:outline-none focus:text-leather-600 dark:focus:text-leather-200 focus:bg-leather-100 dark:focus:bg-ink-700 focus:border-leather-500 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-gray-50 dark:hover:bg-ink-700 hover:border-gray-300 dark:hover:border-ink-600 focus:outline-none focus:text-ink-800 dark:focus:text-paper-200 focus:bg-gray-50 dark:focus:bg-ink-700 focus:border-gray-300 dark:focus:border-ink-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
