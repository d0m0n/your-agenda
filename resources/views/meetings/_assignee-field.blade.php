@php
    $memberId = $memberId ?? null;
    $assigneeName = $assigneeName ?? null;
@endphp

<div x-data="{
    mode: '{{ $memberId ? 'member' : ($assigneeName ? 'manual' : 'member') }}',
    memberId: '{{ $memberId }}',
    assigneeName: @js($assigneeName ?? ''),
}">
    <div class="flex items-center justify-between">
        <x-input-label :value="__('担当者')" />
        <button type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
            x-show="mode === 'member'" @click="mode = 'manual'; memberId = ''">{{ __('手入力に切替') }}</button>
        <button type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
            x-show="mode === 'manual'" @click="mode = 'member'; assigneeName = ''">{{ __('名簿から選ぶ') }}</button>
    </div>
    <select name="member_id" x-show="mode === 'member'" x-model="memberId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
        <option value="">{{ __('未定') }}</option>
        @foreach ($members as $member)
            <option value="{{ $member->id }}">{{ trim(($member->position?->name.' ').$member->name) }}</option>
        @endforeach
    </select>
    <input type="text" name="assignee_name" x-show="mode === 'manual'" x-model="assigneeName"
        placeholder="{{ __('担当者名を入力') }}"
        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm" />
</div>
