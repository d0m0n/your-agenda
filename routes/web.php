<?php

use App\Http\Controllers\AgendaItemController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingInvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ObserverUserController;
use App\Http\Controllers\OrganizationInvitationTemplateController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMeetingController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isSuperAdmin() ? 'admin.dashboard' : 'dashboard');
});

Route::get('/lp', fn () => view('welcome'))->name('lp');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'subscribed'])->name('dashboard');

// 14日間の無料トライアル終了後(またはサブスクリプション未契約時)のペイウォール。
// subscribedミドルウェアの対象外にする(対象にすると無限リダイレクトになる)。
Route::middleware('auth')->group(function () {
    Route::get('/billing', [BillingController::class, 'show'])->name('billing.paywall');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
});

// 支払い操作(Stripe Checkoutの開始)は一般ユーザーのみ行える。
Route::middleware(['auth', 'can:manage'])->group(function () {
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
});

Route::middleware(['auth', 'subscribed'])->group(function () {
    // 資料の閲覧・ダウンロードはobserverも可能なため、管理系(can:manage)グループの外に置く
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // メンバー一覧の閲覧もobserverに開放する(作成・編集・削除・CSV入出力は
    // 引き続き can:manage 側のみ。ビュー側で @can('manage') により操作系UIを隠す)
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    // 問い合わせ・不具合報告・機能要望の送信フォーム。一般/オブザーブ両方が
    // 送信できるため can:manage の外に置く(super_adminはInquiryRequestで拒否)。
    Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
});

// 会議一覧は、解約・トライアル終了後も次第を閲覧し続けられるようにする
// (新規作成・編集・削除は引き続きcan:manage+subscribedグループ側でブロックされる)
// ため、subscribedミドルウェアを外している。
Route::middleware('auth')->group(function () {
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');

    // 議案ファイル(sites)は展開後の中身をWebサーバーが直接配信する静的ファイルのため
    // ルート/ミドルウェアを経由しない。「開く入口」だけをここでラップし、
    // subscribedミドルウェアで未契約時はペイウォールへリダイレクトさせる。
    Route::middleware('subscribed')->get('/sites/{site}/open', [SiteController::class, 'open'])->name('sites.open');
});

// 解約・トライアル終了後もデータを持ち出せるよう、一括/個別ダウンロードは
// subscribedミドルウェアの対象外にする(CLAUDE.mdの解約時データ持ち出し要件)。
Route::middleware(['auth', 'can:manage'])->group(function () {
    Route::get('/settings/export', [OrganizationSettingsController::class, 'export'])->name('settings.export');
    Route::get('/meetings/{meeting}/export', [MeetingController::class, 'export'])->name('meetings.export');
});

Route::middleware(['auth', 'can:manage', 'subscribed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
    Route::get('/meetings/{meeting}/agenda', [MeetingController::class, 'agenda'])->name('meetings.agenda');
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
    Route::post('/meetings/{meeting}/public-link', [MeetingController::class, 'enablePublicLink'])->name('meetings.public-link.enable');
    Route::delete('/meetings/{meeting}/public-link', [MeetingController::class, 'disablePublicLink'])->name('meetings.public-link.disable');

    Route::get('/meetings/{meeting}/invitation', [MeetingInvitationController::class, 'edit'])->name('meetings.invitation.edit');
    Route::put('/meetings/{meeting}/invitation', [MeetingInvitationController::class, 'update'])->name('meetings.invitation.update');
    Route::delete('/meetings/{meeting}/invitation/{type}', [MeetingInvitationController::class, 'reset'])->name('meetings.invitation.reset');
    Route::get('/meetings/{meeting}/invitation/pdf', [MeetingInvitationController::class, 'pdf'])->name('meetings.invitation.pdf');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

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
    // settings.export(次第の一括ダウンロード)はsubscribedミドルウェア対象外の
    // 別グループに切り出し済み(上部参照。解約・トライアル終了後もデータを持ち出せるように)。

    Route::put('/settings/invitation-templates', [OrganizationInvitationTemplateController::class, 'update'])->name('settings.invitation-templates.update');
    Route::delete('/settings/invitation-templates/{type}', [OrganizationInvitationTemplateController::class, 'reset'])->name('settings.invitation-templates.reset');

    Route::get('/observers', [ObserverUserController::class, 'index'])->name('observers.index');
    Route::get('/observers/create', [ObserverUserController::class, 'create'])->name('observers.create');
    Route::post('/observers', [ObserverUserController::class, 'store'])->name('observers.store');
    Route::get('/observers/{observer}/edit', [ObserverUserController::class, 'edit'])->name('observers.edit');
    Route::put('/observers/{observer}', [ObserverUserController::class, 'update'])->name('observers.update');
    Route::delete('/observers/{observer}', [ObserverUserController::class, 'destroy'])->name('observers.destroy');
});

// 会議詳細(次第)はobserverも閲覧できるうえ、解約・トライアル終了後も
// 閲覧だけは可能にするため、subscribedミドルウェアを外している
// (次第にリンクされた議案データ自体はsites.open/materials.download側でブロックされる)。
// /meetings/create 等の固定セグメントより後に登録し、{meeting}に吸収されないようにする。
Route::middleware('auth')->group(function () {
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
});

// メンバー詳細はobserverも閲覧できるため、管理系(can:manage)グループの外に置く。
// 会議一覧・メンバー一覧の並びに合わせて今回のスコープでは引き続きsubscribed対象。
Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
});

// 次第の外部共有用リンク。ログイン不要でアクセスできる(public_tokenが
// 十分に推測困難なUUIDのため)。{meeting}はpublic_tokenで解決する。発行元組織が
// 未契約(トライアル終了・無償提供なし)の場合は、次第本体・議案ファイル・資料の
// いずれも閲覧不可にする(PublicMeetingController参照)。
Route::get('/s/meetings/{meeting:public_token}', [PublicMeetingController::class, 'show'])->name('public.meetings.show');
Route::get('/s/meetings/{meeting:public_token}/materials/{material}', [PublicMeetingController::class, 'downloadMaterial'])->name('public.meetings.materials.download');
Route::get('/s/meetings/{meeting:public_token}/sites/{site}', [PublicMeetingController::class, 'openSite'])->name('public.meetings.sites.open');

// StripeからのWebhook。認証もsubscribedチェックも行わない
// (Cashier標準のWebhookControllerが署名検証を行う)。
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
