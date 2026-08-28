<?php

namespace App\Http\Controllers;

use App\Exceptions\MinutesGenerationException;
use App\Models\Meeting;
use App\Services\ClaudeMinutesGenerator;
use App\Services\MeetingMinutesContextBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingMinutesController extends Controller
{
    public function edit(Meeting $meeting): View
    {
        $meeting->load('organization');

        return view('meetings.minutes', ['meeting' => $meeting]);
    }

    public function generate(
        Request $request,
        Meeting $meeting,
        MeetingMinutesContextBuilder $contextBuilder,
        ClaudeMinutesGenerator $generator,
    ): RedirectResponse {
        $validated = $request->validate([
            'transcript' => ['required', 'string', 'max:200000'],
        ]);

        // 本番(さくらのレンタルサーバー)にはキューワーカーが常駐していないため、
        // 議事録生成は1リクエスト内で同期的に完結させる。set_time_limit()は
        // PHP自体の実行時間上限のみを変更するもので、Apache側のタイムアウトには
        // 効果が無い(root権限が無く確認・変更できない)。本番での実測確認が必須
        // (CLAUDE.mdの「AI議事録生成」参照)。
        set_time_limit((int) config('claude.timeout_seconds') + 30);

        $context = $contextBuilder->build($meeting);

        try {
            $minutesBody = $generator->generate($validated['transcript'], $context['content_blocks']);
        } catch (MinutesGenerationException $e) {
            return back()->withInput()->withErrors(['transcript' => $e->getMessage()]);
        }

        $meeting->update([
            'minutes_transcript' => $validated['transcript'],
            'minutes_body' => $minutesBody,
            'minutes_generated_at' => now(),
        ]);

        return redirect()->route('meetings.minutes.edit', $meeting)
            ->with('status', '議事録を生成しました。内容を確認・編集してください。')
            ->with('skippedAttachments', $context['skipped']);
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string']]);

        $meeting->update(['minutes_body' => $validated['body']]);

        return redirect()->route('meetings.minutes.edit', $meeting)->with('status', '議事録を保存しました。');
    }

    public function pdf(Meeting $meeting): View
    {
        abort_unless($meeting->minutes_body, 404);

        $meeting->load('organization');

        return view('meetings.minutes-pdf', ['meeting' => $meeting]);
    }
}
