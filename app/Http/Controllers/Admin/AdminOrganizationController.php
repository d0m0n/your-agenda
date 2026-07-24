<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationDataPurgeService;
use App\Services\StorageUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminOrganizationController extends Controller
{
    public function show(Organization $organization, StorageUsageService $storageUsage): View
    {
        $users = $organization->users()->orderBy('role')->orderBy('name')->get();

        return view('admin.organizations.show', [
            'organization' => $organization,
            'users' => $users,
            'usedBytes' => $storageUsage->usedBytes($organization),
        ]);
    }

    public function updateQuota(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->ensureBelongsToOrganization($organization, $user);

        if (! $user->isGeneral()) {
            throw new NotFoundHttpException;
        }

        $data = $request->validate([
            'storage_quota_gb' => ['required', 'numeric', 'min:0.1', 'max:1000'],
        ]);

        $user->update(['storage_quota_bytes' => (int) round($data['storage_quota_gb'] * 1024 * 1024 * 1024)]);

        return redirect()->route('admin.organizations.show', $organization)->with('status', "{$user->name} の割り当て容量を更新しました。");
    }

    public function toggleFreeAccess(Organization $organization): RedirectResponse
    {
        $organization->update(['free_access_enabled' => ! $organization->free_access_enabled]);

        $message = $organization->free_access_enabled
            ? '無償提供モードを有効にしました。'
            : '無償提供モードを無効にしました。';

        return redirect()->route('admin.organizations.show', $organization)->with('status', $message);
    }

    public function destroyData(Organization $organization, OrganizationDataPurgeService $purger): RedirectResponse
    {
        $purger->purgeUploadedFiles($organization);

        return redirect()->route('admin.organizations.show', $organization)->with('status', 'アップロード済みデータを削除しました。');
    }

    public function destroyUser(Organization $organization, User $user): RedirectResponse
    {
        $this->ensureBelongsToOrganization($organization, $user);

        $user->delete();

        return redirect()->route('admin.organizations.show', $organization)->with('status', "{$user->name} のアカウントを削除しました。");
    }

    private function ensureBelongsToOrganization(Organization $organization, User $user): void
    {
        if ($user->role === UserRole::SuperAdmin || $user->organization_id !== $organization->id) {
            throw new NotFoundHttpException;
        }
    }
}
