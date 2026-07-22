<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('メンバー編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('members._form', ['member' => $member])

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('members.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('キャンセル') }}</a>
                        <x-primary-button>{{ __('更新') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
