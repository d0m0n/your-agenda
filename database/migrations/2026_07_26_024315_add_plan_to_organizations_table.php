<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // スタンダード/プラスのプラン区分。Stripe側にプラス用の価格が
            // まだ無いため、管理者パネルからの手動切り替えのみで運用する
            // (free_access_enabledと同じ方式)。将来プラス限定機能を実装する
            // 際は、Organization::hasPlusAccess()経由のGate('plus')で判定する。
            $table->string('plan')->default('standard')->after('billing_email');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
