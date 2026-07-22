<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('基本設定') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $usagePercent = $quotaBytes > 0 ? min(100, (int) round($usedBytes / $quotaBytes * 100)) : 0;
                $usageColor = match (true) {
                    $usagePercent >= 100 => 'bg-red-600',
                    $usagePercent >= 80 => 'bg-amber-500',
                    default => 'bg-indigo-600',
                };
            @endphp
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('データ使用量') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    {{ __('資料置き場・議案ファイル・各種画像の合計です。') }}
                </p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="{{ $usageColor }} h-2.5 rounded-full" style="width: {{ $usagePercent }}%"></div>
                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ \App\Services\StorageUsageService::formatBytes($usedBytes) }} / {{ \App\Services\StorageUsageService::formatBytes($quotaBytes) }}
                    <span class="text-gray-500 dark:text-gray-400">({{ $usagePercent }}%)</span>
                </p>
                @if ($usagePercent >= 100)
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ __('容量の上限に達しています。新しいファイルはアップロードできません。不要なデータを削除してください。') }}</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('組織情報') }}</h3>
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('組織名')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $organization->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="google_calendar_id" :value="__('Google カレンダーID')" />
                        <x-text-input id="google_calendar_id" name="google_calendar_id" type="text" class="mt-1 block w-full"
                            :value="old('google_calendar_id', $organization->google_calendar_id)" placeholder="example@group.calendar.google.com" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Googleカレンダーの「設定」→「カレンダーの統合」にあるカレンダーIDを入力してください。') }}</p>
                        <x-input-error :messages="$errors->get('google_calendar_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="header_image" :value="__('ダッシュボードのヘッダー画像')" />
                        @if ($organization->header_image_path)
                            <img src="{{ asset('storage/'.$organization->header_image_path) }}" alt="" class="mt-2 h-24 w-full max-w-md rounded-md object-cover">
                        @endif
                        <input id="header_image" name="header_image" type="file" accept="image/jpeg,image/png,image/webp"
                            class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('jpg / png / webp形式。自動でリサイズされます。') }}</p>
                        <x-input-error :messages="$errors->get('header_image')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="icon_image" :value="__('アイコン画像')" />
                        @if ($organization->icon_image_path)
                            <img src="{{ asset('storage/'.$organization->icon_image_path) }}" alt="" class="mt-2 h-16 w-16 rounded-md object-cover">
                        @endif
                        <input id="icon_image" name="icon_image" type="file" accept="image/jpeg,image/png,image/webp"
                            class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ナビゲーションメニュー左側に表示されるアイコンです。jpg / png / webp形式。自動でリサイズされます。') }}</p>
                        <x-input-error :messages="$errors->get('icon_image')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label :value="__('ダッシュボードに表示するペイン')" />
                        <div class="mt-2 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="show_meetings_pane" value="1" @checked(old('show_meetings_pane', $organization->show_meetings_pane)) class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                {{ __('会議一覧') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="show_calendar_pane" value="1" @checked(old('show_calendar_pane', $organization->show_calendar_pane)) class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                {{ __('カレンダー') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="show_birthday_pane" value="1" @checked(old('show_birthday_pane', $organization->show_birthday_pane)) class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                {{ __('今月の誕生日') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="show_materials_pane" value="1" @checked(old('show_materials_pane', $organization->show_materials_pane)) class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                {{ __('資料置き場') }}
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('更新') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('オブザーブユーザー') }}</h3>
                    <a href="{{ route('observers.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('管理する') }}</a>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('閲覧専用アカウントの作成・パスワード変更・削除ができます。') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('次第の一括ダウンロード') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('全会議の次第とZip議案をまとめてダウンロードします。解約時のデータ持ち出しにも利用できます。') }}</p>
                    </div>
                    <a href="{{ route('settings.export') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shrink-0">
                        {{ __('ダウンロード') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
