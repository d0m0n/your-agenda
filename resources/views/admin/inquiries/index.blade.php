<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('お問い合わせ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('admin.inquiries.index') }}" class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-4">
                <div>
                    <x-input-label for="filter_status" :value="__('状態')" />
                    <select id="filter_status" name="status" class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm text-sm">
                        <option value="" @selected(($filters['status'] ?? '') === '')>{{ __('すべて') }}</option>
                        <option value="unhandled" @selected(($filters['status'] ?? '') === 'unhandled')>{{ __('未対応') }}</option>
                        <option value="handled" @selected(($filters['status'] ?? '') === 'handled')>{{ __('対応済み') }}</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="filter_category" :value="__('種類')" />
                    <select id="filter_category" name="category" class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm text-sm">
                        <option value="" @selected(($filters['category'] ?? '') === '')>{{ __('すべて') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>{{ $category->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="filter_q" :value="__('キーワード検索(件名・内容・組織名・氏名)')" />
                    <x-text-input id="filter_q" name="q" type="text" class="mt-1 block w-full text-sm" :value="$filters['q'] ?? ''" />
                </div>

                <div class="flex gap-2">
                    <x-primary-button type="submit">{{ __('絞り込む') }}</x-primary-button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline self-center">{{ __('条件をクリア') }}</a>
                    @endif
                </div>
            </form>

            <div class="space-y-4">
                @forelse ($inquiries as $inquiry)
                    <div class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-ink-800 dark:bg-brass-400 text-white dark:text-ink-900">
                                        {{ $inquiry->category->label() }}
                                    </span>
                                    @if ($inquiry->isHandled())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">
                                            {{ __('対応済み') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300">
                                            {{ __('未対応') }}
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-2 font-serif text-base font-semibold text-ink-800 dark:text-paper-100">{{ $inquiry->subject }}</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $inquiry->organization?->name ?? __('(組織削除済み)') }}
                                    /
                                    {{ $inquiry->user?->name ?? __('(ユーザー削除済み)') }}
                                    ・{{ $inquiry->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('admin.inquiries.toggle-handled', $inquiry) }}">
                                @csrf
                                @method('PATCH')
                                @if ($inquiry->isHandled())
                                    <x-secondary-button type="submit">{{ __('未対応に戻す') }}</x-secondary-button>
                                @else
                                    <x-primary-button type="submit">{{ __('対応済みにする') }}</x-primary-button>
                                @endif
                            </form>
                        </div>

                        <p class="mt-4 text-sm text-ink-700 dark:text-paper-200 whitespace-pre-wrap">{{ $inquiry->body }}</p>
                    </div>
                @empty
                    <div class="bg-paper-50 dark:bg-ink-800 shadow-sm sm:rounded-lg p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('該当するお問い合わせはありません。') }}
                    </div>
                @endforelse
            </div>

            <div>
                {{ $inquiries->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
