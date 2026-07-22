<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null = デフォルト容量(config('storage_quota.default_bytes'))を使う。
            // 管理者アカウントがユーザーごとに個別の割り当て容量へ上書きできる。
            $table->unsignedBigInteger('storage_quota_bytes')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('storage_quota_bytes');
        });
    }
};
