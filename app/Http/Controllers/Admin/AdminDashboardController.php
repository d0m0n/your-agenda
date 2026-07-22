<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\StorageUsageService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(StorageUsageService $storageUsage): View
    {
        $organizations = Organization::withCount('users')
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
