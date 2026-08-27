<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationSettingsRequest;
use App\Services\ImageUploadService;
use App\Services\MeetingArchiveExportService;
use App\Services\MeetingInvitationTemplateService;
use App\Services\StorageUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OrganizationSettingsController extends Controller
{
    public function edit(StorageUsageService $storageUsage, MeetingInvitationTemplateService $templates): View
    {
        $user = auth()->user();

        $invitationTemplates = [
            'pdf' => $templates->organizationTemplate($user->organization, 'pdf'),
            'email' => $templates->organizationTemplate($user->organization, 'email'),
            'line' => $templates->organizationTemplate($user->organization, 'line'),
        ];

        return view('settings.edit', [
            'organization' => $user->organization,
            'usedBytes' => $storageUsage->usedBytes($user->organization),
            'quotaBytes' => $storageUsage->quotaBytes($user),
            'invitationTemplates' => $invitationTemplates,
            'invitationPlaceholders' => MeetingInvitationTemplateService::PLACEHOLDERS,
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

        // Stripeに顧客レコードが存在する場合、組織名・請求先メールアドレスの
        // 変更をStripe側にも反映する(領収書・請求書メールの送付先として使われる)。
        // Stripe側の障害等で失敗しても、ここまでの設定保存自体は失わせない。
        if ($organization->hasStripeId()) {
            try {
                $organization->syncStripeCustomerDetails();
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('settings.edit')->with('status', '組織情報を更新しました。');
    }

    public function export(MeetingArchiveExportService $exporter): BinaryFileResponse
    {
        $organization = auth()->user()->organization;
        $zipPath = $exporter->export($organization);
        $filename = $organization->name.'_次第一括ダウンロード_'.now()->jst()->format('Ymd').'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
