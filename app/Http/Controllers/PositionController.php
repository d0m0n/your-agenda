<?php

namespace App\Http\Controllers;

use App\Http\Requests\PositionRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::orderBy('serial_number')->get();

        return view('positions.index', ['positions' => $positions]);
    }

    public function create(): View
    {
        return view('positions.create', ['nextSerialNumber' => $this->nextAvailableSerialNumber()]);
    }

    public function store(PositionRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return redirect()->route('positions.index')->with('status', '役職を登録しました。');
    }

    public function edit(Position $position): View
    {
        return view('positions.edit', ['position' => $position, 'nextSerialNumber' => null]);
    }

    public function update(PositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()->route('positions.index')->with('status', '役職を更新しました。');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('positions.index')->with('status', '役職を削除しました。');
    }

    /**
     * Smallest positive integer not yet used as a serial_number in this organization.
     */
    private function nextAvailableSerialNumber(): int
    {
        $usedNumbers = Position::orderBy('serial_number')->pluck('serial_number');

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
