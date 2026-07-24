<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
            {{ __('組織一覧') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-paper-50 dark:bg-ink-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-paper-200 dark:divide-ink-700">
                        <thead class="bg-paper-200 dark:bg-ink-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('組織名') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('契約状況') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('契約日') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('ユーザー数') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('データ使用量') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('お問い合わせ') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-paper-50 dark:bg-ink-800 divide-y divide-paper-200 dark:divide-ink-700">
                            @forelse ($organizations as $organization)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $organization->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $organization->subscriptionStatusLabel() }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $organization->contracted_at?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $organization->users_count }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \App\Services\StorageUsageService::formatBytes($usageByOrganization[$organization->id]) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if ($organization->inquiries_count > 0)
                                            <a href="{{ route('admin.inquiries.index', ['organization_id' => $organization->id]) }}" class="text-leather-500 dark:text-leather-300 hover:underline">
                                                {{ $organization->inquiries_count }}{{ __('件') }}
                                            </a>
                                        @else
                                            0{{ __('件') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.organizations.show', $organization) }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ __('詳細') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('組織が登録されていません。') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
