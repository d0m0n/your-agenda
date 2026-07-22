<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ $meeting->name }}
            </h2>
            @can('manage')
                <a href="{{ route('meetings.edit', $meeting) }}" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('編集') }}
                </a>
            @endcan
        </div>
    </x-slot>

    @if ($meeting->headerImageUrl())
        <x-slot name="headerImage">
            <img src="{{ $meeting->headerImageUrl() }}" alt="" class="w-full max-h-64 object-cover">
        </x-slot>
    @endif

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- 紙の次第を1枚のシートに見立て、日時・次第・Wi-Fi/メモを
                 別々のカードに分けず、区切り線だけで続けて表示する。 --}}
            <div class="bg-paper-50 dark:bg-ink-800 shadow-md sm:rounded-lg overflow-hidden border border-paper-200 dark:border-ink-700">

                {{-- 表題部: 会議名・開催日時・開催場所 --}}
                <div class="px-6 pt-8 pb-6 sm:px-10 border-b border-dashed border-paper-200 dark:border-ink-600">
                    <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">
                        {{ $meeting->organization->name }}
                    </p>
                    <h1 class="mt-1 font-serif text-2xl font-semibold text-ink-800 dark:text-paper-100">
                        {{ $meeting->name }} {{ __('次第') }}
                    </h1>
                    <dl class="mt-5 flex flex-wrap gap-x-10 gap-y-2 text-sm">
                        <div class="flex items-baseline gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('開催日時') }}</dt>
                            <dd class="font-medium text-ink-800 dark:text-paper-100">{{ $meeting->scheduleLabel() ?? '-' }}</dd>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('開催場所') }}</dt>
                            <dd class="font-medium text-ink-800 dark:text-paper-100">{{ $meeting->location ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- 次第本体 --}}
                <div class="px-6 py-6 sm:px-10">
                    <ol class="space-y-4">
                        @forelse ($meeting->topLevelAgendaItems as $index => $item)
                            <li class="border-b border-paper-200 dark:border-ink-700 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm font-medium text-ink-800 dark:text-paper-100">
                                        <span class="font-serif text-leather-500 dark:text-leather-300">{{ $index + 1 }}.</span>
                                        @if ($item->linkUrl())
                                            <a href="{{ $item->linkUrl() }}" target="_blank" rel="noopener noreferrer" class="text-leather-500 dark:text-leather-300 hover:underline">{{ $item->title }}</a>
                                        @else
                                            {{ $item->title }}
                                        @endif
                                    </p>
                                    @if ($item->assigneeLabel())
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-right shrink-0">{{ $item->assigneeLabel() }}</p>
                                    @endif
                                </div>

                                @if ($item->children->isNotEmpty())
                                    <ol class="mt-3 ml-6 pl-4 border-l-2 border-paper-200 dark:border-ink-700 space-y-2">
                                        @foreach ($item->children as $childIndex => $child)
                                            <li class="flex items-center justify-between gap-4">
                                                <p class="text-sm text-ink-700 dark:text-paper-200">
                                                    <span class="font-serif text-leather-500 dark:text-leather-300">{{ sprintf('%02d', $childIndex + 1) }}.</span>
                                                    @if ($child->linkUrl())
                                                        <a href="{{ $child->linkUrl() }}" target="_blank" rel="noopener noreferrer" class="text-leather-500 dark:text-leather-300 hover:underline">{{ $child->title }}</a>
                                                    @else
                                                        {{ $child->title }}
                                                    @endif
                                                </p>
                                                @if ($child->assigneeLabel())
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 text-right shrink-0">{{ $child->assigneeLabel() }}</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('次第はまだ登録されていません。') }}</p>
                        @endforelse
                    </ol>
                </div>

                {{-- 付記: Wi-Fi情報・メモ --}}
                @if ($meeting->wifi_ssid || $meeting->wifi_password || $meeting->memo)
                    <div class="border-t border-dashed border-paper-200 dark:border-ink-600 bg-paper-200/40 dark:bg-ink-900/30 px-6 py-6 sm:px-10 space-y-5">
                        @if ($meeting->wifi_ssid || $meeting->wifi_password)
                            <div>
                                <h3 class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase mb-3">{{ __('Wi-Fi情報') }}</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Wi-Fi SSID') }}</dt>
                                        <dd class="text-ink-800 dark:text-paper-100">{{ $meeting->wifi_ssid ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Wi-Fi パスワード') }}</dt>
                                        <dd class="text-ink-800 dark:text-paper-100">{{ $meeting->wifi_password ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endif

                        @if ($meeting->memo)
                            <div>
                                <h3 class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase mb-3">{{ __('メモ') }}</h3>
                                <p class="text-sm text-ink-800 dark:text-paper-100 whitespace-pre-wrap">{{ $meeting->memo }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
