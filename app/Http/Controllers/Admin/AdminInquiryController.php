<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryCategory;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inquiry::withoutGlobalScope(OrganizationScope::class)
            ->with(['organization', 'user']);

        if ($request->query('status') === 'handled') {
            $query->whereNotNull('handled_at');
        } elseif ($request->query('status') === 'unhandled') {
            $query->whereNull('handled_at');
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($keyword = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('subject', 'like', "%{$keyword}%")
                    ->orWhere('body', 'like', "%{$keyword}%")
                    ->orWhereHas('organization', fn ($oq) => $oq->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%"));
            });
        }

        $inquiries = $query->latest()->paginate(20)->withQueryString();

        $unhandledCount = Inquiry::withoutGlobalScope(OrganizationScope::class)->whereNull('handled_at')->count();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'categories' => InquiryCategory::cases(),
            'unhandledCount' => $unhandledCount,
            'filters' => $request->only(['status', 'category', 'q']),
        ]);
    }

    public function toggleHandled(int $inquiry): RedirectResponse
    {
        $inquiry = Inquiry::withoutGlobalScope(OrganizationScope::class)->findOrFail($inquiry);

        // handled_atはユーザー投稿フォームからは送信させないためFillableに
        // 含めていないので、この管理者専用の状態変更はforceFillで行う。
        $inquiry->forceFill(['handled_at' => $inquiry->isHandled() ? null : now()])->save();

        return back()->with('status', $inquiry->isHandled() ? __('対応済みにしました。') : __('未対応に戻しました。'));
    }
}
