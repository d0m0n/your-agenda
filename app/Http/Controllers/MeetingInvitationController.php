<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Services\MeetingInvitationTemplateService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingInvitationController extends Controller
{
    public function edit(Meeting $meeting, MeetingInvitationTemplateService $templates): View
    {
        $meeting->load(['organization', 'topLevelAgendaItems']);

        $invitationBodies = [
            'pdf' => $meeting->invitation_pdf_body ?? $templates->template($meeting, 'pdf'),
            'email' => $meeting->invitation_email_body ?? $templates->template($meeting, 'email'),
            'line' => $meeting->invitation_line_body ?? $templates->template($meeting, 'line'),
        ];

        return view('meetings.invitation', ['meeting' => $meeting, 'invitationBodies' => $invitationBodies]);
    }

    public function update(Request $request, Meeting $meeting, MeetingInvitationTemplateService $templates): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', function ($attribute, $value, $fail) use ($templates) {
                if (! $templates->isValidType($value)) {
                    $fail('種類が不正です。');
                }
            }],
            'body' => ['required', 'string'],
        ]);

        $meeting->update([
            'invitation_'.$validated['type'].'_body' => $validated['body'],
        ]);

        return redirect()->route('meetings.invitation.edit', $meeting)->with('status', '案内文を保存しました。');
    }

    public function reset(Meeting $meeting, string $type, MeetingInvitationTemplateService $templates): RedirectResponse
    {
        abort_unless($templates->isValidType($type), 404);

        $meeting->update(['invitation_'.$type.'_body' => null]);

        return redirect()->route('meetings.invitation.edit', $meeting)->with('status', 'テンプレートに戻しました。');
    }

    public function pdf(Meeting $meeting, MeetingInvitationTemplateService $templates, QrCodeService $qrCodes): View
    {
        $body = $meeting->invitation_pdf_body ?? $templates->template($meeting, 'pdf');

        $meeting->load('organization');

        $mapQrCodeDataUri = $meeting->venue_map_url ? $qrCodes->dataUri($meeting->venue_map_url) : null;

        return view('meetings.invitation-pdf', [
            'meeting' => $meeting,
            'body' => $body,
            'mapQrCodeDataUri' => $mapQrCodeDataUri,
            'issueDate' => now()->format('Y年n月').'吉日',
        ]);
    }
}
