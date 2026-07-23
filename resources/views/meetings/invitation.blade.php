<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}の案内文</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('案内文作成') }}: {{ $meeting->name }}
            </h2>
            <a href="{{ route('meetings.agenda', $meeting) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                {{ __('次第編集に戻る') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{ tab: 'pdf' }">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('案内文作成') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('会議情報から自動作成した例文です。内容を手直ししてから保存できます。') }}</p>

                <div class="flex gap-1 border-b border-paper-200 dark:border-ink-700 mb-4">
                    <button type="button" @click="tab = 'pdf'"
                        :class="tab === 'pdf' ? 'border-leather-500 text-leather-500 dark:text-leather-300' : 'border-transparent text-gray-500 dark:text-gray-400'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">{{ __('PDF案内文') }}</button>
                    <button type="button" @click="tab = 'email'"
                        :class="tab === 'email' ? 'border-leather-500 text-leather-500 dark:text-leather-300' : 'border-transparent text-gray-500 dark:text-gray-400'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">{{ __('メール本文') }}</button>
                    <button type="button" @click="tab = 'line'"
                        :class="tab === 'line' ? 'border-leather-500 text-leather-500 dark:text-leather-300' : 'border-transparent text-gray-500 dark:text-gray-400'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">{{ __('LINE本文') }}</button>
                </div>

                @foreach (['pdf' => __('PDF案内文'), 'email' => __('メール本文'), 'line' => __('LINE本文')] as $type => $label)
                    <div x-show="tab === '{{ $type }}'" x-cloak>
                        <form method="POST" action="{{ route('meetings.invitation.update', $meeting) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="type" value="{{ $type }}">
                            <textarea name="body" rows="14"
                                class="block w-full text-sm font-mono border-paper-200 dark:border-ink-600 dark:bg-ink-900 dark:text-paper-100 rounded-md shadow-sm">{{ old('type') === $type ? old('body') : $invitationBodies[$type] }}</textarea>
                            @if (old('type') === $type)
                                <x-input-error :messages="$errors->get('body')" class="mt-2" />
                            @endif

                            <div class="flex flex-wrap items-center gap-4">
                                <x-primary-button>{{ __('保存') }}</x-primary-button>

                                @if ($type === 'pdf')
                                    <a href="{{ route('meetings.invitation.pdf', $meeting) }}" target="_blank" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                                        {{ __('印刷 / PDF保存') }}
                                    </a>
                                @else
                                    <button type="button" @click="navigator.clipboard.writeText($el.closest('form').querySelector('textarea').value)"
                                        class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                                        {{ __('コピー') }}
                                    </button>
                                @endif

                                @if ($meeting->{'invitation_'.$type.'_body'})
                                    <x-confirm-delete-button
                                        :id="'reset-invitation-'.$type"
                                        :action="route('meetings.invitation.reset', [$meeting, $type])"
                                        :message="__('編集内容を破棄して、自動生成された例文に戻しますか?')"
                                        :confirm-label="__('テンプレートに戻す')"
                                        class="text-sm">
                                        {{ __('テンプレートに戻す') }}
                                    </x-confirm-delete-button>
                                @endif
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
