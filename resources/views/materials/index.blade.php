<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('資料置き場') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @can('manage')
                <div class="bg-white dark:bg-ink-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('資料をアップロード') }}</h3>
                    <form method="POST" action="{{ route('materials.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end"
                        x-data="uploadProgress()" @submit.prevent="submitViaXhr">
                        @csrf
                        <div class="sm:col-span-1">
                            <x-input-label for="title" :value="__('タイトル')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-1">
                            <x-input-label for="file" :value="__('ファイル')" />
                            <input id="file" name="file" type="file" required
                                class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            <x-upload-progress-bar />
                        </div>
                        <div>
                            <x-primary-button class="disabled:opacity-50 disabled:cursor-not-allowed" x-bind:disabled="uploading">
                                <span x-show="!uploading">{{ __('アップロード') }}</span>
                                <span x-show="uploading" x-cloak>{{ __('アップロード中…') }}</span>
                            </x-primary-button>
                        </div>
                    </form>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('pdf, doc(x), xls(x), ppt(x), zip, jpg, png, gif, webp, txt, csv。最大20MB。') }}</p>
                </div>
            @endcan

            <div class="bg-white dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('タイトル') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('アップロード者') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('日時') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-ink-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($materials as $material)
                                <tr x-data="{ replacing: {{ old('material_id') == $material->id ? 'true' : 'false' }} }">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $material->title }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $material->user?->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $material->created_at->format('Y-m-d H:i') }}
                                        @unless ($material->updated_at->equalTo($material->created_at))
                                            <br>
                                            <span class="text-xs">{{ __('差し替え') }}: {{ $material->updated_at->format('Y-m-d H:i') }}</span>
                                        @endunless
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('materials.download', $material) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('ダウンロード') }}</a>
                                            @can('manage')
                                                <button type="button" @click="replacing = !replacing" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">{{ __('差し替え') }}</button>
                                                <x-confirm-delete-button
                                                    :id="'delete-material-'.$material->id"
                                                    :action="route('materials.destroy', $material)"
                                                    :message="__('この資料を削除しますか?')">
                                                    {{ __('削除') }}
                                                </x-confirm-delete-button>
                                            @endcan
                                        </div>
                                        @can('manage')
                                            <form x-show="replacing" x-cloak method="POST" action="{{ route('materials.update', $material) }}"
                                                enctype="multipart/form-data" class="mt-2 flex flex-col items-end gap-2"
                                                x-data="uploadProgress()" @submit.prevent="submitViaXhr">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="material_id" value="{{ $material->id }}">
                                                <div class="w-full text-left">
                                                    <input type="file" name="file" required
                                                        class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                                                    @if (old('material_id') == $material->id)
                                                        <x-input-error :messages="$errors->get('file')" class="mt-1" />
                                                    @endif
                                                    <x-upload-progress-bar />
                                                </div>
                                                <x-secondary-button type="submit" class="disabled:opacity-50 disabled:cursor-not-allowed shrink-0" x-bind:disabled="uploading">
                                                    <span x-show="!uploading">{{ __('差し替える') }}</span>
                                                    <span x-show="uploading" x-cloak>{{ __('アップロード中…') }}</span>
                                                </x-secondary-button>
                                            </form>
                                            <p x-show="replacing" x-cloak class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-left">
                                                {{ __('タイトルはそのまま、ファイルの中身だけを差し替えます。') }}
                                            </p>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('資料はまだありません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $materials->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
