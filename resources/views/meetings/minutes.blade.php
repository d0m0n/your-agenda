<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}の議事録</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('議事録を作成') }}: {{ $meeting->name }}
            </h2>
            <a href="{{ route('meetings.show', $meeting) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                {{ __('次第に戻る') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('skippedAttachments') && count(session('skippedAttachments')) > 0)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 rounded-lg p-4">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
                        {{ __('以下の添付は自動読み込みできませんでした') }}
                    </p>
                    <ul class="text-xs text-amber-700 dark:text-amber-400 space-y-1 list-disc list-inside">
                        @foreach (session('skippedAttachments') as $item)
                            <li>{{ $item['title'] }} — {{ $item['reason'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('文字起こしから議事録を生成') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('会議の文字起こしテキストを貼り付けると、次第・議案ファイル・資料の内容とあわせてAIが議事録のドラフトを作成します(プラスプラン限定機能)。') }}
                </p>

                <form method="POST" action="{{ route('meetings.minutes.generate', $meeting) }}" class="space-y-3"
                    x-data="{ generating: false }" @submit="generating = true">
                    @csrf
                    {{-- textareaはdisabledにしない: disabled要素はフォーム送信時に除外されるため、
                         送信中にdisabledへ切り替えるとtranscriptが空のままPOSTされてしまう。
                         readonlyなら見た目の編集不可はそのままにpost内容は保持される。 --}}
                    <textarea name="transcript" rows="16" x-bind:readonly="generating"
                        placeholder="{{ __('文字起こしのテキストをここに貼り付けてください') }}"
                        class="block w-full text-sm font-mono border-paper-200 dark:border-ink-600 dark:bg-ink-900 dark:text-paper-100 rounded-md shadow-sm">{{ old('transcript') ?? $meeting->minutes_transcript }}</textarea>
                    <x-input-error :messages="$errors->get('transcript')" class="mt-2" />

                    <x-primary-button x-bind:disabled="generating">
                        <span x-show="!generating">{{ __('議事録を生成する') }}</span>
                        <span x-show="generating" x-cloak>{{ __('生成中…(1分ほどかかる場合があります)') }}</span>
                    </x-primary-button>
                </form>
            </div>

            @if ($meeting->minutes_body)
                <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('生成された議事録(編集できます)') }}</h3>
                        <a href="{{ route('meetings.minutes.pdf', $meeting) }}" target="_blank" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                            {{ __('印刷 / PDF保存') }}
                        </a>
                    </div>
                    @if ($meeting->minutes_generated_at)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('生成日時') }}: {{ $meeting->minutes_generated_at->jst()->format('Y-m-d H:i') }}
                        </p>
                    @endif

                    <form method="POST" action="{{ route('meetings.minutes.update', $meeting) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <textarea name="body" rows="20"
                            class="block w-full text-sm font-mono border-paper-200 dark:border-ink-600 dark:bg-ink-900 dark:text-paper-100 rounded-md shadow-sm">{{ old('body') ?? $meeting->minutes_body }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />

                        <x-primary-button>{{ __('保存') }}</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
