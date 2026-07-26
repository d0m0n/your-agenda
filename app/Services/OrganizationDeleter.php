<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 組織の完全削除を実行する(取り消し不可)。退会依頼への手動対応
 * (app/Console/Commands/DeleteOrganization.php)と、猶予期間経過後の
 * 自動削除(app/Console/Commands/ProcessOrganizationRetention.php)の
 * 両方から共通して呼ばれる。
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
class OrganizationDeleter
{
    public function __construct(private OrganizationDataPurgeService $purger) {}

    public function delete(Organization $organization): void
    {
        if ($organization->subscribed('default')) {
            try {
                $organization->subscription('default')->cancelNow();
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->purger->purgeUploadedFiles($organization);

        // Cashierのsubscriptionsテーブルはorganization_idに外部キー制約が
        // 無く、Organization削除時に自動では消えないため個別に削除する。
        $organization->subscriptions()->delete();

        $organizationName = $organization->name;
        $organizationId = $organization->id;

        $organization->delete();

        Log::info("組織を削除しました: id={$organizationId}, name={$organizationName}");
    }
}
