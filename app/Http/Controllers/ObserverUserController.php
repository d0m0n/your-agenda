<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ObserverRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObserverUserController extends Controller
{
    public function index(): View
    {
        $observers = User::where('organization_id', auth()->user()->organization_id)
            ->where('role', UserRole::Observer)
            ->orderBy('name')
            ->get();

        return view('observers.index', ['observers' => $observers]);
    }

    public function create(): View
    {
        return view('observers.create');
    }

    public function store(ObserverRequest $request): RedirectResponse
    {
        User::create([
            'organization_id' => $request->user()->organization_id,
            'role' => UserRole::Observer,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        return redirect()->route('observers.index')->with('status', 'オブザーブユーザーを作成しました。');
    }

    public function edit(User $observer): View
    {
        $this->ensureIsObserver($observer);

        return view('observers.edit', ['observer' => $observer]);
    }

    public function update(ObserverRequest $request, User $observer): RedirectResponse
    {
        $this->ensureIsObserver($observer);

        $data = [
            'name' => $request->string('name'),
            'email' => $request->string('email'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password'));
        }

        $observer->update($data);

        return redirect()->route('observers.index')->with('status', 'オブザーブユーザーを更新しました。');
    }

    public function destroy(User $observer): RedirectResponse
    {
        $this->ensureIsObserver($observer);

        $observer->delete();

        return redirect()->route('observers.index')->with('status', 'オブザーブユーザーを削除しました。');
    }

    private function ensureIsObserver(User $observer): void
    {
        if ($observer->role !== UserRole::Observer || $observer->organization_id !== auth()->user()->organization_id) {
            throw new NotFoundHttpException;
        }
    }
}
