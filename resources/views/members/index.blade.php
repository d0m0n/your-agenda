<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('メンバー一覧') }}
            </h2>
            <div class="flex items-center gap-4">
                <div x-data class="inline-flex rounded-md border border-paper-200 dark:border-ink-700 overflow-hidden text-sm">
                    <button type="button" @click="$store.membersView.set('table')"
                        :class="$store.membersView.mode === 'table' ? 'bg-leather-500 text-white' : 'bg-paper-50 dark:bg-ink-800 text-gray-600 dark:text-gray-300'"
                        class="px-3 py-1.5 transition-colors">
                        {{ __('表形式') }}
                    </button>
                    <button type="button" @click="$store.membersView.set('card')"
                        :class="$store.membersView.mode === 'card' ? 'bg-leather-500 text-white' : 'bg-paper-50 dark:bg-ink-800 text-gray-600 dark:text-gray-300'"
                        class="px-3 py-1.5 border-l border-paper-200 dark:border-ink-700 transition-colors">
                        {{ __('カード形式') }}
                    </button>
                </div>
                @can('manage')
                    <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-brass-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-900 uppercase tracking-widest hover:bg-ink-700 dark:hover:bg-brass-300 focus:outline-none focus:ring-2 focus:ring-leather-400 focus:ring-offset-2 dark:focus:ring-offset-ink-800 transition">
                        {{ __('新規登録') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @php
        $genderLabels = ['male' => __('男性'), 'female' => __('女性'), 'other' => __('その他')];
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
        <div x-data class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="px-4 py-3 rounded-md bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @can('manage')
                <div class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('CSV一括登録') }}</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('members.csv-template') }}" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                            {{ __('CSVテンプレートをダウンロード') }}
                        </a>
                        <a href="{{ route('members.export') }}" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                            {{ __('登録メンバーをCSVでダウンロード') }}
                        </a>
                        <form method="POST" action="{{ route('members.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="file" name="csv_file" accept=".csv,text/csv" required
                                class="text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
                            <x-secondary-button type="submit">{{ __('CSVから一括登録') }}</x-secondary-button>
                        </form>
                    </div>
                </div>
            @endcan

            <div x-show="$store.membersView.mode === 'table'" x-cloak class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-paper-200 dark:divide-ink-700">
                        <thead class="bg-paper-200 dark:bg-ink-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('serial_number') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('通し番号') }}{{ $sortIcon('serial_number') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('name') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('氏名') }}{{ $sortIcon('name') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('position') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('役職') }}{{ $sortIcon('position') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('所属部署') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('name_kana') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('よみがな') }}{{ $sortIcon('name_kana') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('性別') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('company') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('所属企業') }}{{ $sortIcon('company') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('birth_date') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('生年月日') }}{{ $sortIcon('birth_date') }}</a>
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-paper-50 dark:bg-ink-800 divide-y divide-paper-200 dark:divide-ink-700">
                            @forelse ($members as $member)
                                <tr x-data x-on:click="window.location = '{{ route('members.show', $member) }}'" class="cursor-pointer hover:bg-paper-200/40 dark:hover:bg-ink-700/50 transition-colors">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($member->photoUrl())
                                            <img src="{{ $member->photoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->serial_number }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $member->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->position?->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->department?->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->name_kana }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $genderLabels[$member->gender] ?? '' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->company }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->birth_date?->format('Y-m-d') }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm space-x-3" x-on:click.stop>
                                        @can('manage')
                                            <a href="{{ route('members.edit', $member) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('編集') }}</a>
                                            <x-confirm-delete-button
                                                :id="'delete-member-'.$member->id"
                                                :action="route('members.destroy', $member)"
                                                :message="__('このメンバーを削除しますか?')">
                                                {{ __('削除') }}
                                            </x-confirm-delete-button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('登録されているメンバーはいません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="$store.membersView.mode === 'card'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($members as $member)
                    <a href="{{ route('members.show', $member) }}"
                        class="group block bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg shadow-sm hover:shadow-md transition-shadow p-5">
                        <div class="flex items-center gap-4">
                            @if ($member->photoUrl())
                                <img src="{{ $member->photoUrl() }}" alt="" class="h-14 w-14 rounded-full object-cover shrink-0">
                            @else
                                <div class="h-14 w-14 rounded-full bg-ink-800 dark:bg-ink-900 shrink-0 flex items-center justify-center">
                                    <span class="font-serif text-lg text-paper-100">{{ mb_substr($member->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-serif text-base font-semibold text-ink-800 dark:text-paper-100 truncate group-hover:text-leather-500 dark:group-hover:text-leather-300">
                                    {{ $member->name }}
                                </p>
                                @if ($member->position || $member->department)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $member->position?->name }}
                                        @if ($member->position && $member->department)
                                            <span class="text-gray-400 dark:text-gray-600">/</span>
                                        @endif
                                        {{ $member->department?->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if ($member->company)
                            <p class="mt-3 pt-3 border-t border-dashed border-paper-200 dark:border-ink-700 text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $member->company }}
                            </p>
                        @endif
                    </a>
                @empty
                    <p class="col-span-full text-sm text-gray-500 dark:text-gray-400">{{ __('登録されているメンバーはいません。') }}</p>
                @endforelse
            </div>

            <div>
                {{ $members->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
