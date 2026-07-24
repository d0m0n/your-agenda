<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 管理者が特定組織に対し、課金なしで全機能を使わせる「無償提供モード」。
 * super_adminのみが管理者パネルから切り替えられる(Organization::hasActiveAccess参照)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('free_access_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('free_access_enabled');
        });
    }
};
