<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        $meetings = Meeting::orderByDesc('held_at')->take(5)->get();

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
