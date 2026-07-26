<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\OrganizationDeleter;
use Illuminate\Console\Command;

/**
 * 退会依頼への対応(組織の完全削除)。UIからの操作経路はなく、super_admin
 * アカウント作成等と同様、サーバー上でのartisan実行のみで行う運用
 * (本人確認をSSHアクセス権限に委ねる)。実際の削除処理は
 * App\Services\OrganizationDeleterに委譲する(削除される/されないものの
 * 詳細はそちらのdocblockを参照)。
 */
class DeleteOrganization extends Command
{
    protected $signature = 'organizations:delete {organization : 削除する組織のID} {--force : 確認をスキップする}';

    protected $description = '退会依頼を受けた組織を完全に削除する(取り消し不可)';

    public function handle(OrganizationDeleter $deleter): int
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

        $organizationName = $organization->name;
        $organizationId = $organization->id;

        $deleter->delete($organization);

        $this->info("組織「{$organizationName}」(id={$organizationId})を削除しました。");

        return self::SUCCESS;
    }
}
