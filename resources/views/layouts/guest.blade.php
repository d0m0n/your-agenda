<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('layouts.theme-init')

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2 bg-paper dark:bg-night">

            {{-- ブランドパネル: 「議題」が読み上げられ、最後に『あなた次第』へ収束する演出。
                 右端の点線は手帳の綴じ糸を思わせるステッチ。 --}}
            <div class="relative overflow-hidden bg-ink-900 px-6 py-10 sm:px-12 sm:py-14 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:border-r lg:border-dashed lg:border-brass-400/30">
                <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-brass-400/15 blur-3xl"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 -translate-x-1/3 translate-y-1/3 rounded-full bg-leather-400/15 blur-3xl"></div>

                <div class="relative mx-auto w-full max-w-md">
                    <a href="/" class="inline-flex items-center gap-2">
                        <x-brand-mark class="h-7 w-7 text-brass-300" />
                        <span class="text-sm font-medium tracking-[0.2em] text-paper-100/70 uppercase">Your agenda</span>
                    </a>

                    <ol class="mt-12 hidden space-y-3 border-l border-paper-100/15 pl-5 lg:block">
                        <li class="agenda-reveal-item flex items-baseline gap-3 text-paper-100/60" data-reveal-order="1">
                            <span class="font-serif text-xs text-brass-300/90">01</span>
                            <span class="text-sm">開会の挨拶</span>
                        </li>
                        <li class="agenda-reveal-item flex items-baseline gap-3 text-paper-100/60" data-reveal-order="2">
                            <span class="font-serif text-xs text-brass-300/90">02</span>
                            <span class="text-sm">議案協議</span>
                        </li>
                        <li class="agenda-reveal-item flex items-baseline gap-3 text-paper-100/60" data-reveal-order="3">
                            <span class="font-serif text-xs text-brass-300/90">03</span>
                            <span class="text-sm">その他連絡事項</span>
                        </li>
                    </ol>

                    <div class="agenda-reveal-item mt-10" data-reveal-order="4">
                        <p class="font-serif text-4xl font-semibold leading-tight text-paper-100 sm:text-5xl">
                            すべては、<br class="hidden sm:block">あなた次第。
                        </p>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-paper-100/60">
                            会議・次第・メンバー・資料を、ひとつの場所で。<br>
                            議題を決めるのも、進めるのも――あなた次第です。
                        </p>
                    </div>
                </div>
            </div>

            {{-- フォームパネル --}}
            <div class="flex items-center justify-center px-6 py-12 sm:px-12 lg:px-16">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
