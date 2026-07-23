<?php

namespace App\Http\Controllers;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ログイン不要で公開次第を閲覧するための窓口。Wi-Fi情報・メモなど
 * 組織メンバー限定の情報は含めない。{meeting}はpublic_tokenで解決される
 * (routes/web.phpの {meeting:public_token} ルートバインディング)。
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

        return view('meetings.public-show', ['meeting' => $meeting]);
    }

    public function downloadMaterial(Meeting $meeting, Material $material): StreamedResponse
    {
        $isLinkedToThisMeeting = AgendaItem::where('meeting_id', $meeting->id)
            ->where('material_id', $material->id)
            ->exists();

        abort_unless($isLinkedToThisMeeting, 404);

        return Storage::disk('local')->download($material->file_path, $material->original_filename);
    }
}
