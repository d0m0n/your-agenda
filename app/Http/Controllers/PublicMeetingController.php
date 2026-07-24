<?php

namespace App\Http\Controllers;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ログイン不要で公開次第を閲覧するための窓口。Wi-Fi情報・メモなど
 * 組織メンバー限定の情報は含めない。{meeting}はpublic_tokenで解決される
 * (routes/web.phpの {meeting:public_token} ルートバインディング)。
 * 発行元組織が未契約(トライアル終了・無償提供なし)の場合は、次第本体・
 * 議案ファイル・資料のいずれも閲覧不可にする。
 */
class PublicMeetingController extends Controller
{
    public function show(Meeting $meeting): View
    {
        $meeting->load([
            'organization',
            'topLevelAgendaItems.member',
            'topLevelAgendaItems.children.member',
        ]);

        if (! $meeting->organization->hasActiveAccess()) {
            return view('meetings.public-unavailable');
        }

        return view('meetings.public-show', ['meeting' => $meeting]);
    }

    public function downloadMaterial(Meeting $meeting, Material $material): StreamedResponse
    {
        abort_unless($meeting->organization->hasActiveAccess(), 404);

        $isLinkedToThisMeeting = AgendaItem::where('meeting_id', $meeting->id)
            ->where('material_id', $material->id)
            ->exists();

        abort_unless($isLinkedToThisMeeting, 404);

        return Storage::disk('local')->download($material->file_path, $material->original_filename);
    }

    public function openSite(Meeting $meeting, Site $site): RedirectResponse
    {
        abort_unless($meeting->organization->hasActiveAccess(), 404);

        $isLinkedToThisMeeting = AgendaItem::where('meeting_id', $meeting->id)
            ->where('site_id', $site->id)
            ->exists();

        abort_unless($isLinkedToThisMeeting, 404);

        return redirect()->to($site->publicUrl());
    }
}
