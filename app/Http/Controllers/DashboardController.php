<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $organization = auth()->user()->organization;

        $meetings = Meeting::where('held_at', '>=', now()->startOfDay())
            ->orderBy('held_at')
            ->take(5)
            ->get();

        $birthdayMembers = Member::whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->get()
            ->sortBy(fn (Member $member) => $member->birth_date->day);

        $materials = Material::with('user')->latest()->take(5)->get();

        // 会議もメンバーも1件も無ければ、契約直後の未セットアップ組織とみなし
        // ダッシュボードに最初の一歩を案内するカードを出す(会議・次第の有無で
        // 判定し、開催日時の有無は問わない)。
        $isNewOrganization = $organization->members()->count() === 0 && Meeting::count() === 0;

        return view('dashboard', [
            'organization' => $organization,
            'meetings' => $meetings,
            'birthdayMembers' => $birthdayMembers,
            'materials' => $materials,
            'isNewOrganization' => $isNewOrganization,
        ]);
    }
}
