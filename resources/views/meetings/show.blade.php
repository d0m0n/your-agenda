<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $meeting->name }}
            </h2>
            @can('manage')
                <a href="{{ route('meetings.edit', $meeting) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('編集') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($meeting->headerImageUrl())
                <img src="{{ $meeting->headerImageUrl() }}" alt="" class="w-full max-h-64 object-cover rounded-lg shadow-sm">
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('開催日時') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $meeting->scheduleLabel() ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('開催場所') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $meeting->location ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('次第') }}</h3>
                <ol class="space-y-3">
                    @forelse ($meeting->topLevelAgendaItems as $index => $item)
                        <li class="border-b border-gray-100 dark:border-gray-700 pb-3 last:border-b-0 last:pb-0">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $index + 1 }}.
                                    @if ($item->linkUrl())
                                        <a href="{{ $item->linkUrl() }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->title }}</a>
                                    @else
                                        {{ $item->title }}
                                    @endif
                                </p>
                                @if ($item->assigneeLabel())
                                    <p class="text-xs text-gray-500 dark:text-gray-400 text-right shrink-0">{{ $item->assigneeLabel() }}</p>
                                @endif
                            </div>

                            @if ($item->children->isNotEmpty())
                                <ol class="mt-2 ml-6 pl-4 border-l-2 border-gray-200 dark:border-gray-700 space-y-2">
                                    @foreach ($item->children as $childIndex => $child)
                                        <li class="flex items-center justify-between gap-4">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ sprintf('%02d', $childIndex + 1) }}.
                                                @if ($child->linkUrl())
                                                    <a href="{{ $child->linkUrl() }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $child->title }}</a>
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

            @if ($meeting->wifi_ssid || $meeting->wifi_password || $meeting->memo)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">
                    @if ($meeting->wifi_ssid || $meeting->wifi_password)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Wi-Fi情報') }}</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Wi-Fi SSID') }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $meeting->wifi_ssid ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Wi-Fi パスワード') }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $meeting->wifi_password ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    @if ($meeting->memo)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('メモ') }}</h3>
                            <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $meeting->memo }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
