<?php

namespace App\Http\Controllers;

use App\Services\MeetingInvitationTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationInvitationTemplateController extends Controller
{
    public function update(Request $request, MeetingInvitationTemplateService $templates): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', function ($attribute, $value, $fail) use ($templates) {
                if (! $templates->isValidType($value)) {
                    $fail('種類が不正です。');
                }
            }],
            'body' => ['required', 'string'],
        ]);

        $request->user()->organization->update([
            'invitation_'.$validated['type'].'_template' => $validated['body'],
        ]);

        return redirect()->route('settings.edit')->with('status', '案内文のデフォルトを保存しました。');
    }

    public function reset(Request $request, string $type, MeetingInvitationTemplateService $templates): RedirectResponse
    {
        abort_unless($templates->isValidType($type), 404);

        $request->user()->organization->update([
            'invitation_'.$type.'_template' => null,
        ]);

        return redirect()->route('settings.edit')->with('status', '組み込みの既定テンプレートに戻しました。');
    }
}
