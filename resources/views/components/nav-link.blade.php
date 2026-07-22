@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-leather-400 text-sm font-medium leading-5 text-ink-800 dark:text-paper-100 focus:outline-none focus:border-leather-500 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-ink-700 dark:hover:text-paper-200 hover:border-gray-300 dark:hover:border-ink-600 focus:outline-none focus:text-ink-700 dark:focus:text-paper-200 focus:border-gray-300 dark:focus:border-ink-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
