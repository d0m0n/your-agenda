<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// トライアル終了が近い組織へのリマインドメール。本番のcron(schedule:run、
// CLAUDE.mdの「本番環境の制約」参照)が毎日この時刻にまとめて拾う。
Schedule::command('trials:send-ending-reminders')->dailyAt('09:00');
