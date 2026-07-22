<x-app-layout>
    <x-slot name="title">{{ $member->organization->name }}の名刺 | {{ $member->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('プロフィール') }}
            </h2>
            <div class="flex items-center gap-4 text-sm">
                @can('manage')
                    <a href="{{ route('members.edit', $member) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                        {{ __('編集') }}
                    </a>
                    <x-confirm-delete-button
                        :id="'delete-member-'.$member->id"
                        :action="route('members.destroy', $member)"
                        :message="__('このメンバーを削除しますか?')">
                        {{ __('削除') }}
                    </x-confirm-delete-button>
                @endcan
                <a href="{{ route('members.index') }}" class="text-gray-500 dark:text-gray-400 hover:underline">
                    {{ __('一覧に戻る') }}
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $genderLabels = ['male' => __('男性'), 'female' => __('女性'), 'other' => __('その他')];
        $snsAccounts = [
            'LINE' => $member->line_id,
            'X' => $member->x_account,
            'Instagram' => $member->instagram_account,
            'Facebook' => $member->facebook_account,
            'TikTok' => $member->tiktok_account,
        ];
        $snsAccounts = array_filter($snsAccounts);
        $hasBackNotes = $member->birth_date || $member->gender || $member->hobby || $member->motto || $snsAccounts;
    @endphp

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            {{-- 印刷した名刺を模したカード。真鍮色の箔押しラインを最上部に置き、
                 表面(氏名・役職・連絡先)と裏面(生年月日・趣味・座右の銘・SNS)を
                 破線の区切りで分ける。 --}}
            <div class="relative bg-paper-50 dark:bg-ink-800 shadow-xl rounded-lg overflow-hidden border border-brass-300/50 dark:border-ink-700">
                <div class="h-1.5 bg-gradient-to-r from-brass-300 via-brass-500 to-brass-300"></div>

                @if ($member->serial_number)
                    <p class="absolute top-6 right-6 sm:right-10 text-[11px] tracking-[0.2em] text-ink-400 dark:text-ink-200 uppercase">
                        No. {{ str_pad((string) $member->serial_number, 3, '0', STR_PAD_LEFT) }}
                    </p>
                @endif

                {{-- 表面: 氏名・役職・連絡先 --}}
                <div class="px-8 pt-8 pb-6 sm:px-10">
                    <div class="flex items-start gap-6">
                        @if ($member->photoUrl())
                            <img src="{{ $member->photoUrl() }}" alt="" class="h-24 w-24 rounded-full object-cover ring-4 ring-paper-100 dark:ring-ink-900 border border-brass-300/60 shrink-0">
                        @else
                            <div class="h-24 w-24 rounded-full bg-ink-800 dark:bg-ink-900 ring-4 ring-paper-100 dark:ring-ink-900 border border-brass-300/60 shrink-0 flex items-center justify-center">
                                <span class="font-serif text-3xl text-brass-200">{{ mb_substr($member->name, 0, 1) }}</span>
                            </div>
                        @endif

                        <div class="min-w-0 pt-1">
                            <h1 class="font-serif text-3xl font-bold text-ink-800 dark:text-paper-100 leading-tight break-words">
                                {{ $member->name }}
                            </h1>
                            @if ($member->name_kana || $member->name_romaji)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 tracking-wide">
                                    {{ $member->name_kana }}
                                    @if ($member->name_romaji)
                                        <span class="italic text-gray-400 dark:text-gray-500">{{ $member->name_kana ? '・' : '' }}{{ $member->name_romaji }}</span>
                                    @endif
                                </p>
                            @endif

                            @if ($member->position || $member->company)
                                <p class="mt-3 text-sm font-medium tracking-wide text-leather-500 dark:text-leather-300">
                                    {{ $member->position?->name }}
                                    @if ($member->position && $member->company)
                                        <span class="text-gray-400 dark:text-gray-600">/</span>
                                    @endif
                                    {{ $member->company }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($member->phone || $member->email)
                        <dl class="mt-6 pt-6 border-t border-dashed border-paper-200 dark:border-ink-600 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @if ($member->phone)
                                <div class="flex items-baseline gap-2">
                                    <dt class="text-[11px] tracking-[0.15em] text-gray-400 dark:text-gray-500 uppercase">{{ __('TEL') }}</dt>
                                    <dd class="text-ink-800 dark:text-paper-100">{{ $member->phone }}</dd>
                                </div>
                            @endif
                            @if ($member->email)
                                <div class="flex items-baseline gap-2 min-w-0">
                                    <dt class="text-[11px] tracking-[0.15em] text-gray-400 dark:text-gray-500 uppercase">{{ __('MAIL') }}</dt>
                                    <dd class="text-ink-800 dark:text-paper-100 truncate">{{ $member->email }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>

                {{-- 裏面: 生年月日・趣味・座右の銘・SNS --}}
                @if ($hasBackNotes)
                    <div class="border-t border-dashed border-paper-200 dark:border-ink-600 bg-paper-200/40 dark:bg-ink-900/30 px-8 py-6 sm:px-10 space-y-5">
                        @if ($member->birth_date || $member->gender)
                            <dl class="flex flex-wrap gap-x-10 gap-y-2 text-sm">
                                @if ($member->birth_date)
                                    <div class="flex items-baseline gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('生年月日') }}</dt>
                                        <dd class="font-medium text-ink-800 dark:text-paper-100">
                                            {{ $member->birth_date->format('Y年n月j日') }}(満{{ $member->birth_date->age }}歳)
                                        </dd>
                                    </div>
                                @endif
                                @if ($member->gender)
                                    <div class="flex items-baseline gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('性別') }}</dt>
                                        <dd class="font-medium text-ink-800 dark:text-paper-100">{{ $genderLabels[$member->gender] ?? $member->gender }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif

                        @if ($member->hobby)
                            <div>
                                <h3 class="text-[11px] font-semibold tracking-[0.15em] text-gray-500 dark:text-gray-400 uppercase mb-1">{{ __('趣味') }}</h3>
                                <p class="text-sm text-ink-800 dark:text-paper-100">{{ $member->hobby }}</p>
                            </div>
                        @endif

                        @if ($member->motto)
                            <div class="border-l-2 border-leather-400 pl-4">
                                <h3 class="text-[11px] font-semibold tracking-[0.15em] text-gray-500 dark:text-gray-400 uppercase mb-1">{{ __('座右の銘') }}</h3>
                                <p class="font-serif italic text-ink-700 dark:text-paper-200">「{{ $member->motto }}」</p>
                            </div>
                        @endif

                        @if ($snsAccounts)
                            <div>
                                <h3 class="text-[11px] font-semibold tracking-[0.15em] text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('SNS') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($snsAccounts as $label => $value)
                                        <span class="inline-flex items-center gap-1.5 text-xs bg-paper-50 dark:bg-ink-800 border border-brass-300/60 dark:border-ink-600 rounded-full pl-2.5 pr-3 py-1">
                                            <span class="font-semibold text-brass-600 dark:text-brass-300">{{ $label }}</span>
                                            <span class="text-ink-700 dark:text-paper-200">{{ $value }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
