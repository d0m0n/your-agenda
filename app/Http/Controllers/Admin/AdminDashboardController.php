<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Services\StorageUsageService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(StorageUsageService $storageUsage): View
    {
        // inquiriesはBelongsToOrganizationのグローバルスコープを持つため、
        // 素のwithCountだと管理者自身(organization_id=null)でフィルタされ
        // 常に0件になってしまう。withoutGlobalScopeで組織を横断して集計する。
        // subscriptionStatusLabel()がCashierのsubscribed()/onGenericTrial()を
        // 呼ぶ際にN+1にならないよう、subscriptionsをeager loadしておく。
        $organizations = Organization::withCount([
            'users',
            'inquiries' => fn ($query) => $query->withoutGlobalScope(OrganizationScope::class),
        ])
            ->with('subscriptions')
            ->orderByDesc('contracted_at')
            ->paginate(20);

        $usageByOrganization = $organizations->mapWithKeys(
            fn (Organization $organization) => [$organization->id => $storageUsage->usedBytes($organization)]
        );

        return view('admin.dashboard', [
            'organizations' => $organizations,
            'usageByOrganization' => $usageByOrganization,
        ]);
    }
}
