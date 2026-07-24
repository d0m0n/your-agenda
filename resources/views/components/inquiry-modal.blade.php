@php
    $hasInquiryErrors = $errors->hasAny(['category', 'subject', 'body']);
@endphp

<x-modal name="inquiry-form" :show="$hasInquiryErrors" max-width="lg">
    <form method="POST" action="{{ route('inquiries.store') }}" class="p-6 space-y-5">
        @csrf

        <div>
            <h2 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ __('お問い合わせ') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('ご質問・不具合報告・機能追加のご要望など、お気軽にお送りください。') }}
            </p>
        </div>

        <div>
            <x-input-label for="inquiry_category" :value="__('種類')" />
            <select id="inquiry_category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-leather-400 focus:ring-leather-400">
                @foreach (\App\Enums\InquiryCategory::cases() as $category)
                    <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="inquiry_subject" :value="__('件名')" />
            <x-text-input id="inquiry_subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')" required />
            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="inquiry_body" :value="__('内容')" />
            <textarea id="inquiry_body" name="body" rows="5" required
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-leather-400 focus:ring-leather-400">{{ old('body') }}</textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
        </div>

        <div class="flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                {{ __('キャンセル') }}
            </x-secondary-button>
            <x-primary-button>
                {{ __('送信する') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>
