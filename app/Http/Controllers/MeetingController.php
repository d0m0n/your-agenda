<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetingRequest;
use App\Models\Meeting;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(): View
    {
        $meetings = Meeting::orderByDesc('held_at')->paginate(20);

        return view('meetings.index', ['meetings' => $meetings]);
    }

    public function create(): View
    {
        return view('meetings.create');
    }

    public function store(MeetingRequest $request, ImageUploadService $imageUploader): RedirectResponse
    {
        $data = $request->validated();

        if ($image = $request->file('header_image')) {
            $data['header_image_path'] = $imageUploader->store($image, 'meetings');
        }
        unset($data['header_image']);

        $meeting = Meeting::create($data);

        return redirect()->route('meetings.edit', $meeting)->with('status', '会議を登録しました。続けて次第を追加できます。');
    }

    public function show(Meeting $meeting): View
    {
        $meeting->load(['agendaItems.member', 'agendaItems.site']);

        return view('meetings.show', ['meeting' => $meeting]);
    }

    public function edit(Meeting $meeting): View
    {
        $meeting->load(['agendaItems.member', 'agendaItems.site']);
        $members = $meeting->organization->members()->orderBy('name')->get();
        $sites = $meeting->sites;

        return view('meetings.edit', ['meeting' => $meeting, 'members' => $members, 'sites' => $sites]);
    }

    public function update(MeetingRequest $request, Meeting $meeting, ImageUploadService $imageUploader): RedirectResponse
    {
        $data = $request->validated();

        if ($image = $request->file('header_image')) {
            $imageUploader->delete($meeting->header_image_path);
            $data['header_image_path'] = $imageUploader->store($image, 'meetings');
        }
        unset($data['header_image']);

        $meeting->update($data);

        return redirect()->route('meetings.edit', $meeting)->with('status', '会議情報を更新しました。');
    }

    public function destroy(Meeting $meeting, ImageUploadService $imageUploader): RedirectResponse
    {
        $imageUploader->delete($meeting->header_image_path);
        $meeting->delete();

        return redirect()->route('meetings.index')->with('status', '会議を削除しました。');
    }
}
