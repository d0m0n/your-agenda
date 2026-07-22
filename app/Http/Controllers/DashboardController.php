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

        return view('dashboard', [
            'organization' => $organization,
            'meetings' => $meetings,
            'birthdayMembers' => $birthdayMembers,
            'materials' => $materials,
        ]);
    }
}
