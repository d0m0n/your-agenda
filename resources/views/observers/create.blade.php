<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('オブザーブユーザー新規作成') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('observers.store') }}" class="space-y-6">
                    @csrf

                    @include('observers._form', ['observer' => null])

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('observers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('キャンセル') }}</a>
                        <x-primary-button>{{ __('作成') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
