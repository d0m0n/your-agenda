<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\OrganizationDataPurgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 退会依頼への対応(組織の完全削除)。UIからの操作経路はなく、super_admin
 * アカウント作成等と同様、サーバー上でのartisan実行のみで行う運用
 * (本人確認をSSHアクセス権限に委ねる)。
 *
 * 削除される: 組織・ユーザー(一般/オブザーブ)・メンバー・会議・次第・
 * 役職・部署・お問い合わせ・議案ファイル/資料/各種画像の実ファイル。
 * これらはmeetings/members/positions/departments/sites/users/inquiries
 * テーブルがorganization_idにcascadeOnDeleteを設定しているため、
 * Organizationレコードの削除だけで連鎖的に消える(実ファイルはDBカスケードの
 * 対象外のため、OrganizationDataPurgeServiceで別途削除する)。
 *
 * 削除されない: Stripe側の顧客情報そのもの(領収書・請求書等の会計記録は、
 * 税法上の保存義務との兼ね合いから、サブスクリプションを即時解約した上で
 * Stripe側にはあえて残す。完全に消したい場合は別途Stripeダッシュボードから
 * 手動対応する)。
 */
class DeleteOrganization extends Command
{
    protected $signature = 'organizations:delete {organization : 削除する組織のID} {--force : 確認をスキップする}';

    protected $description = '退会依頼を受けた組織を完全に削除する(取り消し不可)';

    public function handle(OrganizationDataPurgeService $purger): int
    {
        $organization = Organization::withCount(['users', 'meetings', 'members'])->find($this->argument('organization'));

        if (! $organization) {
            $this->error('指定されたIDの組織が見つかりません。');

            return self::FAILURE;
        }

        $this->warn('以下の組織を完全に削除します。この操作は取り消せません。');
        $this->table(
            ['項目', '内容'],
            [
                ['組織ID', $organization->id],
                ['組織名', $organization->name],
                ['ユーザー数', $organization->users_count],
                ['会議数', $organization->meetings_count],
                ['メンバー数', $organization->members_count],
                ['Stripe Customer ID', $organization->stripe_id ?? '(なし)'],
            ],
        );

        if (! $this->option('force')) {
            $typed = $this->ask('削除を実行するには、組織名を正確に入力してください');

            if ($typed !== $organization->name) {
                $this->error('入力された組織名が一致しないため、削除を中止しました。');

                return self::FAILURE;
            }
        }

        if ($organization->subscribed('default')) {
            try {
                $organization->subscription('default')->cancelNow();
                $this->info('Stripeのサブスクリプションを即時解約しました。');
            } catch (Throwable $e) {
                report($e);
                $this->warn('Stripeサブスクリプションの解約に失敗しました。Stripeダッシュボードから手動で解約してください。');
            }
        }

        $purger->purgeUploadedFiles($organization);

        // Cashierのsubscriptionsテーブルはorganization_idに外部キー制約が
        // 無く、Organization削除時に自動では消えないため個別に削除する。
        $organization->subscriptions()->delete();

        $organizationName = $organization->name;
        $organizationId = $organization->id;

        $organization->delete();

        Log::info("組織を削除しました: id={$organizationId}, name={$organizationName}");
        $this->info("組織「{$organizationName}」(id={$organizationId})を削除しました。");

        return self::SUCCESS;
    }
}
