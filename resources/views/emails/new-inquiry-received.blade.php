<x-mail::message>
# {{ __('新しいお問い合わせが届きました') }}

<x-mail::table>
| | |
|:---|:---|
| {{ __('種別') }} | {{ $inquiry->category->label() }} |
| {{ __('組織名') }} | {{ $inquiry->organization?->name ?? '-' }} |
| {{ __('送信者') }} | {{ $inquiry->user?->name ?? '-' }}({{ $inquiry->user?->email ?? '-' }}) |
| {{ __('件名') }} | {{ $inquiry->subject }} |
</x-mail::table>

{{ $inquiry->body }}

<x-mail::button :url="$adminUrl">
{{ __('管理者パネルで確認する') }}
</x-mail::button>
</x-mail::message>
