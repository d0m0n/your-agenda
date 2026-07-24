<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * plan_status(未使用の文字列カラム、実質的な契約状態のロジックには一度も
 * 使われていなかった)を廃止する。Cashierのsubscriptionsテーブルと
 * trial_ends_atから導出するOrganization::hasActiveAccess()に一本化し、
 * 二重管理による食い違いを避ける。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('plan_status');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('plan_status')->default('trial');
        });
    }
};
