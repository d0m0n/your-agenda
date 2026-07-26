<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // hasActiveAccess()がfalseになった(トライアル終了・解約等で
            // 保護ルートに入れなくなった)日時。ここから
            // config('billing.deletion_grace_period_days')日が経過し、
            // 再契約等でアクセスが回復していなければ自動的に組織を完全削除する
            // (ProcessOrganizationRetentionコマンド参照)。
            // アクセスが回復した場合はnullに戻す。
            $table->timestamp('access_lost_at')->nullable()->after('free_access_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('access_lost_at');
        });
    }
};
