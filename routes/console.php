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

// 日次バックアップ(spatie/laravel-backup)。DB+storage/app/public・
// storage/app/privateを1つのzipにまとめてstorage/app/backups
// (publicから見えない専用ディスク)に保存する。サーバー外への退避は、
// この方法とは別にローカルMac等からrsync/scpで定期的に引き揚げる運用を
// 想定している(CLAUDE.mdの「バックアップ」参照)。backup:clean/
// backup:monitorは容量超過・失敗時に通知メールを送るだけで失敗しても
// 後続のスケジュールを止めないよう、それぞれ独立に登録する。
Schedule::command('backup:run')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('04:00')->withoutOverlapping();

// トライアル終了・解約でアクセス権を失った組織の猶予期間管理・自動削除
// (CLAUDE.mdの「退会処理」参照)。
Schedule::command('organizations:process-retention')->dailyAt('06:00')->withoutOverlapping();
