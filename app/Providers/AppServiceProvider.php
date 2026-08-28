<?php

namespace App\Providers;

use Anthropic\Client as AnthropicClient;
use App\Models\Inquiry;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Models\User;
use App\Services\StorageUsageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AI議事録生成機能(プラスプラン限定)で使う。config('claude.api_key')が
        // 未設定でもここでは例外にせず、実際にメッセージを送るタイミングで
        // Anthropic SDK側のエラーとして表面化させる。
        $this->app->singleton(AnthropicClient::class, fn () => new AnthropicClient(
            apiKey: config('claude.api_key'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // config('app.timezone')はUTCのまま(DB保存済みの日時をJSTとして
        // 誤解釈しないよう変更しない)。画面表示だけJSTにしたい箇所は
        // ->jst()を挟んでからformat()する(例: $model->created_at->jst()->format(...))。
        Carbon::macro('jst', function () {
            /** @var Carbon $this */
            return $this->copy()->timezone('Asia/Tokyo');
        });

        Gate::define('manage', fn (User $user) => $user->isGeneral());
        Gate::define('super-admin', fn (User $user) => $user->isSuperAdmin());

        // プラスプラン限定機能用。将来ルート・Blade側で
        // can:plus / @can('plus') として参照する(実装済みのプラス限定機能は
        // まだ無いが、管理者パネルでplanを切り替えられる状態は準備済み)。
        Gate::define('plus', fn (User $user) => $user->organization?->hasPlusAccess() ?? false);

        // 「1組織=1契約」のため、Stripeの顧客(Billable)はUserではなくOrganization。
        Cashier::useCustomerModel(Organization::class);

        // ナビの容量バッジ用。使用量の実測はディスクI/Oを伴うため、ページ遷移の
        // たびに計算しないよう組織単位で少しの間だけキャッシュする(多少古くても
        // 「そろそろ危ない」を知らせる目的なので厳密さは不要)。
        View::composer('layouts.navigation', function ($view) {
            $user = Auth::user();

            // トライアル残り日数バッジは一般・オブザーブ両方に表示する
            // (どちらのユーザーもトライアル終了の影響を受けるため)。
            $view->with(
                'trialDaysRemaining',
                $user?->organization?->trialDaysRemaining() ?: null
            );

            if (! $user?->isGeneral() || ! $user->organization) {
                $view->with('storageUsagePercent', null);

                return;
            }

            $percent = Cache::remember(
                "storage-usage-percent-{$user->organization_id}",
                now()->addMinutes(10),
                function () use ($user) {
                    $storageUsage = app(StorageUsageService::class);
                    $quota = $storageUsage->quotaBytes($user);

                    return $quota > 0
                        ? min(100, (int) round($storageUsage->usedBytes($user->organization) / $quota * 100))
                        : 0;
                }
            );

            $view->with('storageUsagePercent', $percent);
        });

        View::composer('layouts.admin', function ($view) {
            $view->with(
                'adminUnhandledInquiriesCount',
                Inquiry::withoutGlobalScope(OrganizationScope::class)->whereNull('handled_at')->count()
            );
        });

        // 常設の再課金導線バナー用。一般・オブザーブ両方に表示する
        // (どちらのユーザーもアクセス制限の影響を受けるため)。
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();

            $view->with(
                'organizationHasActiveAccess',
                $user?->organization ? $user->organization->hasActiveAccess() : null
            );
        });
    }
}
