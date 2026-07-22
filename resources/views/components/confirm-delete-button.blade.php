@props([
    'id',
    'action',
    'title' => '削除の確認',
    'message',
    'confirmLabel' => '削除する',
    'method' => 'DELETE',
])

<button type="button" x-data x-on:click.prevent="$dispatch('open-modal', '{{ $id }}')"
    {{ $attributes->merge(['class' => 'text-red-600 dark:text-red-400 hover:underline']) }}>
    {{ $slot }}
</button>

<x-modal :name="$id" max-width="sm">
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @method($method)

        <h2 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ $title }}</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                {{ __('キャンセル') }}
            </x-secondary-button>
            <x-danger-button>
                {{ $confirmLabel }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
