<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationSettingsRequest;
use App\Services\ImageUploadService;
use App\Services\MeetingArchiveExportService;
use App\Services\StorageUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrganizationSettingsController extends Controller
{
    public function edit(StorageUsageService $storageUsage): View
    {
        $user = auth()->user();

        return view('settings.edit', [
            'organization' => $user->organization,
            'usedBytes' => $storageUsage->usedBytes($user->organization),
            'quotaBytes' => $storageUsage->quotaBytes($user),
        ]);
    }

    public function update(OrganizationSettingsRequest $request, ImageUploadService $imageUploader): RedirectResponse
    {
        $organization = $request->user()->organization;
        $data = $request->validated();

        if ($image = $request->file('header_image')) {
            $imageUploader->delete($organization->header_image_path);
            $data['header_image_path'] = $imageUploader->store($image, 'organizations');
        }
        unset($data['header_image']);

        if ($icon = $request->file('icon_image')) {
            $imageUploader->delete($organization->icon_image_path);
            $data['icon_image_path'] = $imageUploader->store($icon, 'organizations');
        }
        unset($data['icon_image']);

        foreach (['show_meetings_pane', 'show_calendar_pane', 'show_birthday_pane', 'show_materials_pane'] as $pane) {
            $data[$pane] = $request->boolean($pane);
        }

        $organization->update($data);

        return redirect()->route('settings.edit')->with('status', '組織情報を更新しました。');
    }

    public function export(MeetingArchiveExportService $exporter): BinaryFileResponse
    {
        $organization = auth()->user()->organization;
        $zipPath = $exporter->export($organization);
        $filename = $organization->name.'_次第一括ダウンロード_'.now()->format('Ymd').'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
