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

// 日次バックアップ(spatie/laravel-backup)。DB+storage/app/publicを1つの
// zipにまとめてstorage/app/private/Backup(publicから見えない場所)に保存する。
// サーバー外への退避は、この方法とは別にローカルMac等からrsync/scpで
// 定期的に引き揚げる運用を想定している(CLAUDE.mdの「バックアップ」参照)。
// backup:clean/backup:monitorは容量超過・失敗時に通知メールを送るだけで
// 失敗しても後続のスケジュールを止めないよう、それぞれ独立に登録する。
Schedule::command('backup:run')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('04:00')->withoutOverlapping();
