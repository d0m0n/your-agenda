<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('オブザーブユーザー管理') }}
            </h2>
            <a href="{{ route('observers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 transition">
                {{ __('新規作成') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-paper-200 dark:divide-ink-700">
                        <thead class="bg-paper-200 dark:bg-ink-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('氏名') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('ログインID(メール)') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-paper-50 dark:bg-ink-800 divide-y divide-paper-200 dark:divide-ink-700">
                            @forelse ($observers as $observer)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $observer->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $observer->email }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm space-x-3">
                                        <a href="{{ route('observers.edit', $observer) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('編集') }}</a>
                                        <x-confirm-delete-button
                                            :id="'delete-observer-'.$observer->id"
                                            :action="route('observers.destroy', $observer)"
                                            :message="__('このオブザーブユーザーを削除しますか?')">
                                            {{ __('削除') }}
                                        </x-confirm-delete-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('オブザーブユーザーはいません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="{{ route('settings.edit') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('基本設定に戻る') }}</a>
        </div>
    </div>
</x-app-layout>
