<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Stripeの顧客情報(領収書・請求書メール等の送付先)に同期するための
            // 請求先メールアドレス。組織にはログインユーザーのようなメール欄が
            // 無いため、任意で設定できるようにする。
            $table->string('billing_email')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('billing_email');
        });
    }
};
