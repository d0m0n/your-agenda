<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('役職管理') }}
            </h2>
            <a href="{{ route('positions.create') }}" class="inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-brass-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-900 uppercase tracking-widest hover:bg-ink-700 dark:hover:bg-brass-300 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 dark:focus:ring-offset-ink-800 transition">
                {{ __('新規登録') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-paper-200 dark:divide-ink-700">
                        <thead class="bg-paper-200 dark:bg-ink-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('通し番号') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('役職名') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-paper-50 dark:bg-ink-800 divide-y divide-paper-200 dark:divide-ink-700">
                            @forelse ($positions as $position)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $position->serial_number }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $position->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm space-x-3">
                                        <a href="{{ route('positions.edit', $position) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('編集') }}</a>
                                        <x-confirm-delete-button
                                            :id="'delete-position-'.$position->id"
                                            :action="route('positions.destroy', $position)"
                                            :message="__('この役職を削除しますか?(メンバーの役職は未設定になります)')">
                                            {{ __('削除') }}
                                        </x-confirm-delete-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('登録されている役職はありません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
