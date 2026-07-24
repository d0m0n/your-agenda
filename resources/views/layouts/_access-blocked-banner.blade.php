@if (($organizationHasActiveAccess ?? null) === false && ! request()->routeIs('billing.*'))
    <div class="print-hidden bg-red-600 text-white text-sm text-center py-2 px-4">
        {{ __('お支払い情報の未登録により、新規作成や議案データの閲覧など一部機能がご利用いただけません。') }}
        <a href="{{ route('billing.paywall') }}" class="underline font-semibold hover:text-red-100">
            {{ __('今すぐお支払い情報を登録する') }}
        </a>
    </div>
@endif
