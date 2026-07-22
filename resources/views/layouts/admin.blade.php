<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('layouts.theme-init')

        <title>{{ __('管理者パネル') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-toast />

        <div class="min-h-screen bg-paper-100 dark:bg-night">
            <nav class="bg-ink-900 border-b border-ink-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-14 items-center">
                        <div class="flex items-center gap-8">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-serif font-semibold text-paper-100">
                                <x-brand-mark class="h-6 w-6 text-brass-300" />
                                {{ __('あなた次第 管理者パネル') }}
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-paper-100/70 hover:text-paper-100">{{ __('組織一覧') }}</a>
                        </div>
                        <div class="flex items-center gap-4">
                            <x-theme-toggle />
                            <span class="text-sm text-paper-100/70">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-paper-100/70 hover:text-paper-100">{{ __('ログアウト') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white dark:bg-ink-800 shadow">
                    <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
