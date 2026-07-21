<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Zipアップロード') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('sites.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('タイトル')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="zip_file" :value="__('Zipファイル')" />
                        <input id="zip_file" name="zip_file" type="file" accept=".zip" required
                            class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('gian.htm はZip直下、もしくは1階層下のフォルダに配置してください。展開後の合計サイズは200MBまで、ファイル数は1000件までです。') }}
                        </p>
                        <x-input-error :messages="$errors->get('zip_file')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('sites.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('キャンセル') }}</a>
                        <x-primary-button>{{ __('アップロード') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
