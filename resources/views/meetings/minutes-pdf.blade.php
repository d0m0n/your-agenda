<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}の議事録</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('議事録') }}: {{ $meeting->name }}
            </h2>
            <div class="print-hidden flex items-center gap-4 text-sm">
                <button type="button" onclick="window.print()" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('印刷 / PDF保存') }}
                </button>
                <a href="{{ route('meetings.minutes.edit', $meeting) }}" class="text-gray-500 dark:text-gray-400 hover:underline">
                    {{ __('編集に戻る') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 print:max-w-none print:mx-0 print:px-0">
            <div class="print-sheet bg-paper-50 dark:bg-ink-800 shadow-md sm:rounded-lg overflow-hidden border border-paper-200 dark:border-ink-700 print:rounded-none px-8 py-10 sm:px-12">
                <h1 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ $meeting->name }} 議事録</h1>
                @if ($meeting->minutes_generated_at)
                    <p class="mt-1 text-xs text-ink-500 dark:text-paper-100/60">
                        {{ __('生成日時') }}: {{ $meeting->minutes_generated_at->jst()->format('Y-m-d H:i') }}
                    </p>
                @endif

                <div class="mt-6 text-sm leading-loose text-ink-800 dark:text-paper-100 font-serif whitespace-pre-wrap">{{ $meeting->minutes_body }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
