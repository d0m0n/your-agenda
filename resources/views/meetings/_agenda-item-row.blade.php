@php
    $nested = $nested ?? false;
    $linkTitle = $item->site->title ?? $item->material->title ?? null;
@endphp

<div x-data="{ editing: false }" class="border border-gray-200 dark:border-gray-700 rounded-md p-4 {{ $nested ? 'bg-gray-50 dark:bg-gray-900/40' : '' }}">
    <div x-show="!editing" class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $number }}. {{ $item->title }}</p>
            @if ($item->assigneeLabel() || $linkTitle)
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @if ($item->assigneeLabel())
                        {{ __('担当者') }}: {{ $item->assigneeLabel() }}
                    @endif
                    @if ($linkTitle)
                        @if ($item->assigneeLabel())・@endif
                        <a href="{{ $item->linkUrl() }}" target="_blank" rel="noopener noreferrer" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('議案') }}: {{ $linkTitle }}</a>
                    @endif
                </p>
            @endif
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('agenda-items.move-up', [$meeting, $item]) }}">
                @csrf
                <button type="submit" @if($isFirst) disabled class="opacity-30" @endif class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&uarr;</button>
            </form>
            <form method="POST" action="{{ route('agenda-items.move-down', [$meeting, $item]) }}">
                @csrf
                <button type="submit" @if($isLast) disabled class="opacity-30" @endif class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&darr;</button>
            </form>
            <button type="button" @click="editing = true" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">{{ __('編集') }}</button>
            <x-confirm-delete-button
                :id="'delete-agenda-item-'.$item->id"
                :action="route('agenda-items.destroy', [$meeting, $item])"
                :message="__('この次第を削除しますか?')"
                class="text-sm">
                {{ __('削除') }}
            </x-confirm-delete-button>
        </div>
    </div>

    <form x-show="editing" method="POST" action="{{ route('agenda-items.update', [$meeting, $item]) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        @csrf
        @method('PUT')
        <div class="sm:col-span-3">
            <x-input-label :value="__('議題')" />
            <x-text-input name="title" type="text" class="mt-1 block w-full" value="{{ $item->title }}" required />
        </div>
        <div>
            @include('meetings._assignee-field', ['members' => $members, 'memberId' => $item->member_id, 'assigneeName' => $item->assignee_name])
        </div>
        <div>
            @include('meetings._agenda-link-field', [
                'sites' => $sites, 'materials' => $materials,
                'selected' => $item->site_id ? 'site:'.$item->site_id : ($item->material_id ? 'material:'.$item->material_id : ''),
            ])
        </div>
        <div class="flex gap-3">
            <x-primary-button>{{ __('保存') }}</x-primary-button>
            <x-secondary-button type="button" @click="editing = false">{{ __('キャンセル') }}</x-secondary-button>
        </div>
    </form>
</div>
