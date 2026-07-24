<?php

namespace App\Http\Controllers;

use App\Http\Requests\InquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;

class InquiryController extends Controller
{
    public function store(InquiryRequest $request): RedirectResponse
    {
        Inquiry::create([
            'user_id' => $request->user()->id,
            'category' => $request->validated('category'),
            'subject' => $request->validated('subject'),
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', __('お問い合わせを送信しました。ご連絡ありがとうございます。'));
    }
}
