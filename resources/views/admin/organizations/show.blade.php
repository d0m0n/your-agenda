<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ $organization->name }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-leather-500 dark:text-leather-300 hover:underline">
                {{ __('組織一覧に戻る') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('契約状況') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('契約ステータス') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $organization->plan_status ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('契約日') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $organization->contracted_at?->format('Y-m-d') ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('データ使用量') }}</h3>
                    <x-confirm-delete-button
                        id="delete-organization-data"
                        :action="route('admin.organizations.destroy-data', $organization)"
                        :message="__('この組織がアップロードした資料・議案ファイル・画像を全て削除します。よろしいですか?(会議・メンバー等の記録は残ります)')"
                        :confirm-label="__('削除する')"
                        class="text-xs">
                        {{ __('アップロード済みデータを削除') }}
                    </x-confirm-delete-button>
                </div>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ \App\Services\StorageUsageService::formatBytes($usedBytes) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('資料置き場・議案ファイル・各種画像の合計です。') }}</p>
            </div>

            <div class="bg-white dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 pb-0">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('ユーザー') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('氏名') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('メールアドレス') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('種別') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('割り当て容量(GB)') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-ink-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($users as $orgUser)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $orgUser->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $orgUser->email }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $orgUser->isGeneral() ? __('一般ユーザー') : __('オブザーブユーザー') }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                                        @if ($orgUser->isGeneral())
                                            <form method="POST" action="{{ route('admin.organizations.users.update-quota', [$organization, $orgUser]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" step="0.1" min="0.1" max="1000" name="storage_quota_gb"
                                                    value="{{ round($orgUser->storageQuotaBytes() / 1024 / 1024 / 1024, 1) }}"
                                                    class="w-20 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                                                <x-secondary-button type="submit">{{ __('変更') }}</x-secondary-button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                        <x-confirm-delete-button
                                            :id="'delete-user-'.$orgUser->id"
                                            :action="route('admin.organizations.users.destroy', [$organization, $orgUser])"
                                            :message="__('このアカウントを削除しますか?')"
                                            :confirm-label="__('削除する')">
                                            {{ __('アカウント削除') }}
                                        </x-confirm-delete-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('ユーザーがいません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
