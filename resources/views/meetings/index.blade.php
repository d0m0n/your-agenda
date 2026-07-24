<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('会議一覧') }}
            </h2>
            @can('manage')
                <a href="{{ route('meetings.create') }}" class="inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-brass-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-900 uppercase tracking-widest hover:bg-ink-700 dark:hover:bg-brass-300 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 dark:focus:ring-offset-ink-800 transition">
                    {{ __('新規登録') }}
                </a>
            @endcan
        </div>
    </x-slot>

    @php
        $sortUrl = function (string $column) use ($sort, $direction) {
            $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection]);
        };
        $sortIcon = function (string $column) use ($sort, $direction) {
            if ($sort !== $column) {
                return '';
            }

            return $direction === 'asc' ? ' ▲' : ' ▼';
        };
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-paper-200 dark:divide-ink-700">
                        <thead class="bg-paper-200 dark:bg-ink-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('name') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('会議名') }}{{ $sortIcon('name') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('held_at') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('開催日時') }}{{ $sortIcon('held_at') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('location') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('開催場所') }}{{ $sortIcon('location') }}</a>
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-paper-50 dark:bg-ink-800 divide-y divide-paper-200 dark:divide-ink-700">
                            @forelse ($meetings as $meeting)
                                <tr x-data x-on:click="window.location = '{{ route('meetings.show', $meeting) }}'" class="cursor-pointer hover:bg-paper-200/40 dark:hover:bg-ink-700/50 transition-colors">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $meeting->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $meeting->scheduleLabel() }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $meeting->location }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm space-x-3" x-on:click.stop>
                                        @can('manage')
                                            <a href="{{ route('meetings.edit', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('編集') }}</a>
                                            <a href="{{ route('meetings.agenda', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('次第編集') }}</a>
                                            <a href="{{ route('meetings.invitation.edit', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('案内文作成') }}</a>
                                            <x-confirm-delete-button
                                                :id="'delete-meeting-'.$meeting->id"
                                                :action="route('meetings.destroy', $meeting)"
                                                :message="__('この会議を削除しますか?(次第も削除されます)')">
                                                {{ __('削除') }}
                                            </x-confirm-delete-button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('登録されている会議はありません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $meetings->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
