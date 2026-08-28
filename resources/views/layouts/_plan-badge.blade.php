@php
    $isPlus = $organization->plan === \App\Enums\OrganizationPlan::Plus;
    // プラスはワインレッド(leather-500、手帳の革表紙をイメージした主アクセント色)の
    // 単色バッジにする。誕生日ペインの「本日」バッジ(bg-brass-500 text-white)と
    // 同じく、明暗どちらのテーマでも視認できる単色+白文字の組み合わせのため
    // dark:のバリエーションは持たせない。
    $planBadgeClass = $isPlus
        ? 'bg-leather-500 text-white'
        : 'bg-paper-200 text-ink-600 dark:bg-ink-700 dark:text-paper-100/70';
@endphp
@if (Auth::user()->isGeneral())
    <a href="{{ route('settings.edit') }}"
        class="inline-flex items-center gap-1 rounded-full px-4 py-1.5 text-xs font-medium shrink-0 {{ $planBadgeClass }}"
        title="{{ __('現在のプラン(基本設定で変更できます)') }}">
        {{ $organization->plan->label() }}{{ __('プラン') }}
    </a>
@else
    <span class="inline-flex items-center gap-1 rounded-full px-4 py-1.5 text-xs font-medium shrink-0 {{ $planBadgeClass }}">
        {{ $organization->plan->label() }}{{ __('プラン') }}
    </span>
@endif
