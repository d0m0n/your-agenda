<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::orderBy('serial_number')->get();

        return view('departments.index', ['departments' => $departments]);
    }

    public function create(): View
    {
        return view('departments.create', ['nextSerialNumber' => $this->nextAvailableSerialNumber()]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return redirect()->route('departments.index')->with('status', '部署を登録しました。');
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', ['department' => $department, 'nextSerialNumber' => null]);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('departments.index')->with('status', '部署を更新しました。');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('departments.index')->with('status', '部署を削除しました。');
    }

    /**
     * Smallest positive integer not yet used as a serial_number in this organization.
     */
    private function nextAvailableSerialNumber(): int
    {
        $usedNumbers = Department::orderBy('serial_number')->pluck('serial_number');

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
