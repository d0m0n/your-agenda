<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetingRequest;
use App\Models\Material;
use App\Models\Meeting;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    private const SORTABLE_COLUMNS = ['name', 'held_at', 'location'];

    public function index(Request $request): View
    {
        $sort = $request->get('sort', 'held_at');
        if (! in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'held_at';
        }
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $meetings = Meeting::orderBy($sort, $direction)->paginate(20)->withQueryString();

        return view('meetings.index', ['meetings' => $meetings, 'sort' => $sort, 'direction' => $direction]);
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

    private const AGENDA_ITEM_RELATIONS = [
        'organization',
        'topLevelAgendaItems.member.position',
        'topLevelAgendaItems.site',
        'topLevelAgendaItems.material',
        'topLevelAgendaItems.children.member.position',
        'topLevelAgendaItems.children.site',
        'topLevelAgendaItems.children.material',
    ];

    public function show(Meeting $meeting): View
    {
        $meeting->load(self::AGENDA_ITEM_RELATIONS);

        return view('meetings.show', ['meeting' => $meeting]);
    }

    public function edit(Meeting $meeting, Request $request): View
    {
        $meeting->load(self::AGENDA_ITEM_RELATIONS);
        $members = $meeting->organization->members()->with('position')->orderBy('name')->get();
        $sites = $meeting->sites;
        $materials = Material::orderBy('title')->get();

        $pastMeetings = Meeting::where('id', '!=', $meeting->id)
            ->whereHas('agendaItems', fn ($query) => $query->whereNull('parent_id'))
            ->orderByDesc('held_at')
            ->get(['id', 'name', 'held_at']);

        $copySourceMeeting = null;
        $copyCandidates = collect();

        if ($request->filled('copy_from')) {
            $copySourceMeeting = Meeting::with([
                'topLevelAgendaItems.member.position',
                'topLevelAgendaItems.children.member.position',
            ])->find($request->integer('copy_from'));
            $copyCandidates = $copySourceMeeting?->topLevelAgendaItems ?? collect();
        }

        return view('meetings.edit', [
            'meeting' => $meeting, 'members' => $members, 'sites' => $sites, 'materials' => $materials,
            'pastMeetings' => $pastMeetings,
            'copySourceMeeting' => $copySourceMeeting,
            'copyCandidates' => $copyCandidates,
        ]);
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
