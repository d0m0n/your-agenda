<?php

namespace App\Providers;

use App\Models\Inquiry;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Models\User;
use App\Services\StorageUsageService;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage', fn (User $user) => $user->isGeneral());
        Gate::define('super-admin', fn (User $user) => $user->isSuperAdmin());

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
