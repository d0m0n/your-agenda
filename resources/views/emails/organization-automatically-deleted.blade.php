<x-mail::message>
# {{ __('組織を自動削除しました') }}

<x-mail::table>
| | |
|:---|:---|
| {{ __('組織名') }} | {{ $organizationName }} |
| {{ __('組織ID(削除済み)') }} | {{ $organizationId }} |
| {{ __('アクセス権を失った日') }} | {{ $accessLostAt->jst()->format('Y-m-d') }} |
| {{ __('猶予期間') }} | {{ config('billing.deletion_grace_period_days') }}{{ __('日') }} |
| {{ __('削除日時') }} | {{ now()->jst()->format('Y-m-d H:i:s') }} |
</x-mail::table>

{{ __('トライアル終了・解約後、猶予期間内に再契約が無かったため、組織のデータを自動的に完全に削除しました。') }}
</x-mail::message>
