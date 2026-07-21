<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    @php
        // ペインが奇数個表示のとき、最後の1つを2カラム分の幅に広げて
        // 半分だけ空く不格好なレイアウトにならないようにする。
        $visiblePanes = collect([
            'meetings' => $organization->show_meetings_pane,
            'calendar' => $organization->show_calendar_pane,
            'birthday' => $organization->show_birthday_pane,
            'materials' => $organization->show_materials_pane,
        ])->filter()->keys();
        $lastPane = $visiblePanes->last();
        $expandLast = $visiblePanes->count() % 2 === 1;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($organization->header_image_path)
                <img src="{{ asset('storage/'.$organization->header_image_path) }}" alt="{{ $organization->name }}"
                    class="w-full max-h-64 object-cover rounded-lg shadow-sm">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @if ($organization->show_meetings_pane)
                    {{-- ペイン1: 会議一覧 --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 @if($expandLast && $lastPane === 'meetings') lg:col-span-2 @endif">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('会議一覧') }}</h3>
                        <ul class="space-y-3">
                            @forelse ($meetings as $meeting)
                                <li>
                                    <a href="{{ route('meetings.show', $meeting) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $meeting->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $meeting->held_at?->format('Y-m-d H:i') }}</p>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('会議はまだ登録されていません。') }}</p>
                            @endforelse
                        </ul>
                        @can('manage')
                            <a href="{{ route('meetings.index') }}" class="mt-4 inline-block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('すべて見る') }}</a>
                        @endcan
                    </div>
                @endif

                @if ($organization->show_calendar_pane)
                    {{-- ペイン2: カレンダー --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 @if($expandLast && $lastPane === 'calendar') lg:col-span-2 @endif">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('カレンダー') }}</h3>
                        @if ($organization->google_calendar_id)
                            <iframe
                                src="https://calendar.google.com/calendar/embed?src={{ urlencode($organization->google_calendar_id) }}&ctz=Asia%2FTokyo"
                                class="w-full h-64 border-0" loading="lazy"></iframe>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Googleカレンダーが設定されていません。') }}</p>
                            @can('manage')
                                <a href="{{ route('settings.edit') }}" class="mt-2 inline-block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('基本設定で設定する') }}</a>
                            @endcan
                        @endif
                    </div>
                @endif

                @if ($organization->show_birthday_pane)
                    {{-- ペイン3: 今月の誕生日メンバー --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 @if($expandLast && $lastPane === 'birthday') lg:col-span-2 @endif">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('今月の誕生日') }}</h3>
                        <ul class="space-y-2">
                            @forelse ($birthdayMembers as $member)
                                <li class="flex items-center gap-3 text-sm">
                                    @if ($member->photoUrl())
                                        <img src="{{ $member->photoUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                    @endif
                                    <span class="text-gray-900 dark:text-gray-100">{{ $member->name }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        ({{ $member->birth_date->format('n/j') }}・{{ now()->year - $member->birth_date->year }}{{ __('歳') }})
                                    </span>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('今月誕生日のメンバーはいません。') }}</p>
                            @endforelse
                        </ul>
                        @can('manage')
                            <a href="{{ route('members.index') }}" class="mt-4 inline-block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('メンバー一覧を見る') }}</a>
                        @endcan
                    </div>
                @endif

                @if ($organization->show_materials_pane)
                    {{-- ペイン4: 資料置き場 --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 @if($expandLast && $lastPane === 'materials') lg:col-span-2 @endif">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('資料置き場') }}</h3>
                        <ul class="space-y-2">
                            @forelse ($materials as $material)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-gray-900 dark:text-gray-100">{{ $material->title }}</span>
                                    <a href="{{ route('materials.download', $material) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">{{ __('ダウンロード') }}</a>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('資料はまだありません。') }}</p>
                            @endforelse
                        </ul>
                        <a href="{{ route('materials.index') }}" class="mt-4 inline-block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('すべて見る') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
