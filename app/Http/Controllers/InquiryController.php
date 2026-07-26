<?php

namespace App\Http\Controllers;

use App\Http\Requests\InquiryRequest;
use App\Mail\NewInquiryReceived;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function store(InquiryRequest $request): RedirectResponse
    {
        $inquiry = Inquiry::create([
            'user_id' => $request->user()->id,
            'category' => $request->validated('category'),
            'subject' => $request->validated('subject'),
            'body' => $request->validated('body'),
        ]);

        // 管理者パネルを定期的に見に行かなくても新着に気づけるよう、運営者へ
        // メールで知らせる。宛先が未設定の組織・環境では何もしない。
        if (config('inquiry.notify_email')) {
            Mail::to(config('inquiry.notify_email'))->send(new NewInquiryReceived($inquiry));
        }

        return back()->with('status', __('お問い合わせを送信しました。ご連絡ありがとうございます。'));
    }
}
