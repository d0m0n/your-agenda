<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        @include('layouts.theme-init')
        @include('layouts._favicon')

        <title>{{ __('ご利用いただけません') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-paper-100 dark:bg-night text-ink-800 dark:text-paper-100">
        <header class="border-b border-paper-200 dark:border-ink-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center">
                <a href="{{ url('/lp') }}" class="flex items-center gap-2">
                    <x-brand-mark class="h-6 w-6 text-leather-400" />
                    <span class="font-serif font-semibold text-sm text-ink-800 dark:text-paper-100">あなた次第</span>
                </a>
            </div>
        </header>

        <div class="py-20">
            <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">
                    {{ __('このページは現在ご利用いただけません') }}
                </h1>
                <p class="mt-3 text-sm text-ink-600 dark:text-paper-100/70">
                    {{ __('このリンクの発行元組織のお支払い状況により、現在このページはご覧いただけません。詳しくは発行元の組織にお問い合わせください。') }}
                </p>
            </div>
        </div>
    </body>
</html>
