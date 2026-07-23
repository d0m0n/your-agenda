<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('次第編集') }}: {{ $meeting->name }}
            </h2>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('meetings.edit', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('会議情報を編集') }}
                </a>
                <a href="{{ route('meetings.invitation.edit', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('案内文作成') }}
                </a>
                <a href="{{ route('meetings.show', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('会議画面を見る') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('議案ファイル') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('この会議専用にアップロードされます。他の会議の次第からは選択できません。') }}</p>

                @if ($sites->isNotEmpty())
                    <ul class="space-y-2 mb-4">
                        @foreach ($sites as $site)
                            <li class="border-b border-gray-100 dark:border-gray-700 pb-2 last:border-b-0 last:pb-0"
                                x-data="{ replacing: {{ old('site_id') == $site->id ? 'true' : 'false' }} }">
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <a href="{{ $site->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="text-leather-500 dark:text-leather-300 hover:underline">{{ $site->title }}</a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('アップロード') }}: {{ $site->created_at->format('Y-m-d H:i') }}
                                            @unless ($site->updated_at->equalTo($site->created_at))
                                                ・{{ __('差し替え') }}: {{ $site->updated_at->format('Y-m-d H:i') }}
                                            @endunless
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <button type="button" @click="replacing = !replacing" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">{{ __('差し替え') }}</button>
                                        <x-confirm-delete-button
                                            :id="'delete-site-'.$site->id"
                                            :action="route('sites.destroy', $site)"
                                            :message="__('この議案ファイルを削除しますか?')"
                                            class="text-xs">
                                            {{ __('削除') }}
                                        </x-confirm-delete-button>
                                    </div>
                                </div>

                                <form x-show="replacing" x-cloak method="POST" action="{{ route('meetings.sites.update', [$meeting, $site]) }}"
                                    enctype="multipart/form-data" class="mt-2 flex flex-wrap items-end gap-2"
                                    x-data="uploadProgress()" @submit.prevent="submitViaXhr">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                                    <div class="flex-1 min-w-[12rem]">
                                        <input type="file" name="zip_file" accept=".zip,.pdf,.jpg,.jpeg,.png,.gif,.webp" required
                                            class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                                        @if (old('site_id') == $site->id)
                                            <x-input-error :messages="$errors->get('zip_file')" class="mt-1" />
                                        @endif
                                        <x-upload-progress-bar />
                                    </div>
                                    <x-secondary-button type="submit" class="disabled:opacity-50 disabled:cursor-not-allowed shrink-0" x-bind:disabled="uploading">
                                        <span x-show="!uploading">{{ __('差し替える') }}</span>
                                        <span x-show="uploading" x-cloak>{{ __('アップロード中…') }}</span>
                                    </x-secondary-button>
                                </form>
                                <p x-show="replacing" x-cloak class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('タイトルはそのまま、ファイルの中身だけを差し替えます。次第からのリンクは変更されません。') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('meetings.sites.store', $meeting) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end"
                    x-data="uploadProgress()" @submit.prevent="submitViaXhr">
                    @csrf
                    <div>
                        <x-input-label for="site_title" :value="__('タイトル')" />
                        <x-text-input id="site_title" name="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="zip_file" :value="__('議案ファイル(Zip / PDF / 画像)')" />
                        <input id="zip_file" name="zip_file" type="file" accept=".zip,.pdf,.jpg,.jpeg,.png,.gif,.webp" required
                            class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                        <x-input-error :messages="$errors->get('zip_file')" class="mt-2" />
                        <x-upload-progress-bar />
                    </div>
                    <div>
                        <x-primary-button class="disabled:opacity-50 disabled:cursor-not-allowed" x-bind:disabled="uploading">
                            <span x-show="!uploading">{{ __('アップロード') }}</span>
                            <span x-show="uploading" x-cloak>{{ __('アップロード中…') }}</span>
                        </x-primary-button>
                    </div>
                </form>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Zipの場合はgian.htmをZip直下、もしくは1階層下のフォルダに配置してください。PDF・画像はファイル名を問わずそのままアップロードできます。') }}</p>
            </div>

            @if ($pastMeetings->isNotEmpty())
                <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('過去の次第からコピー') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('選択した項目(子項目も含む)をこの会議の次第の末尾に追加します。議案ファイルのリンクはコピーされません。') }}</p>

                    <form method="GET" action="{{ route('meetings.agenda', $meeting) }}" class="flex flex-wrap items-end gap-3 mb-4">
                        <div class="flex-1 min-w-[12rem]">
                            <x-input-label for="copy_from" :value="__('コピー元の会議')" />
                            <select id="copy_from" name="copy_from" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                                <option value="">{{ __('選択してください') }}</option>
                                @foreach ($pastMeetings as $pastMeeting)
                                    <option value="{{ $pastMeeting->id }}" @selected($copySourceMeeting?->id === $pastMeeting->id)>
                                        {{ $pastMeeting->name }}@if ($pastMeeting->held_at) ({{ $pastMeeting->heldAtDateLabel() }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-secondary-button>{{ __('次第を表示') }}</x-secondary-button>
                    </form>

                    @if ($copySourceMeeting)
                        @if ($copyCandidates->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('この会議には次第がありません。') }}</p>
                        @else
                            <form method="POST" action="{{ route('agenda-items.copy', $meeting) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="source_meeting_id" value="{{ $copySourceMeeting->id }}">
                                <ul class="space-y-2">
                                    @foreach ($copyCandidates as $item)
                                        <li class="text-sm">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="item_ids[]" value="{{ $item->id }}"
                                                    class="rounded border-gray-300 dark:border-gray-700 text-leather-500 focus:ring-leather-400">
                                                <span class="text-gray-900 dark:text-gray-100">{{ $item->title }}</span>
                                                @if ($item->children->isNotEmpty())
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ __('子項目:') }} {{ $item->children->count() }})</span>
                                                @endif
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                <x-input-error :messages="$errors->get('item_ids')" class="mt-2" />
                                <x-primary-button>{{ __('選択した項目をコピー') }}</x-primary-button>
                            </form>
                        @endif
                    @endif
                </div>
            @endif

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('次第') }}</h3>

                <div class="space-y-3">
                    @forelse ($meeting->topLevelAgendaItems as $item)
                        @include('meetings._agenda-item-row', [
                            'meeting' => $meeting, 'item' => $item, 'members' => $members, 'sites' => $sites, 'materials' => $materials,
                            'number' => $loop->iteration, 'isFirst' => $loop->first, 'isLast' => $loop->last,
                        ])

                        <div class="ml-6 pl-4 border-l-2 border-gray-200 dark:border-gray-700 space-y-3"
                            x-data="{ showAddChild: {{ old('parent_id') == $item->id ? 'true' : 'false' }} }">
                            @foreach ($item->children as $child)
                                @include('meetings._agenda-item-row', [
                                    'meeting' => $meeting, 'item' => $child, 'members' => $members, 'sites' => $sites, 'materials' => $materials,
                                    'number' => sprintf('%02d', $loop->iteration), 'isFirst' => $loop->first, 'isLast' => $loop->last,
                                    'nested' => true,
                                ])
                            @endforeach

                            <button type="button" x-show="!showAddChild" @click="showAddChild = true"
                                class="text-xs text-leather-500 dark:text-leather-300 hover:underline">
                                + {{ __('子項目を追加') }}
                            </button>

                            <form x-show="showAddChild" method="POST" action="{{ route('agenda-items.store', $meeting) }}"
                                class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end border border-dashed border-gray-300 dark:border-gray-600 rounded-md p-4">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $item->id }}">
                                <div class="sm:col-span-3">
                                    <x-input-label :value="__('子項目を追加')" />
                                    <x-text-input name="title" type="text" class="mt-1 block w-full" placeholder="{{ __('例: ●●の件') }}" value="{{ old('parent_id') == $item->id ? old('title') : '' }}" required />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>
                                <div>
                                    @include('meetings._assignee-field', ['members' => $members])
                                </div>
                                <div>
                                    @include('meetings._agenda-link-field', ['sites' => $sites, 'materials' => $materials])
                                </div>
                                <div class="flex gap-3">
                                    <x-primary-button>{{ __('子項目を追加') }}</x-primary-button>
                                    <x-secondary-button type="button" @click="showAddChild = false">{{ __('閉じる') }}</x-secondary-button>
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
                        @include('meetings._assignee-field', ['members' => $members])
                    </div>
                    <div>
                        @include('meetings._agenda-link-field', ['sites' => $sites, 'materials' => $materials])
                    </div>
                    <div>
                        <x-primary-button>{{ __('追加') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
