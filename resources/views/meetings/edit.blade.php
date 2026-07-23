<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('会議情報編集') }}: {{ $meeting->name }}
            </h2>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('meetings.agenda', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('次第を編集') }}
                </a>
                <a href="{{ route('meetings.invitation.edit', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('案内文作成') }}
                </a>
                <a href="{{ route('meetings.show', $meeting) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('会議画面を見る') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('会議情報') }}</h3>
                <form method="POST" action="{{ route('meetings.update', $meeting) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('meetings._form', ['meeting' => $meeting])

                    <div class="flex items-center justify-end gap-4">
                        <x-primary-button>{{ __('更新') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
