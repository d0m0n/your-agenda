<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('取扱説明書') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($manual === 'observer')
                @include('help._observer')
            @else
                @include('help._general')
            @endif
        </div>
    </div>
</x-app-layout>
