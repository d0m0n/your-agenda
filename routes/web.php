<?php

use App\Http\Controllers\AgendaItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ObserverUserController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isSuperAdmin() ? 'admin.dashboard' : 'dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 資料の閲覧・ダウンロードはobserverも可能なため、管理系(can:manage)グループの外に置く
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // 会議一覧・メンバー一覧の閲覧もobserverに開放する(作成・編集・削除・CSV入出力は
    // 引き続き can:manage 側のみ。ビュー側で @can('manage') により操作系UIを隠す)
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
});

Route::middleware(['auth', 'can:manage'])->group(function () {
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/csv-template', [MemberController::class, 'csvTemplate'])->name('members.csv-template');
    Route::get('/members/export', [MemberController::class, 'export'])->name('members.export');
    Route::post('/members/import', [MemberController::class, 'import'])->name('members.import');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::post('/meetings/{meeting}/sites', [SiteController::class, 'storeForMeeting'])->name('meetings.sites.store');
    Route::put('/meetings/{meeting}/sites/{site}', [SiteController::class, 'updateForMeeting'])->name('meetings.sites.update');

    Route::post('/meetings/{meeting}/agenda-items', [AgendaItemController::class, 'store'])->name('agenda-items.store');
    Route::post('/meetings/{meeting}/agenda-items/copy', [AgendaItemController::class, 'copyFromMeeting'])->name('agenda-items.copy');
    Route::put('/meetings/{meeting}/agenda-items/{agendaItem}', [AgendaItemController::class, 'update'])->name('agenda-items.update');
    Route::delete('/meetings/{meeting}/agenda-items/{agendaItem}', [AgendaItemController::class, 'destroy'])->name('agenda-items.destroy');
    Route::post('/meetings/{meeting}/agenda-items/{agendaItem}/move-up', [AgendaItemController::class, 'moveUp'])->name('agenda-items.move-up');
    Route::post('/meetings/{meeting}/agenda-items/{agendaItem}/move-down', [AgendaItemController::class, 'moveDown'])->name('agenda-items.move-down');

    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    Route::get('/settings', [OrganizationSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [OrganizationSettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/export', [OrganizationSettingsController::class, 'export'])->name('settings.export');

    Route::get('/observers', [ObserverUserController::class, 'index'])->name('observers.index');
    Route::get('/observers/create', [ObserverUserController::class, 'create'])->name('observers.create');
    Route::post('/observers', [ObserverUserController::class, 'store'])->name('observers.store');
    Route::get('/observers/{observer}/edit', [ObserverUserController::class, 'edit'])->name('observers.edit');
    Route::put('/observers/{observer}', [ObserverUserController::class, 'update'])->name('observers.update');
    Route::delete('/observers/{observer}', [ObserverUserController::class, 'destroy'])->name('observers.destroy');
});

// 会議画面はobserverも閲覧できるため、管理系(can:manage)グループの外に置く。
// /meetings/create 等の固定セグメントより後に登録し、{meeting}に吸収されないようにする。
Route::middleware('auth')->group(function () {
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
