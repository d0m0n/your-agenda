<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    /**
     * 一般ユーザー・オブザーブユーザーそれぞれ向けの取扱説明書を表示する。
     * ロールに応じて表示するセクションを切り替えるだけで、URL・入口は共通。
     */
    public function index(): View
    {
        $manual = auth()->user()->isObserver() ? 'observer' : 'general';

        return view('help.index', ['manual' => $manual]);
    }
}
