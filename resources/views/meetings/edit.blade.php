<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('会議編集') }}: {{ $meeting->name }}
            </h2>
            <a href="{{ route('meetings.show', $meeting) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                {{ __('会議画面を見る') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('会議情報') }}</h3>
                <form method="POST" action="{{ route('meetings.update', $meeting) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('meetings._form', ['meeting' => $meeting])

                    <div class="flex items-center justify-end gap-4">
                        <x-primary-button>{{ __('更新') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('議案Zipファイル') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('この会議専用にアップロードされます。他の会議の次第からは選択できません。') }}</p>

                @if ($sites->isNotEmpty())
                    <ul class="space-y-2 mb-4">
                        @foreach ($sites as $site)
                            <li class="flex items-center justify-between text-sm border-b border-gray-100 dark:border-gray-700 pb-2 last:border-b-0 last:pb-0">
                                <a href="{{ $site->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $site->title }}</a>
                                <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('{{ __('この議案Zipを削除しますか?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('削除') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('meetings.sites.store', $meeting) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    @csrf
                    <div>
                        <x-input-label for="site_title" :value="__('タイトル')" />
                        <x-text-input id="site_title" name="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="zip_file" :value="__('Zipファイル')" />
                        <input id="zip_file" name="zip_file" type="file" accept=".zip" required
                            class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                        <x-input-error :messages="$errors->get('zip_file')" class="mt-2" />
                    </div>
                    <div>
                        <x-primary-button>{{ __('アップロード') }}</x-primary-button>
                    </div>
                </form>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('gian.htm はZip直下、もしくは1階層下のフォルダに配置してください。') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('次第') }}</h3>

                <div class="space-y-3">
                    @forelse ($meeting->agendaItems as $index => $item)
                        <div x-data="{ editing: false }" class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                            <div x-show="!editing" class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $index + 1 }}. {{ $item->title }}</p>
                                    @if ($item->member || $item->site)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @if ($item->member)
                                                {{ __('担当者') }}: {{ $item->member->name }}
                                            @endif
                                            @if ($item->site)
                                                @if ($item->member)・@endif
                                                <a href="{{ $item->site->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('議案') }}: {{ $item->site->title }}</a>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <form method="POST" action="{{ route('agenda-items.move-up', [$meeting, $item]) }}">
                                        @csrf
                                        <button type="submit" @if($index === 0) disabled class="opacity-30" @endif class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&uarr;</button>
                                    </form>
                                    <form method="POST" action="{{ route('agenda-items.move-down', [$meeting, $item]) }}">
                                        @csrf
                                        <button type="submit" @if($index === $meeting->agendaItems->count() - 1) disabled class="opacity-30" @endif class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&darr;</button>
                                    </form>
                                    <button type="button" @click="editing = true" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('編集') }}</button>
                                    <form method="POST" action="{{ route('agenda-items.destroy', [$meeting, $item]) }}" onsubmit="return confirm('{{ __('この次第を削除しますか?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline">{{ __('削除') }}</button>
                                    </form>
                                </div>
                            </div>

                            <form x-show="editing" method="POST" action="{{ route('agenda-items.update', [$meeting, $item]) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                @csrf
                                @method('PUT')
                                <div class="sm:col-span-3">
                                    <x-input-label :value="__('議題')" />
                                    <x-text-input name="title" type="text" class="mt-1 block w-full" value="{{ $item->title }}" required />
                                </div>
                                <div>
                                    <x-input-label :value="__('担当者')" />
                                    <select name="member_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                                        <option value="">{{ __('未定') }}</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" @selected($item->member_id === $member->id)>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Zip議案')" />
                                    <select name="site_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                                        <option value="">{{ __('なし') }}</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}" @selected($item->site_id === $site->id)>{{ $site->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-3">
                                    <x-primary-button>{{ __('保存') }}</x-primary-button>
                                    <x-secondary-button type="button" @click="editing = false">{{ __('キャンセル') }}</x-secondary-button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('次第はまだ登録されていません。') }}</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('agenda-items.store', $meeting) }}" class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end border-t border-gray-200 dark:border-gray-700 pt-6">
                    @csrf
                    <div class="sm:col-span-3">
                        <x-input-label for="new_title" :value="__('議題を追加')" />
                        <x-text-input id="new_title" name="title" type="text" class="mt-1 block w-full" placeholder="{{ __('議題名') }}" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label :value="__('担当者')" />
                        <select name="member_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                            <option value="">{{ __('未定') }}</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label :value="__('Zip議案')" />
                        <select name="site_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                            <option value="">{{ __('なし') }}</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-primary-button>{{ __('追加') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
