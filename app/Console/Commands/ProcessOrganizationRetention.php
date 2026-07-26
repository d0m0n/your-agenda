<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Mail\AccountScheduledForDeletion;
use App\Mail\OrganizationAutomaticallyDeleted;
use App\Models\Organization;
use App\Services\OrganizationDeleter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * トライアル終了・解約でhasActiveAccess()を失った組織のデータ保持を管理する
 * 日次バッチ(routes/console.phpから毎日実行)。
 *
 * 1. アクセスを新たに失った組織: access_lost_atを記録し、一般ユーザーへ
 *    削除予定日を知らせるメールを送る(猶予期間の開始)。
 * 2. 再契約等でアクセスが回復した組織: access_lost_atをnullに戻し、
 *    削除予定を解除する。
 * 3. 猶予期間(config('billing.deletion_grace_period_days'))が過ぎても
 *    アクセスが回復していない組織: 自動的に完全削除する(取り消し不可)。
 *
 * 無償提供モード(free_access_enabled)の組織は、hasActiveAccess()が
 * 常にtrueになるため対象外(このコマンドが特別扱いする必要はない)。
 */
class ProcessOrganizationRetention extends Command
{
    protected $signature = 'organizations:process-retention';

    protected $description = 'アクセス権を失った組織の猶予期間を管理し、期限切れの組織を自動削除する';

    public function handle(OrganizationDeleter $deleter): int
    {
        $this->startGracePeriods();
        $this->clearRestoredAccess();
        $this->deleteExpiredOrganizations($deleter);

        return self::SUCCESS;
    }

    private function startGracePeriods(): void
    {
        $organizations = Organization::query()
            ->whereNull('access_lost_at')
            ->get()
            ->filter(fn (Organization $organization) => ! $organization->hasActiveAccess());

        foreach ($organizations as $organization) {
            $organization->forceFill(['access_lost_at' => now()])->save();

            $deletionDate = now()->addDays((int) config('billing.deletion_grace_period_days'));

            $recipients = $organization->users()->where('role', UserRole::General)->get();
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(new AccountScheduledForDeletion($organization, $deletionDate));
            }

            $this->info("組織「{$organization->name}」(id={$organization->id})の猶予期間を開始しました(削除予定日: {$deletionDate->format('Y-m-d')})。");
        }
    }

    private function clearRestoredAccess(): void
    {
        $organizations = Organization::query()
            ->whereNotNull('access_lost_at')
            ->get()
            ->filter(fn (Organization $organization) => $organization->hasActiveAccess());

        foreach ($organizations as $organization) {
            $organization->forceFill(['access_lost_at' => null])->save();

            $this->info("組織「{$organization->name}」(id={$organization->id})のアクセスが回復したため、削除予定を解除しました。");
        }
    }

    private function deleteExpiredOrganizations(OrganizationDeleter $deleter): void
    {
        $organizations = Organization::query()
            ->whereNotNull('access_lost_at')
            ->get()
            ->filter(fn (Organization $organization) => $organization->isPastDeletionGracePeriod());

        foreach ($organizations as $organization) {
            $name = $organization->name;
            $id = $organization->id;
            $accessLostAt = $organization->access_lost_at;

            $deleter->delete($organization);

            if (config('error_alerts.mail_to')) {
                Mail::to(config('error_alerts.mail_to'))->send(
                    new OrganizationAutomaticallyDeleted($name, $id, $accessLostAt)
                );
            }

            $this->info("組織「{$name}」(id={$id})を猶予期間経過のため自動削除しました。");
        }
    }
}
