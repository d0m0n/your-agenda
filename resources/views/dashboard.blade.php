<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    @if ($organization->header_image_path)
        <x-slot name="headerImage">
            <img src="{{ asset('storage/'.$organization->header_image_path) }}" alt="{{ $organization->name }}"
                class="w-full max-h-64 object-cover">
        </x-slot>
    @endif

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

        $paneCardClass = 'bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 shadow-md sm:rounded-lg overflow-hidden';
        $paneHeaderClass = 'flex items-center gap-3 px-6 pt-5 pb-4 sm:px-8 border-b border-dashed border-paper-200 dark:border-ink-600';
        $paneIconClass = 'h-5 w-5 shrink-0 text-leather-500 dark:text-leather-300';
        $paneBodyClass = 'px-6 py-5 sm:px-8';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @can('manage')
                @if ($isNewOrganization)
                    <div class="rounded-lg border border-dashed border-leather-300 dark:border-leather-600 bg-leather-50 dark:bg-leather-700/10 p-6">
                        <h3 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ __('ようこそ、「あなた次第」へ') }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('まずは以下から始めてみましょう。') }}</p>
                        <ol class="mt-4 space-y-3">
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-leather-500 text-xs font-medium text-white">1</span>
                                <a href="{{ route('members.create') }}" class="text-sm font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('メンバーを登録する') }}</a>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-leather-500 text-xs font-medium text-white">2</span>
                                <a href="{{ route('meetings.create') }}" class="text-sm font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('最初の会議を作成する') }}</a>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-leather-500 text-xs font-medium text-white">3</span>
                                <a href="{{ route('settings.edit') }}" class="text-sm font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('組織情報・ヘッダー画像を設定する') }}</a>
                            </li>
                        </ol>
                    </div>
                @endif
            @endcan

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @if ($organization->show_meetings_pane)
                    {{-- ペイン1: 今後の会議予定 --}}
                    <div class="{{ $paneCardClass }} @if($expandLast && $lastPane === 'meetings') lg:col-span-2 @endif">
                        <div class="{{ $paneHeaderClass }}">
                            <svg viewBox="0 0 24 24" class="{{ $paneIconClass }}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="8" />
                                <path d="M12 8v4l3 2" />
                            </svg>
                            <h3 class="font-serif text-base font-semibold text-ink-800 dark:text-paper-100">{{ __('今後の会議予定') }}</h3>
                        </div>
                        <div class="{{ $paneBodyClass }}">
                            <ul>
                                @forelse ($meetings as $meeting)
                                    <li class="py-3 first:pt-0 last:pb-0 border-b border-dashed border-paper-200 dark:border-ink-700 last:border-b-0">
                                        <a href="{{ route('meetings.show', $meeting) }}" class="text-sm font-medium text-ink-700 dark:text-paper-100 hover:text-leather-500 dark:hover:text-leather-300 hover:underline">
                                            {{ $meeting->name }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $meeting->scheduleLabel() }}
                                            @if ($meeting->location)
                                                ・{{ $meeting->location }}
                                            @endif
                                        </p>
                                    </li>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('予定なし') }}</p>
                                @endforelse
                            </ul>
                            @can('manage')
                                <a href="{{ route('meetings.index') }}" class="mt-4 inline-block text-xs font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('すべて見る') }}</a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if ($organization->show_calendar_pane)
                    {{-- ペイン2: カレンダー --}}
                    <div class="{{ $paneCardClass }} @if($expandLast && $lastPane === 'calendar') lg:col-span-2 @endif">
                        <div class="{{ $paneHeaderClass }}">
                            <svg viewBox="0 0 24 24" class="{{ $paneIconClass }}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="5" width="16" height="15" rx="2" />
                                <path d="M4 10h16M8 3v4M16 3v4" />
                            </svg>
                            <h3 class="font-serif text-base font-semibold text-ink-800 dark:text-paper-100">{{ __('カレンダー') }}</h3>
                        </div>
                        <div class="{{ $paneBodyClass }}">
                            @if ($organization->google_calendar_id)
                                <iframe
                                    src="https://calendar.google.com/calendar/embed?src={{ urlencode($organization->google_calendar_id) }}&ctz=Asia%2FTokyo"
                                    class="w-full h-64 border border-paper-200 dark:border-ink-700 rounded-md" loading="lazy"></iframe>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Googleカレンダーが設定されていません。') }}</p>
                                @can('manage')
                                    <a href="{{ route('settings.edit') }}" class="mt-2 inline-block text-xs font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('基本設定で設定する') }}</a>
                                @endcan
                            @endif
                        </div>
                    </div>
                @endif

                @if ($organization->show_birthday_pane)
                    {{-- ペイン3: 今月の誕生日メンバー --}}
                    <div class="{{ $paneCardClass }} @if($expandLast && $lastPane === 'birthday') lg:col-span-2 @endif">
                        <div class="{{ $paneHeaderClass }}">
                            <svg viewBox="0 0 24 24" class="{{ $paneIconClass }}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="9" width="16" height="11" rx="1.5" />
                                <path d="M4 13h16M12 9v11M8 9c-1.5 0-2.5-1-2.5-2.25S6.5 4.5 8 4.5 10.5 6 12 9c1.5-3 2.5-4.5 4-4.5s2.5 1 2.5 2.25S17.5 9 16 9" />
                            </svg>
                            <h3 class="font-serif text-base font-semibold text-ink-800 dark:text-paper-100">{{ __('今月の誕生日') }}</h3>
                        </div>
                        <div class="{{ $paneBodyClass }}">
                            <ul>
                                @forelse ($birthdayMembers as $member)
                                    @php $isToday = $member->birth_date->day === now()->day; @endphp
                                    <li class="flex items-center gap-3 text-sm py-2 first:pt-0 last:pb-0 border-b border-dashed border-paper-200 dark:border-ink-700 last:border-b-0 @if($isToday) -mx-2 rounded-md bg-brass-100 dark:bg-brass-600/20 px-2 border-b-0 @endif">
                                        @if ($member->photoUrl())
                                            <img src="{{ $member->photoUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover @if($isToday) ring-2 ring-brass-400 @endif">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-ink-700 @if($isToday) ring-2 ring-brass-400 @endif"></div>
                                        @endif
                                        <span class="text-ink-800 dark:text-paper-100 @if($isToday) font-semibold @endif">{{ $member->name }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            ({{ $member->birth_date->format('n/j') }}・{{ now()->year - $member->birth_date->year }}{{ __('歳') }})
                                        </span>
                                        @if ($isToday)
                                            <span class="ml-auto shrink-0 rounded-full bg-brass-500 px-2 py-0.5 text-xs font-medium tracking-wide text-white">
                                                {{ __('本日') }}
                                            </span>
                                        @endif
                                    </li>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('今月誕生日のメンバーはいません。') }}</p>
                                @endforelse
                            </ul>
                            @can('manage')
                                <a href="{{ route('members.index') }}" class="mt-4 inline-block text-xs font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('メンバー一覧を見る') }}</a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if ($organization->show_materials_pane)
                    {{-- ペイン4: 資料置き場 --}}
                    <div class="{{ $paneCardClass }} @if($expandLast && $lastPane === 'materials') lg:col-span-2 @endif">
                        <div class="{{ $paneHeaderClass }}">
                            <svg viewBox="0 0 24 24" class="{{ $paneIconClass }}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" />
                            </svg>
                            <h3 class="font-serif text-base font-semibold text-ink-800 dark:text-paper-100">{{ __('資料置き場') }}</h3>
                        </div>
                        <div class="{{ $paneBodyClass }}">
                            <ul>
                                @forelse ($materials as $material)
                                    <li class="flex items-center justify-between text-sm py-2 first:pt-0 last:pb-0 border-b border-dashed border-paper-200 dark:border-ink-700 last:border-b-0">
                                        <span class="text-ink-800 dark:text-paper-100">{{ $material->title }}</span>
                                        <a href="{{ route('materials.download', $material) }}" class="text-leather-500 dark:text-leather-300 hover:underline text-xs font-medium">{{ __('ダウンロード') }}</a>
                                    </li>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('資料はまだありません。') }}</p>
                                @endforelse
                            </ul>
                            <a href="{{ route('materials.index') }}" class="mt-4 inline-block text-xs font-medium text-leather-500 dark:text-leather-300 hover:underline">{{ __('すべて見る') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
