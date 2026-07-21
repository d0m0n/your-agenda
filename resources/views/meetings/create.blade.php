<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('会議新規登録') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('meetings.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @include('meetings._form', ['meeting' => null])

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('meetings.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('キャンセル') }}</a>
                        <x-primary-button>{{ __('登録') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
