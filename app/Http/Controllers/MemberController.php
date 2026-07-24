<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Services\ImageUploadService;
use App\Services\MemberCsvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MemberController extends Controller
{
    private const SORTABLE_COLUMNS = ['serial_number', 'name', 'name_kana', 'company', 'birth_date', 'position'];

    public function index(Request $request): View
    {
        $sort = $request->get('sort', 'name');
        if (! in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'name';
        }
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $query = Member::with(['position', 'department']);

        if ($sort === 'position') {
            $query->leftJoin('positions', 'positions.id', '=', 'members.position_id')
                ->orderBy('positions.serial_number', $direction)
                ->select('members.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $members = $query->paginate(20)->withQueryString();

        return view('members.index', ['members' => $members, 'sort' => $sort, 'direction' => $direction]);
    }

    public function show(Member $member): View
    {
        $member->load(['position', 'department', 'organization']);

        // 名刺めくり用に、氏名順での前後のメンバーを求める。
        $orderedIds = Member::orderBy('name')->orderBy('id')->pluck('id')->values();
        $currentIndex = $orderedIds->search($member->id);

        $previousMember = $currentIndex > 0 ? Member::find($orderedIds[$currentIndex - 1]) : null;
        $nextMember = $currentIndex < $orderedIds->count() - 1 ? Member::find($orderedIds[$currentIndex + 1]) : null;

        return view('members.show', [
            'member' => $member,
            'previousMember' => $previousMember,
            'nextMember' => $nextMember,
        ]);
    }

    public function create(): View
    {
        $positions = Position::orderBy('serial_number')->get();
        $departments = Department::orderBy('serial_number')->get();

        return view('members.create', [
            'positions' => $positions, 'departments' => $departments,
            'nextSerialNumber' => $this->nextAvailableSerialNumber(),
        ]);
    }

    public function store(MemberRequest $request, ImageUploadService $imageUploader): RedirectResponse
    {
        $data = $request->validated();

        if ($photo = $request->file('photo')) {
            $data['photo_path'] = $imageUploader->store($photo, 'members');
        }
        unset($data['photo']);

        Member::create($data);

        return redirect()->route('members.index')->with('status', 'メンバーを登録しました。');
    }

    public function edit(Member $member): View
    {
        $positions = Position::orderBy('serial_number')->get();
        $departments = Department::orderBy('serial_number')->get();

        return view('members.edit', [
            'member' => $member, 'positions' => $positions, 'departments' => $departments,
            'nextSerialNumber' => null,
        ]);
    }

    public function update(MemberRequest $request, Member $member, ImageUploadService $imageUploader): RedirectResponse
    {
        $data = $request->validated();

        if ($photo = $request->file('photo')) {
            $imageUploader->delete($member->photo_path);
            $data['photo_path'] = $imageUploader->store($photo, 'members');
        }
        unset($data['photo']);

        $member->update($data);

        return redirect()->route('members.index')->with('status', 'メンバー情報を更新しました。');
    }

    public function destroy(Member $member, ImageUploadService $imageUploader): RedirectResponse
    {
        $imageUploader->delete($member->photo_path);
        $member->delete();

        return redirect()->route('members.index')->with('status', 'メンバーを削除しました。');
    }

    public function csvTemplate(MemberCsvService $csv): Response
    {
        return $this->csvResponse($csv->template(), 'members_template.csv');
    }

    public function export(MemberCsvService $csv): Response
    {
        $members = Member::orderBy('name')->get();

        return $this->csvResponse($csv->export($members), 'members.csv');
    }

    public function import(Request $request, MemberCsvService $csv): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $csv->import($request->file('csv_file'), $request->user()->organization_id);

        $status = "{$result['created']}件のメンバーを登録しました。";
        if (! empty($result['skipped'])) {
            $status .= ' スキップ: '.collect($result['skipped'])
                ->map(fn ($s) => "{$s['row']}行目({$s['reason']})")
                ->implode(', ');
        }

        return redirect()->route('members.index')->with('status', $status);
    }

    private function csvResponse(string $csv, string $filename): Response
    {
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Smallest positive integer not yet used as a serial_number in this organization.
     */
    private function nextAvailableSerialNumber(): int
    {
        $usedNumbers = Member::whereNotNull('serial_number')->orderBy('serial_number')->pluck('serial_number');

        $candidate = 1;
        foreach ($usedNumbers as $number) {
            if ($number > $candidate) {
                break;
            }
            if ($number === $candidate) {
                $candidate++;
            }
        }

        return $candidate;
    }
}
