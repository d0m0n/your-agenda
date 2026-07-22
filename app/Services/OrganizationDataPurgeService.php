<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Frees up an organization's storage usage by deleting every uploaded file
 * (materials, site Zip/PDF/image files, header/icon/photo images) while
 * keeping the organization, its users, and its text records (meetings,
 * members, agenda items) intact. Used by the super admin panel to reclaim
 * space without deleting the tenant itself.
 */
class OrganizationDataPurgeService
{
    public function purgeUploadedFiles(Organization $organization): void
    {
        $this->purgeMaterials($organization);
        $this->purgeSites($organization);
        $this->purgeOrganizationImages($organization);
        $this->purgeMeetingImages($organization);
        $this->purgeMemberPhotos($organization);
    }

    private function purgeMaterials(Organization $organization): void
    {
        $materials = Material::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get();

        foreach ($materials as $material) {
            Storage::disk('local')->delete($material->file_path);
            $material->delete();
        }
    }

    private function purgeSites(Organization $organization): void
    {
        $sites = Site::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->get();

        foreach ($sites as $site) {
            File::deleteDirectory(storage_path("app/public/sites/{$site->uuid}"));
            $site->delete();
        }
    }

    private function purgeOrganizationImages(Organization $organization): void
    {
        $this->deletePublicFile($organization->header_image_path);
        $this->deletePublicFile($organization->icon_image_path);
        $organization->update(['header_image_path' => null, 'icon_image_path' => null]);
    }

    private function purgeMeetingImages(Organization $organization): void
    {
        Meeting::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->each(function (Meeting $meeting) {
                $this->deletePublicFile($meeting->header_image_path);
                $meeting->update(['header_image_path' => null]);
            });
    }

    private function purgeMemberPhotos(Organization $organization): void
    {
        Member::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organization->id)
            ->each(function (Member $member) {
                $this->deletePublicFile($member->photo_path);
                $member->update(['photo_path' => null]);
            });
    }

    private function deletePublicFile(?string $relativePath): void
    {
        if ($relativePath) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
