<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Mail\TrialEndingSoonMail;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * 無料トライアルの終了が3日後に迫っている組織の一般ユーザー全員に、
 * リマインドメールを送る(1組織につき1回だけ。trial_ending_reminder_sent_atで
 * 重複送信を防ぐ)。毎日1回、スケジューラ(routes/console.php)から実行する想定。
 */
class SendTrialEndingReminders extends Command
{
    private const REMINDER_DAYS_BEFORE = 3;

    protected $signature = 'trials:send-ending-reminders';

    protected $description = 'トライアル終了が3日後に迫っている組織へリマインドメールを送る';

    public function handle(): int
    {
        $targetDate = now()->addDays(self::REMINDER_DAYS_BEFORE)->toDateString();

        $organizations = Organization::query()
            ->whereNotNull('trial_ends_at')
            ->whereNull('trial_ending_reminder_sent_at')
            ->where('free_access_enabled', false)
            ->whereDate('trial_ends_at', $targetDate)
            ->get()
            // すでに契約済み(トライアル終了前に前倒しで支払い済み)の組織には送らない。
            ->filter(fn (Organization $organization) => $organization->onGenericTrial() && ! $organization->subscribed('default'));

        foreach ($organizations as $organization) {
            $recipients = $organization->users()->where('role', UserRole::General)->get();

            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(new TrialEndingSoonMail($organization));
            }

            $organization->forceFill(['trial_ending_reminder_sent_at' => now()])->save();

            $this->info("組織「{$organization->name}」(id={$organization->id})へリマインドメールを送信しました({$recipients->count()}件)。");
        }

        return self::SUCCESS;
    }
}
