<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Sums the on-disk footprint of everything an organization has uploaded:
 * materials, site files (Zip/PDF/image), and the header/icon/photo images
 * for the organization, its meetings, and its members. Always queries by
 * an explicit organization_id with the multi-tenant scope removed, so it
 * gives correct results whether the caller is a general user looking at
 * their own organization or a super admin looking at someone else's.
 */
class StorageUsageService
{
    public function usedBytes(Organization $organization): int
    {
        return $this->materialsBytes($organization)
            + $this->sitesBytes($organization)
            + $this->organizationImageBytes($organization)
            + $this->meetingImageBytes($organization)
            + $this->memberPhotoBytes($organization);
    }

    public function quotaBytes(User $user): int
    {
        return $user->storageQuotaBytes();
    }

    public function remainingBytes(User $user): int
    {
        if (! $user->organization) {
            return 0;
        }

        return max(0, $this->quotaBytes($user) - $this->usedBytes($user->organization));
    }

    public function wouldExceedQuota(User $user, int $additionalBytes): bool
    {
        if (! $user->organization) {
            return true;
        }

        return $this->usedBytes($user->organization) + $additionalBytes > $this->quotaBytes($user);
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === end($units)) {
                return number_format($value, $value < 10 ? 2 : 1).' '.$unit;
            }
            $value /= 1024;
        }

        return $bytes.' B'; // unreachable, keeps static analysis happy
    }

    private function materialsBytes(Organization $organization): int
    {
        return Material::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get()
            ->sum(fn (Material $material) => Storage::disk('local')->size($material->file_path) ?: 0);
    }

    private function sitesBytes(Organization $organization): int
    {
        return Site::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get()
            ->sum(fn (Site $site) => $this->bytesForSiteUuid($site->uuid));
    }

    /**
     * Public so callers replacing a site's file in place (same uuid, new
     * content) can measure the old and staged-new footprint separately and
     * work out the net change before it's applied.
     */
    public function bytesForSiteUuid(string $uuid): int
    {
        return $this->directoryBytes(storage_path("app/public/sites/{$uuid}"));
    }

    private function organizationImageBytes(Organization $organization): int
    {
        return $this->publicRelativeFileBytes($organization->header_image_path)
            + $this->publicRelativeFileBytes($organization->icon_image_path);
    }

    private function meetingImageBytes(Organization $organization): int
    {
        return Meeting::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get()
            ->sum(fn (Meeting $meeting) => $this->publicRelativeFileBytes($meeting->header_image_path));
    }

    private function memberPhotoBytes(Organization $organization): int
    {
        return Member::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get()
            ->sum(fn (Member $member) => $this->publicRelativeFileBytes($member->photo_path));
    }

    private function publicRelativeFileBytes(?string $relativePath): int
    {
        if (! $relativePath) {
            return 0;
        }

        return Storage::disk('public')->exists($relativePath)
            ? (Storage::disk('public')->size($relativePath) ?: 0)
            : 0;
    }

    private function directoryBytes(string $absolutePath): int
    {
        if (! File::isDirectory($absolutePath)) {
            return 0;
        }

        return collect(File::allFiles($absolutePath))->sum(fn ($file) => $file->getSize());
    }
}
