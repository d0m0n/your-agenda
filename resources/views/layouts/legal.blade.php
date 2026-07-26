<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('layouts.theme-init')
        @include('layouts._favicon')

        <title>{{ $title }} | {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-paper-100 dark:bg-night text-ink-800 dark:text-paper-100">

        <header class="sticky top-0 z-40 bg-paper-100/90 dark:bg-night/90 backdrop-blur border-b border-paper-200 dark:border-ink-700">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <a href="{{ url('/lp') }}" class="flex items-center gap-2">
                    <x-brand-mark class="h-6 w-6 text-leather-400 shrink-0" />
                    <span class="font-serif font-semibold text-base text-ink-800 dark:text-paper-100">あなた(の)次第</span>
                </a>
                <x-theme-toggle />
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-ink-800 dark:text-paper-100">
                {{ $title }}
            </h1>

            <div class="legal-content mt-8">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-paper-200 dark:border-ink-700">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 flex flex-col items-center gap-3">
                <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-ink-400 dark:text-paper-100/60">
                    <a href="{{ route('legal.tokushoho') }}" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">特定商取引法に基づく表記</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">利用規約</a>
                    <a href="{{ route('legal.privacy') }}" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">プライバシーポリシー</a>
                </nav>
                <p class="text-xs text-ink-400 dark:text-paper-100/40">&copy; {{ date('Y') }} あなた(の)次第</p>
            </div>
        </footer>
    </body>
</html>
